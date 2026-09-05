<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Brand.php';

class Report {
    public static function getDailyReport(?int $brandId = null, ?string $date = null): array {
        $db = Database::getConnection();
        $targetDate = $date ?: date('Y-m-d');

        $sqlIn = "SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE transaction_date = ? AND type IN ('income', 'loan_received', 'loan_repayment_received')";
        $sqlOut = "SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE transaction_date = ? AND type IN ('expense', 'loan_given', 'loan_repayment')";

        $paramsIn = [$targetDate];
        $paramsOut = [$targetDate];

        if ($brandId) {
            $sqlIn .= " AND brand_id = ?";
            $sqlOut .= " AND brand_id = ?";
            $paramsIn[] = $brandId;
            $paramsOut[] = $brandId;
        }

        $stmtIn = $db->prepare($sqlIn);
        $stmtIn->execute($paramsIn);
        $moneyIn = (float)$stmtIn->fetchColumn();

        $stmtOut = $db->prepare($sqlOut);
        $stmtOut->execute($paramsOut);
        $moneyOut = (float)$stmtOut->fetchColumn();

        return [
            'date' => $targetDate,
            'money_in' => $moneyIn,
            'money_out' => $moneyOut,
            'net_change' => $moneyIn - $moneyOut
        ];
    }

    public static function getMonthlyReport(?int $brandId = null, ?string $yearMonth = null): array {
        $db = Database::getConnection();
        $ym = $yearMonth ?: date('Y-m');
        $startDate = $ym . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $sqlIn = "SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE transaction_date BETWEEN ? AND ? AND type IN ('income', 'loan_repayment_received')";
        $sqlOut = "SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE transaction_date BETWEEN ? AND ? AND type IN ('expense', 'loan_repayment')";

        $paramsIn = [$startDate, $endDate];
        $paramsOut = [$startDate, $endDate];

        if ($brandId) {
            $sqlIn .= " AND brand_id = ?";
            $sqlOut .= " AND brand_id = ?";
            $paramsIn[] = $brandId;
            $paramsOut[] = $brandId;
        }

        $stmtIn = $db->prepare($sqlIn);
        $stmtIn->execute($paramsIn);
        $moneyIn = (float)$stmtIn->fetchColumn();

        $stmtOut = $db->prepare($sqlOut);
        $stmtOut->execute($paramsOut);
        $moneyOut = (float)$stmtOut->fetchColumn();

        return [
            'month' => date('F Y', strtotime($startDate)),
            'year_month' => $ym,
            'money_in' => $moneyIn,
            'money_out' => $moneyOut,
            'difference' => $moneyIn - $moneyOut
        ];
    }

    public static function getBrandReport(int $brandId): array {
        $db = Database::getConnection();
        $brand = Brand::find($brandId);
        if (!$brand) return [];

        $availableMoney = Brand::getAvailableBalance($brandId);

        // Total Income
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE brand_id = ? AND type = 'income'");
        $stmt->execute([$brandId]);
        $totalIncome = (float)$stmt->fetchColumn();

        // Total Expenses
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE brand_id = ? AND type = 'expense'");
        $stmt->execute([$brandId]);
        $totalExpenses = (float)$stmt->fetchColumn();

        // Loans Taken (Original Principal)
        $stmt = $db->prepare("SELECT COALESCE(SUM(original_amount), 0) FROM inter_brand_loans WHERE borrower_brand_id = ?");
        $stmt->execute([$brandId]);
        $loansTaken = (float)$stmt->fetchColumn();

        // Loans Given (Original Principal)
        $stmt = $db->prepare("SELECT COALESCE(SUM(original_amount), 0) FROM inter_brand_loans WHERE lender_brand_id = ?");
        $stmt->execute([$brandId]);
        $loansGiven = (float)$stmt->fetchColumn();

        // Outstanding Liability
        $stmt = $db->prepare("SELECT COALESCE(SUM(remaining_amount), 0) FROM inter_brand_loans WHERE borrower_brand_id = ? AND status IN ('active', 'partially_repaid')");
        $stmt->execute([$brandId]);
        $outstandingLiability = (float)$stmt->fetchColumn();

        // Outstanding Receivable
        $stmt = $db->prepare("SELECT COALESCE(SUM(remaining_amount), 0) FROM inter_brand_loans WHERE lender_brand_id = ? AND status IN ('active', 'partially_repaid')");
        $stmt->execute([$brandId]);
        $outstandingReceivable = (float)$stmt->fetchColumn();

        return [
            'brand' => $brand,
            'available_money' => $availableMoney,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'loans_taken' => $loansTaken,
            'loans_given' => $loansGiven,
            'outstanding_liability' => $outstandingLiability,
            'outstanding_receivable' => $outstandingReceivable
        ];
    }
}
