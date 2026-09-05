<?php
require_once __DIR__ . '/../../config/database.php';

class Transaction {
    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO transactions 
            (brand_id, bank_account_id, type, amount, purpose, category, related_brand_id, reference_type, reference_id, transaction_date, note, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['brand_id'],
            $data['bank_account_id'],
            $data['type'],
            $data['amount'],
            $data['purpose'],
            $data['category'] ?? null,
            $data['related_brand_id'] ?? null,
            $data['reference_type'] ?? null,
            $data['reference_id'] ?? null,
            $data['transaction_date'],
            $data['note'] ?? null,
            $data['created_by']
        ]);
        return (int)$db->lastInsertId();
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT t.*, b.brand_name, ba.bank_name, rb.brand_name as related_brand_name, u.name as created_by_name 
            FROM transactions t
            JOIN brands b ON t.brand_id = b.id
            JOIN bank_accounts ba ON t.bank_account_id = ba.id
            LEFT JOIN brands rb ON t.related_brand_id = rb.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function count(array $filters = []): int {
        $db = Database::getConnection();
        $sql = "SELECT COUNT(*) FROM transactions t WHERE 1=1";
        $params = [];

        if (!empty($filters['brand_ids']) && is_array($filters['brand_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['brand_ids']), '?'));
            $sql .= " AND t.brand_id IN ($placeholders)";
            foreach ($filters['brand_ids'] as $bId) {
                $params[] = (int)$bId;
            }
        } elseif (!empty($filters['brand_id'])) {
            $sql .= " AND t.brand_id = ?";
            $params[] = $filters['brand_id'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND t.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['date_range'])) {
            $today = date('Y-m-d');
            switch ($filters['date_range']) {
                case 'today':
                    $sql .= " AND t.transaction_date = ?";
                    $params[] = $today;
                    break;
                case '7_days':
                    $sevenDays = date('Y-m-d', strtotime('-7 days'));
                    $sql .= " AND t.transaction_date >= ?";
                    $params[] = $sevenDays;
                    break;
                case 'this_month':
                    $firstOfMonth = date('Y-m-01');
                    $sql .= " AND t.transaction_date >= ?";
                    $params[] = $firstOfMonth;
                    break;
                case 'custom':
                    if (!empty($filters['start_date'])) {
                        $sql .= " AND t.transaction_date >= ?";
                        $params[] = $filters['start_date'];
                    }
                    if (!empty($filters['end_date'])) {
                        $sql .= " AND t.transaction_date <= ?";
                        $params[] = $filters['end_date'];
                    }
                    break;
            }
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public static function all(array $filters = []): array {
        $db = Database::getConnection();
        $sql = "SELECT t.*, b.brand_name, ba.bank_name, ba.account_number, rb.brand_name as related_brand_name, u.name as created_by_name 
                FROM transactions t
                JOIN brands b ON t.brand_id = b.id
                JOIN bank_accounts ba ON t.bank_account_id = ba.id
                LEFT JOIN brands rb ON t.related_brand_id = rb.id
                LEFT JOIN users u ON t.created_by = u.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['brand_ids']) && is_array($filters['brand_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['brand_ids']), '?'));
            $sql .= " AND t.brand_id IN ($placeholders)";
            foreach ($filters['brand_ids'] as $bId) {
                $params[] = (int)$bId;
            }
        } elseif (!empty($filters['brand_id'])) {
            $sql .= " AND t.brand_id = ?";
            $params[] = $filters['brand_id'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND t.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['date_range'])) {
            $today = date('Y-m-d');
            switch ($filters['date_range']) {
                case 'today':
                    $sql .= " AND t.transaction_date = ?";
                    $params[] = $today;
                    break;
                case '7_days':
                    $sevenDays = date('Y-m-d', strtotime('-7 days'));
                    $sql .= " AND t.transaction_date >= ?";
                    $params[] = $sevenDays;
                    break;
                case 'this_month':
                    $firstOfMonth = date('Y-m-01');
                    $sql .= " AND t.transaction_date >= ?";
                    $params[] = $firstOfMonth;
                    break;
                case 'custom':
                    if (!empty($filters['start_date'])) {
                        $sql .= " AND t.transaction_date >= ?";
                        $params[] = $filters['start_date'];
                    }
                    if (!empty($filters['end_date'])) {
                        $sql .= " AND t.transaction_date <= ?";
                        $params[] = $filters['end_date'];
                    }
                    break;
            }
        }

        $sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

        if (isset($filters['offset']) && isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['offset'] . ", " . (int)$filters['limit'];
        } elseif (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getRecentByBrand(int $brandId, int $limit = 10): array {
        return self::all(['brand_id' => $brandId, 'limit' => $limit]);
    }

    public static function getRecentGroup(int $limit = 10): array {
        return self::all(['limit' => $limit]);
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE transactions SET 
            brand_id = ?, 
            bank_account_id = ?, 
            type = ?, 
            amount = ?, 
            purpose = ?, 
            category = ?, 
            transaction_date = ?, 
            note = ? 
            WHERE id = ?");
        return $stmt->execute([
            $data['brand_id'],
            $data['bank_account_id'],
            $data['type'],
            $data['amount'],
            $data['purpose'],
            $data['category'] ?? null,
            $data['transaction_date'],
            $data['note'] ?? null,
            $id
        ]);
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM transactions WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
