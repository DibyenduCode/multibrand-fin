<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Transaction.php';
require_once __DIR__ . '/Loan.php';

class LoanRepayment {
    public static function generateRepaymentNumber(): string {
        $db = Database::getConnection();
        $year = date('Y');
        $stmt = $db->query("SELECT MAX(id) FROM loan_repayments");
        $maxId = (int)$stmt->fetchColumn() + 1;
        return sprintf("REP-%s-%04d", $year, $maxId);
    }

    public static function create(array $data): int {
        $db = Database::getConnection();

        // 1. Fetch current loan details
        $loan = Loan::find((int)$data['loan_id']);
        if (!$loan) {
            throw new Exception("Loan not found.");
        }

        $amount = (float)$data['amount'];
        if ($amount <= 0) {
            throw new Exception("Repayment amount must be greater than zero.");
        }

        if ($amount > (float)$loan['remaining_amount']) {
            throw new Exception("Repayment amount (₹ " . number_format($amount, 2) . ") exceeds the remaining loan liability (₹ " . number_format($loan['remaining_amount'], 2) . ").");
        }

        $db->beginTransaction();

        try {
            $repaymentNumber = self::generateRepaymentNumber();

            // Insert Repayment Record
            $stmt = $db->prepare("INSERT INTO loan_repayments 
                (loan_id, repayment_number, from_brand_id, to_brand_id, from_bank_account_id, to_bank_account_id, amount, payment_date, payment_reference, description, status, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'completed', ?)");
            
            $stmt->execute([
                $loan['id'],
                $repaymentNumber,
                $loan['borrower_brand_id'],
                $loan['lender_brand_id'],
                $data['from_bank_account_id'],
                $data['to_bank_account_id'],
                $amount,
                $data['payment_date'],
                $data['payment_reference'] ?? null,
                $data['description'] ?? null,
                $data['created_by']
            ]);

            $repaymentId = (int)$db->lastInsertId();

            // Update Loan totals & status
            $newTotalRepaid = (float)$loan['total_repaid'] + $amount;
            $newRemaining = (float)$loan['original_amount'] - $newTotalRepaid;
            $newStatus = ($newRemaining <= 0.01) ? 'paid' : 'partially_repaid';

            $stmt = $db->prepare("UPDATE inter_brand_loans SET total_repaid = ?, remaining_amount = ?, status = ? WHERE id = ?");
            $stmt->execute([$newTotalRepaid, max(0, $newRemaining), $newStatus, $loan['id']]);

            // Create Borrower Transaction (Money Out: loan_repayment)
            Transaction::create([
                'brand_id' => $loan['borrower_brand_id'],
                'bank_account_id' => $data['from_bank_account_id'],
                'type' => 'loan_repayment',
                'amount' => $amount,
                'purpose' => 'Loan Repayment to ' . $loan['lender_brand_name'],
                'category' => 'Loan Repayment',
                'related_brand_id' => $loan['lender_brand_id'],
                'reference_type' => 'repayment',
                'reference_id' => $repaymentId,
                'transaction_date' => $data['payment_date'],
                'note' => 'Repayment Ref: ' . $repaymentNumber . ' for Loan ' . $loan['loan_number'],
                'created_by' => $data['created_by']
            ]);

            // Create Lender Transaction (Money In: loan_repayment_received)
            Transaction::create([
                'brand_id' => $loan['lender_brand_id'],
                'bank_account_id' => $data['to_bank_account_id'],
                'type' => 'loan_repayment_received',
                'amount' => $amount,
                'purpose' => 'Loan Repayment Received from ' . $loan['borrower_brand_name'],
                'category' => 'Loan Repayment',
                'related_brand_id' => $loan['borrower_brand_id'],
                'reference_type' => 'repayment',
                'reference_id' => $repaymentId,
                'transaction_date' => $data['payment_date'],
                'note' => 'Repayment Ref: ' . $repaymentNumber . ' for Loan ' . $loan['loan_number'],
                'created_by' => $data['created_by']
            ]);

            $db->commit();
            return $repaymentId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function getByLoan(int $loanId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT r.*, 
                fb.brand_name as from_brand_name, tb.brand_name as to_brand_name,
                fba.bank_name as from_bank_name, tba.bank_name as to_bank_name,
                u.name as created_by_name
            FROM loan_repayments r
            JOIN brands fb ON r.from_brand_id = fb.id
            JOIN brands tb ON r.to_brand_id = tb.id
            JOIN bank_accounts fba ON r.from_bank_account_id = fba.id
            JOIN bank_accounts tba ON r.to_bank_account_id = tba.id
            LEFT JOIN users u ON r.created_by = u.id
            WHERE r.loan_id = ?
            ORDER BY r.payment_date ASC, r.id ASC");
        $stmt->execute([$loanId]);
        return $stmt->fetchAll();
    }
}
