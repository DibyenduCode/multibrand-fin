<?php
require_once __DIR__ . '/../../config/database.php';

class BankAccount {
    public static function getByBrand(int $brandId, bool $activeOnly = false): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM bank_accounts WHERE brand_id = ?";
        if ($activeOnly) {
            $sql .= " AND status = 'active'";
        }
        $sql .= " ORDER BY bank_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$brandId]);
        $accounts = $stmt->fetchAll();

        foreach ($accounts as &$acc) {
            $acc['current_balance'] = self::getBalance((int)$acc['id']);
        }
        return $accounts;
    }

    public static function getByBrands(array $brandIds, bool $activeOnly = false): array {
        if (empty($brandIds)) return [];
        $db = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($brandIds), '?'));
        $sql = "SELECT ba.*, b.brand_name 
                FROM bank_accounts ba 
                JOIN brands b ON ba.brand_id = b.id 
                WHERE ba.brand_id IN ($placeholders)";
        if ($activeOnly) {
            $sql .= " AND ba.status = 'active'";
        }
        $sql .= " ORDER BY b.brand_name ASC, ba.bank_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($brandIds);
        $accounts = $stmt->fetchAll();

        foreach ($accounts as &$acc) {
            $acc['current_balance'] = self::getBalance((int)$acc['id']);
        }
        return $accounts;
    }

    public static function all(): array {
        $db = Database::getConnection();
        $sql = "SELECT ba.*, b.brand_name 
                FROM bank_accounts ba 
                JOIN brands b ON ba.brand_id = b.id 
                ORDER BY b.brand_name ASC, ba.bank_name ASC";
        $accounts = $db->query($sql)->fetchAll();

        foreach ($accounts as &$acc) {
            $acc['current_balance'] = self::getBalance((int)$acc['id']);
        }
        return $accounts;
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT ba.*, b.brand_name FROM bank_accounts ba JOIN brands b ON ba.brand_id = b.id WHERE ba.id = ?");
        $stmt->execute([$id]);
        $acc = $stmt->fetch();
        if ($acc) {
            $acc['current_balance'] = self::getBalance((int)$acc['id']);
        }
        return $acc ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO bank_accounts (brand_id, bank_name, account_holder_name, account_number, ifsc_code, opening_balance, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['brand_id'],
            $data['bank_name'],
            $data['account_holder_name'],
            $data['account_number'],
            $data['ifsc_code'] ?? null,
            $data['opening_balance'] ?? 0.00,
            $data['status'] ?? 'active'
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE bank_accounts SET bank_name = ?, account_holder_name = ?, account_number = ?, ifsc_code = ?, opening_balance = ?, status = ? WHERE id = ?");
        return $stmt->execute([
            $data['bank_name'],
            $data['account_holder_name'],
            $data['account_number'],
            $data['ifsc_code'] ?? null,
            $data['opening_balance'] ?? 0.00,
            $data['status'] ?? 'active',
            $id
        ]);
    }

    public static function getBalance(int $bankAccountId): float {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT opening_balance FROM bank_accounts WHERE id = ?");
        $stmt->execute([$bankAccountId]);
        $opening = (float)$stmt->fetchColumn();

        // Inflow
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE bank_account_id = ? AND type IN ('income', 'loan_received', 'loan_repayment_received')");
        $stmt->execute([$bankAccountId]);
        $inflow = (float)$stmt->fetchColumn();

        // Outflow
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE bank_account_id = ? AND type IN ('expense', 'loan_given', 'loan_repayment')");
        $stmt->execute([$bankAccountId]);
        $outflow = (float)$stmt->fetchColumn();

        return $opening + $inflow - $outflow;
    }
}
