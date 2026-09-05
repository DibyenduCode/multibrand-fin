<?php
require_once __DIR__ . '/../../config/database.php';

class Brand {
    public static function all(bool $activeOnly = false): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM brands";
        if ($activeOnly) {
            $sql .= " WHERE status = 'active'";
        }
        $sql .= " ORDER BY brand_name ASC";
        return $db->query($sql)->fetchAll();
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO brands (brand_name, company_name, email, phone, address, logo, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['brand_name'],
            $data['company_name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['logo'] ?? null,
            $data['status'] ?? 'active'
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE brands SET brand_name = ?, company_name = ?, email = ?, phone = ?, address = ?, logo = ?, status = ? WHERE id = ?");
        return $stmt->execute([
            $data['brand_name'],
            $data['company_name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['logo'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
    }

    public static function getAvailableBalance(int $brandId): float {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT COALESCE(SUM(opening_balance), 0) FROM bank_accounts WHERE brand_id = ? AND status = 'active'");
        $stmt->execute([$brandId]);
        $opening = (float)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE brand_id = ? AND type IN ('income', 'loan_received', 'loan_repayment_received')");
        $stmt->execute([$brandId]);
        $inflow = (float)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE brand_id = ? AND type IN ('expense', 'loan_given', 'loan_repayment')");
        $stmt->execute([$brandId]);
        $outflow = (float)$stmt->fetchColumn();

        return $opening + $inflow - $outflow;
    }

    public static function getBrandFinancialStats(int $brandId): array {
        $db = Database::getConnection();

        $availableMoney = self::getAvailableBalance($brandId);

        $firstDayOfMonth = date('Y-m-01');
        $lastDayOfMonth = date('Y-m-t');

        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE brand_id = ? AND type IN ('income', 'loan_repayment_received') AND transaction_date BETWEEN ? AND ?");
        $stmt->execute([$brandId, $firstDayOfMonth, $lastDayOfMonth]);
        $monthMoneyIn = (float)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE brand_id = ? AND type IN ('expense', 'loan_repayment') AND transaction_date BETWEEN ? AND ?");
        $stmt->execute([$brandId, $firstDayOfMonth, $lastDayOfMonth]);
        $monthMoneyOut = (float)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COALESCE(SUM(remaining_amount), 0) FROM inter_brand_loans WHERE lender_brand_id = ? AND status IN ('active', 'partially_repaid')");
        $stmt->execute([$brandId]);
        $receivable = (float)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COALESCE(SUM(remaining_amount), 0) FROM inter_brand_loans WHERE borrower_brand_id = ? AND status IN ('active', 'partially_repaid')");
        $stmt->execute([$brandId]);
        $liability = (float)$stmt->fetchColumn();

        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE brand_id = ? AND type IN ('income', 'loan_received', 'loan_repayment_received') AND transaction_date = ?");
        $stmt->execute([$brandId, $today]);
        $todayIn = (float)$stmt->fetchColumn();

        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE brand_id = ? AND type IN ('expense', 'loan_given', 'loan_repayment') AND transaction_date = ?");
        $stmt->execute([$brandId, $today]);
        $todayOut = (float)$stmt->fetchColumn();

        return [
            'available_money' => $availableMoney,
            'month_money_in' => $monthMoneyIn,
            'month_money_out' => $monthMoneyOut,
            'receivable' => $receivable,
            'liability' => $liability,
            'today_in' => $todayIn,
            'today_out' => $todayOut,
            'today_net' => $todayIn - $todayOut,
        ];
    }

    public static function getGroupFinancialStats(): array {
        $db = Database::getConnection();
        $brands = self::all(true);

        $totalAvailable = 0;
        $totalMonthIn = 0;
        $totalMonthOut = 0;

        foreach ($brands as $brand) {
            $stats = self::getBrandFinancialStats((int)$brand['id']);
            $totalAvailable += $stats['available_money'];
            $totalMonthIn += $stats['month_money_in'];
            $totalMonthOut += $stats['month_money_out'];
        }

        $stmt = $db->query("SELECT COALESCE(SUM(remaining_amount), 0) FROM inter_brand_loans WHERE status IN ('active', 'partially_repaid')");
        $totalInternalLoansOutstanding = (float)$stmt->fetchColumn();

        return [
            'total_available' => $totalAvailable,
            'total_month_in' => $totalMonthIn,
            'total_month_out' => $totalMonthOut,
            'total_receivable' => $totalInternalLoansOutstanding,
            'total_liability' => $totalInternalLoansOutstanding,
        ];
    }
}
