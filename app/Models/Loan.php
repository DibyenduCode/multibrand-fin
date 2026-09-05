<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Transaction.php';

class Loan {
    public static function generateLoanNumber(): string {
        $db = Database::getConnection();
        $year = date('Y');
        $stmt = $db->query("SELECT MAX(id) FROM inter_brand_loans");
        $maxId = (int)$stmt->fetchColumn() + 1;
        return sprintf("IBL-%s-%04d", $year, $maxId);
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $loanNumber = self::generateLoanNumber();

            $stmt = $db->prepare("INSERT INTO inter_brand_loans 
                (loan_number, lender_brand_id, borrower_brand_id, lender_bank_account_id, borrower_bank_account_id, original_amount, total_repaid, remaining_amount, loan_date, due_date, purpose, description, status, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, 0.00, ?, ?, ?, ?, ?, 'active', ?)");
            
            $stmt->execute([
                $loanNumber,
                $data['lender_brand_id'],
                $data['borrower_brand_id'],
                $data['lender_bank_account_id'],
                $data['borrower_bank_account_id'],
                $data['original_amount'],
                $data['original_amount'], // remaining_amount initially equal to original_amount
                $data['loan_date'],
                $data['due_date'] ?? null,
                $data['purpose'],
                $data['description'] ?? null,
                $data['created_by']
            ]);

            $loanId = (int)$db->lastInsertId();

            // 1. Transaction for Lender Brand (Money Out: loan_given)
            Transaction::create([
                'brand_id' => $data['lender_brand_id'],
                'bank_account_id' => $data['lender_bank_account_id'],
                'type' => 'loan_given',
                'amount' => $data['original_amount'],
                'purpose' => 'Inter-Brand Loan to ' . $data['borrower_brand_name'],
                'category' => 'Inter-Brand Loan',
                'related_brand_id' => $data['borrower_brand_id'],
                'reference_type' => 'loan',
                'reference_id' => $loanId,
                'transaction_date' => $data['loan_date'],
                'note' => 'Loan Ref: ' . $loanNumber . ' - ' . ($data['description'] ?? ''),
                'created_by' => $data['created_by']
            ]);

            // 2. Transaction for Borrower Brand (Money In: loan_received)
            Transaction::create([
                'brand_id' => $data['borrower_brand_id'],
                'bank_account_id' => $data['borrower_bank_account_id'],
                'type' => 'loan_received',
                'amount' => $data['original_amount'],
                'purpose' => 'Inter-Brand Loan from ' . $data['lender_brand_name'],
                'category' => 'Inter-Brand Loan',
                'related_brand_id' => $data['lender_brand_id'],
                'reference_type' => 'loan',
                'reference_id' => $loanId,
                'transaction_date' => $data['loan_date'],
                'note' => 'Loan Ref: ' . $loanNumber . ' - ' . ($data['description'] ?? ''),
                'created_by' => $data['created_by']
            ]);

            $db->commit();
            return $loanId;
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT l.*, 
                lb.brand_name as lender_brand_name, 
                bb.brand_name as borrower_brand_name,
                lba.bank_name as lender_bank_name, lba.account_number as lender_account_number,
                bba.bank_name as borrower_bank_name, bba.account_number as borrower_account_number,
                u.name as created_by_name
            FROM inter_brand_loans l
            JOIN brands lb ON l.lender_brand_id = lb.id
            JOIN brands bb ON l.borrower_brand_id = bb.id
            JOIN bank_accounts lba ON l.lender_bank_account_id = lba.id
            JOIN bank_accounts bba ON l.borrower_bank_account_id = bba.id
            LEFT JOIN users u ON l.created_by = u.id
            WHERE l.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function all(array $filters = []): array {
        $db = Database::getConnection();
        $sql = "SELECT l.*, 
                lb.brand_name as lender_brand_name, 
                bb.brand_name as borrower_brand_name,
                lba.bank_name as lender_bank_name,
                bba.bank_name as borrower_bank_name,
                u.name as created_by_name
            FROM inter_brand_loans l
            JOIN brands lb ON l.lender_brand_id = lb.id
            JOIN brands bb ON l.borrower_brand_id = bb.id
            JOIN bank_accounts lba ON l.lender_bank_account_id = lba.id
            JOIN bank_accounts bba ON l.borrower_bank_account_id = bba.id
            LEFT JOIN users u ON l.created_by = u.id
            WHERE 1=1";

        $params = [];

        if (!empty($filters['lender_brand_ids']) && is_array($filters['lender_brand_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['lender_brand_ids']), '?'));
            $sql .= " AND l.lender_brand_id IN ($placeholders)";
            foreach ($filters['lender_brand_ids'] as $bId) {
                $params[] = (int)$bId;
            }
        } elseif (!empty($filters['lender_brand_id'])) {
            $sql .= " AND l.lender_brand_id = ?";
            $params[] = $filters['lender_brand_id'];
        }

        if (!empty($filters['borrower_brand_ids']) && is_array($filters['borrower_brand_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['borrower_brand_ids']), '?'));
            $sql .= " AND l.borrower_brand_id IN ($placeholders)";
            foreach ($filters['borrower_brand_ids'] as $bId) {
                $params[] = (int)$bId;
            }
        } elseif (!empty($filters['borrower_brand_id'])) {
            $sql .= " AND l.borrower_brand_id = ?";
            $params[] = $filters['borrower_brand_id'];
        }

        if (!empty($filters['brand_ids']) && is_array($filters['brand_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['brand_ids']), '?'));
            $sql .= " AND (l.lender_brand_id IN ($placeholders) OR l.borrower_brand_id IN ($placeholders))";
            foreach ($filters['brand_ids'] as $bId) {
                $params[] = (int)$bId;
            }
            foreach ($filters['brand_ids'] as $bId) {
                $params[] = (int)$bId;
            }
        } elseif (!empty($filters['brand_id'])) {
            $sql .= " AND (l.lender_brand_id = ? OR l.borrower_brand_id = ?)";
            $params[] = $filters['brand_id'];
            $params[] = $filters['brand_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY l.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get active receivables for a brand (loans given where money is still owed to this brand)
     */
    public static function getReceivables(int $brandId): array {
        return self::all(['lender_brand_id' => $brandId]);
    }

    /**
     * Get active liabilities for a brand (loans taken where this brand owes money)
     */
    public static function getLiabilities(int $brandId): array {
        return self::all(['borrower_brand_id' => $brandId]);
    }
}
