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
        $brandId = (int)$db->lastInsertId();

        // Auto-provision default Hand Cash account for brand
        require_once __DIR__ . '/BankAccount.php';
        BankAccount::provisionDefaultCashAccount($brandId);

        return $brandId;
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

    public static function getBankBalance(int $brandId): float {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM bank_accounts WHERE brand_id = ? AND account_type = 'bank' AND status = 'active'");
        $stmt->execute([$brandId]);
        $accountIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $total = 0.00;
        require_once __DIR__ . '/BankAccount.php';
        foreach ($accountIds as $accId) {
            $total += BankAccount::getBalance((int)$accId);
        }
        return $total;
    }

    public static function getHandCashBalance(int $brandId): float {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM bank_accounts WHERE brand_id = ? AND account_type = 'cash' AND status = 'active'");
        $stmt->execute([$brandId]);
        $accountIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $total = 0.00;
        require_once __DIR__ . '/BankAccount.php';
        foreach ($accountIds as $accId) {
            $total += BankAccount::getBalance((int)$accId);
        }
        return $total;
    }

    public static function getAvailableBalance(int $brandId): float {
        return self::getBankBalance($brandId) + self::getHandCashBalance($brandId);
    }

    public static function getBrandFinancialStats(int $brandId): array {
        $db = Database::getConnection();

        $bankBalance = self::getBankBalance($brandId);
        $handCashBalance = self::getHandCashBalance($brandId);
        $availableMoney = $bankBalance + $handCashBalance;

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
            'bank_balance' => $bankBalance,
            'hand_cash_balance' => $handCashBalance,
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
        $totalBank = 0;
        $totalHandCash = 0;
        $totalMonthIn = 0;
        $totalMonthOut = 0;

        foreach ($brands as $brand) {
            $stats = self::getBrandFinancialStats((int)$brand['id']);
            $totalAvailable += $stats['available_money'];
            $totalBank += $stats['bank_balance'];
            $totalHandCash += $stats['hand_cash_balance'];
            $totalMonthIn += $stats['month_money_in'];
            $totalMonthOut += $stats['month_money_out'];
        }

        $stmt = $db->query("SELECT COALESCE(SUM(remaining_amount), 0) FROM inter_brand_loans WHERE status IN ('active', 'partially_repaid')");
        $totalInternalLoansOutstanding = (float)$stmt->fetchColumn();

        return [
            'total_available' => $totalAvailable,
            'total_bank_balance' => $totalBank,
            'total_hand_cash_balance' => $totalHandCash,
            'total_month_in' => $totalMonthIn,
            'total_month_out' => $totalMonthOut,
            'total_receivable' => $totalInternalLoansOutstanding,
            'total_liability' => $totalInternalLoansOutstanding,
        ];
    }
}
