<?php
require_once __DIR__ . '/../../config/database.php';

class User {
    public static function findByEmail(string $email): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT u.*, b.brand_name 
            FROM users u 
            LEFT JOIN brands b ON u.brand_id = b.id 
            WHERE u.email = ?");
        $stmt->execute([trim(strtolower($email))]);
        $user = $stmt->fetch();
        if ($user) {
            $user['managed_brand_ids'] = self::getUserBrandIds((int)$user['id']);
            $user['managed_brands'] = self::getUserBrands((int)$user['id']);
        }
        return $user ?: null;
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT u.*, b.brand_name 
            FROM users u 
            LEFT JOIN brands b ON u.brand_id = b.id 
            WHERE u.id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            $user['managed_brand_ids'] = self::getUserBrandIds((int)$user['id']);
            $user['managed_brands'] = self::getUserBrands((int)$user['id']);
        }
        return $user ?: null;
    }

    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT u.*, b.brand_name 
            FROM users u 
            LEFT JOIN brands b ON u.brand_id = b.id 
            ORDER BY u.role ASC, u.name ASC");
        $users = $stmt->fetchAll();
        foreach ($users as &$u) {
            $u['managed_brand_ids'] = self::getUserBrandIds((int)$u['id']);
            $u['managed_brands'] = self::getUserBrands((int)$u['id']);
        }
        return $users;
    }

    public static function getUserBrandIds(int $userId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT brand_id FROM user_brands WHERE user_id = ?");
        $stmt->execute([$userId]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Fallback to users.brand_id if user_brands has none
        if (empty($ids)) {
            $stmt2 = $db->prepare("SELECT brand_id FROM users WHERE id = ? AND brand_id IS NOT NULL");
            $stmt2->execute([$userId]);
            $primaryId = $stmt2->fetchColumn();
            if ($primaryId) {
                $ids = [(int)$primaryId];
            }
        }
        return array_map('intval', $ids);
    }

    public static function getUserBrands(int $userId): array {
        $db = Database::getConnection();
        $sql = "SELECT b.* FROM brands b 
                JOIN user_brands ub ON b.id = ub.brand_id 
                WHERE ub.user_id = ? AND b.status = 'active'
                ORDER BY b.brand_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $brands = $stmt->fetchAll();

        if (empty($brands)) {
            $sql2 = "SELECT b.* FROM brands b JOIN users u ON b.id = u.brand_id WHERE u.id = ?";
            $stmt2 = $db->prepare($sql2);
            $stmt2->execute([$userId]);
            $brands = $stmt2->fetchAll();
        }

        return $brands;
    }

    public static function syncUserBrands(int $userId, array $brandIds): void {
        $db = Database::getConnection();
        $db->prepare("DELETE FROM user_brands WHERE user_id = ?")->execute([$userId]);

        if (!empty($brandIds)) {
            $stmt = $db->prepare("INSERT INTO user_brands (user_id, brand_id) VALUES (?, ?)");
            foreach ($brandIds as $bId) {
                if ((int)$bId > 0) {
                    $stmt->execute([$userId, (int)$bId]);
                }
            }
        }
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);

        $brandIds = $data['brand_ids'] ?? [];
        $primaryBrandId = !empty($brandIds) ? (int)$brandIds[0] : (!empty($data['brand_id']) ? (int)$data['brand_id'] : null);

        $stmt = $db->prepare("INSERT INTO users (name, email, password, role, brand_id, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            strtolower(trim($data['email'])),
            $hash,
            $data['role'],
            $primaryBrandId,
            $data['status'] ?? 'active'
        ]);

        $userId = (int)$db->lastInsertId();

        if ($data['role'] === 'brand_user' && !empty($brandIds)) {
            self::syncUserBrands($userId, $brandIds);
        } elseif ($data['role'] === 'brand_user' && $primaryBrandId) {
            self::syncUserBrands($userId, [$primaryBrandId]);
        }

        return $userId;
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        
        $brandIds = $data['brand_ids'] ?? [];
        $primaryBrandId = !empty($brandIds) ? (int)$brandIds[0] : (!empty($data['brand_id']) ? (int)$data['brand_id'] : null);

        if (!empty($data['password'])) {
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ?, brand_id = ?, status = ? WHERE id = ?");
            $res = $stmt->execute([
                $data['name'],
                strtolower(trim($data['email'])),
                $hash,
                $data['role'],
                $primaryBrandId,
                $data['status'] ?? 'active',
                $id
            ]);
        } else {
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, role = ?, brand_id = ?, status = ? WHERE id = ?");
            $res = $stmt->execute([
                $data['name'],
                strtolower(trim($data['email'])),
                $data['role'],
                $primaryBrandId,
                $data['status'] ?? 'active',
                $id
            ]);
        }

        if ($data['role'] === 'brand_user') {
            self::syncUserBrands($id, $brandIds);
        } else {
            self::syncUserBrands($id, []);
        }

        return $res;
    }

    public static function updateProfile(int $id, array $data): bool {
        $db = Database::getConnection();
        $name = trim($data['name']);
        $email = strtolower(trim($data['email']));
        
        if (!empty($data['password'])) {
            $hash = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $hash, $id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $id]);
        }
    }

    public static function verifyPassword(array $user, string $password): bool {
        return password_verify($password, $user['password']);
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        
        try {
            $db->beginTransaction();

            // Delete associated brand assignments
            $stmt = $db->prepare("DELETE FROM user_brands WHERE user_id = ?");
            $stmt->execute([$id]);

            // Disable foreign key checks briefly to delete user while preserving created_by reference IDs in financial tables
            $db->exec("SET FOREIGN_KEY_CHECKS = 0");
            $deleteStmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $res = $deleteStmt->execute([$id]);
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");

            $db->commit();
            return $res;
        } catch (Exception $e) {
            $db->rollBack();
            $db->exec("SET FOREIGN_KEY_CHECKS = 1");
            throw $e;
        }
    }
}
