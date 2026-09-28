<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Transaction.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Format.php';

class BankAccount {
    public static function getByBrand(int $brandId, bool $activeOnly = false, ?string $accountType = null): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM bank_accounts WHERE brand_id = ?";
        $params = [$brandId];

        if ($activeOnly) {
            $sql .= " AND status = 'active'";
        }
        if (!empty($accountType)) {
            $sql .= " AND account_type = ?";
            $params[] = $accountType;
        }

        $sql .= " ORDER BY (account_type = 'cash') DESC, bank_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $accounts = $stmt->fetchAll();

        foreach ($accounts as &$acc) {
            $acc['current_balance'] = self::getBalance((int)$acc['id']);
        }
        return $accounts;
    }

    public static function getHandCashAccount(int $brandId): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM bank_accounts WHERE brand_id = ? AND account_type = 'cash' LIMIT 1");
        $stmt->execute([$brandId]);
        $acc = $stmt->fetch();

        if (!$acc) {
            $newId = self::provisionDefaultCashAccount($brandId);
            return self::find($newId);
        }

        $acc['current_balance'] = self::getBalance((int)$acc['id']);
        return $acc;
    }

    public static function provisionDefaultCashAccount(int $brandId): int {
        $db = Database::getConnection();
        $checkStmt = $db->prepare("SELECT id FROM bank_accounts WHERE brand_id = ? AND account_type = 'cash' LIMIT 1");
        $checkStmt->execute([$brandId]);
        $existing = $checkStmt->fetchColumn();
        if ($existing) {
            return (int)$existing;
        }

        $brandStmt = $db->prepare("SELECT brand_name FROM brands WHERE id = ?");
        $brandStmt->execute([$brandId]);
        $brandName = $brandStmt->fetchColumn() ?: 'Brand';

        $stmt = $db->prepare("INSERT INTO bank_accounts 
            (brand_id, account_type, bank_name, account_holder_name, account_number, ifsc_code, opening_balance, status, created_at, updated_at) 
            VALUES (?, 'cash', 'Hand Cash', ?, 'CASH', NULL, 0.00, 'active', NOW(), NOW())");
        $stmt->execute([$brandId, $brandName . ' Petty Cash']);
        return (int)$db->lastInsertId();
    }

    public static function getByBrands(array $brandIds, bool $activeOnly = false, ?string $accountType = null): array {
        if (empty($brandIds)) return [];
        $db = Database::getConnection();
        $placeholders = implode(',', array_fill(0, count($brandIds), '?'));
        $sql = "SELECT ba.*, b.brand_name 
                FROM bank_accounts ba 
                JOIN brands b ON ba.brand_id = b.id 
                WHERE ba.brand_id IN ($placeholders)";
        $params = $brandIds;

        if ($activeOnly) {
            $sql .= " AND ba.status = 'active'";
        }
        if (!empty($accountType)) {
            $sql .= " AND ba.account_type = ?";
            $params[] = $accountType;
        }

        $sql .= " ORDER BY b.brand_name ASC, (ba.account_type = 'cash') DESC, ba.bank_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $accounts = $stmt->fetchAll();

        foreach ($accounts as &$acc) {
            $acc['current_balance'] = self::getBalance((int)$acc['id']);
        }
        return $accounts;
    }

    public static function all(?string $accountType = null): array {
        $db = Database::getConnection();
        $sql = "SELECT ba.*, b.brand_name 
                FROM bank_accounts ba 
                JOIN brands b ON ba.brand_id = b.id";
        $params = [];

        if (!empty($accountType)) {
            $sql .= " WHERE ba.account_type = ?";
            $params[] = $accountType;
        }

        $sql .= " ORDER BY b.brand_name ASC, (ba.account_type = 'cash') DESC, ba.bank_name ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $accounts = $stmt->fetchAll();

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
        $stmt = $db->prepare("INSERT INTO bank_accounts (brand_id, account_type, bank_name, account_holder_name, account_number, ifsc_code, opening_balance, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['brand_id'],
            $data['account_type'] ?? 'bank',
            $data['bank_name'],
            $data['account_holder_name'],
            $data['account_number'] ?? 'CASH',
            $data['ifsc_code'] ?? null,
            $data['opening_balance'] ?? 0.00,
            $data['status'] ?? 'active'
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $accountType = $data['account_type'] ?? 'bank';
        $stmt = $db->prepare("UPDATE bank_accounts SET account_type = ?, bank_name = ?, account_holder_name = ?, account_number = ?, ifsc_code = ?, opening_balance = ?, status = ? WHERE id = ?");
        return $stmt->execute([
            $accountType,
            $data['bank_name'],
            $data['account_holder_name'],
            $data['account_number'] ?? ($accountType === 'cash' ? 'CASH' : ''),
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
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE bank_account_id = ? AND type IN ('income', 'loan_received', 'loan_repayment_received', 'transfer_in')");
        $stmt->execute([$bankAccountId]);
        $inflow = (float)$stmt->fetchColumn();

        // Outflow
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE bank_account_id = ? AND type IN ('expense', 'loan_given', 'loan_repayment', 'transfer_out')");
        $stmt->execute([$bankAccountId]);
        $outflow = (float)$stmt->fetchColumn();

        return $opening + $inflow - $outflow;
    }

    public static function hasDependencies(int $bankAccountId): bool {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM transactions WHERE bank_account_id = ?");
        $stmt->execute([$bankAccountId]);
        if (((int)$stmt->fetchColumn()) > 0) return true;

        $stmt = $db->prepare("SELECT COUNT(*) FROM fixed_expenses WHERE bank_account_id = ?");
        $stmt->execute([$bankAccountId]);
        if (((int)$stmt->fetchColumn()) > 0) return true;

        $stmt = $db->prepare("SELECT COUNT(*) FROM inter_brand_loans WHERE lender_bank_account_id = ? OR borrower_bank_account_id = ?");
        $stmt->execute([$bankAccountId, $bankAccountId]);
        if (((int)$stmt->fetchColumn()) > 0) return true;

        return false;
    }

    public static function delete(int $id): bool {
        $acc = self::find($id);
        if ($acc && ($acc['account_type'] ?? '') === 'cash') {
            throw new Exception("Hand Cash accounts cannot be deleted as they are essential to brand cash tracking.");
        }

        if (self::hasDependencies($id)) {
            throw new Exception("Cannot delete bank account because it has linked transactions, fixed expenses, or loans. Consider setting its status to 'Inactive' instead.");
        }
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM bank_accounts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function transfer(array $data): array {
        $db = Database::getConnection();
        $isNested = $db->inTransaction();
        if (!$isNested) {
            $db->beginTransaction();
        }

        try {
            $brandId = (int)$data['brand_id'];
            $fromId = (int)$data['from_bank_account_id'];
            $toId = (int)$data['to_bank_account_id'];
            $amount = (float)$data['amount'];
            $transferDate = !empty($data['transaction_date']) ? $data['transaction_date'] : date('Y-m-d');
            $refNo = !empty($data['reference_number']) ? trim($data['reference_number']) : '';
            $note = !empty($data['note']) ? trim($data['note']) : '';
            $userId = (int)$data['created_by'];

            if ($fromId === $toId) {
                throw new Exception("Source and Destination accounts cannot be the same.");
            }

            if ($amount <= 0) {
                throw new Exception("Transfer amount must be greater than zero.");
            }

            $fromAccount = self::find($fromId);
            $toAccount = self::find($toId);

            if (!$fromAccount || !$toAccount) {
                throw new Exception("Invalid accounts selected.");
            }

            if ((int)$fromAccount['brand_id'] !== $brandId || (int)$toAccount['brand_id'] !== $brandId) {
                throw new Exception("Both accounts must belong to the selected brand.");
            }

            // Check sufficient funds in source account
            $fromBalance = self::getBalance($fromId);
            if ($fromBalance < $amount) {
                throw new Exception(sprintf("Insufficient balance in %s. Available: %s", $fromAccount['bank_name'], Format::currency($fromBalance)));
            }

            $noteSuffix = $refNo !== '' ? " [Ref/UTR: {$refNo}]" : "";
            $fullNote = $note !== '' ? ($note . $noteSuffix) : ($refNo !== '' ? "Ref/UTR: {$refNo}" : null);

            // Determine transfer category and narratives based on account types
            $fromType = $fromAccount['account_type'] ?? 'bank';
            $toType = $toAccount['account_type'] ?? 'bank';

            if ($fromType === 'bank' && $toType === 'cash') {
                $category = 'Cash Withdrawal';
                $fromPurpose = "Cash Withdrawal to Hand Cash";
                $toPurpose = "Cash Withdrawn from " . $fromAccount['bank_name'] . " (" . Security::maskAccountNumber($fromAccount['account_number']) . ")";
            } elseif ($fromType === 'cash' && $toType === 'bank') {
                $category = 'Cash Deposit';
                $fromPurpose = "Cash Deposit to " . $toAccount['bank_name'] . " (" . Security::maskAccountNumber($toAccount['account_number']) . ")";
                $toPurpose = "Cash Deposited from Hand Cash";
            } elseif ($fromType === 'cash' && $toType === 'cash') {
                $category = 'Cash Transfer';
                $fromPurpose = "Cash Transfer to " . $toAccount['bank_name'];
                $toPurpose = "Cash Transfer from " . $fromAccount['bank_name'];
            } else {
                $category = 'Bank Transfer';
                $fromPurpose = "Transfer to " . $toAccount['bank_name'] . " (" . Security::maskAccountNumber($toAccount['account_number']) . ")";
                $toPurpose = "Transfer from " . $fromAccount['bank_name'] . " (" . Security::maskAccountNumber($fromAccount['account_number']) . ")";
            }

            // 1. Transaction: Money Out from Source Account
            $fromTxnId = Transaction::create([
                'brand_id' => $brandId,
                'bank_account_id' => $fromId,
                'type' => 'transfer_out',
                'amount' => $amount,
                'purpose' => $fromPurpose,
                'category' => $category,
                'related_brand_id' => null,
                'reference_type' => 'bank_transfer',
                'reference_id' => $toId,
                'transaction_date' => $transferDate,
                'note' => $fullNote,
                'created_by' => $userId
            ]);

            // 2. Transaction: Money In to Destination Account
            $toTxnId = Transaction::create([
                'brand_id' => $brandId,
                'bank_account_id' => $toId,
                'type' => 'transfer_in',
                'amount' => $amount,
                'purpose' => $toPurpose,
                'category' => $category,
                'related_brand_id' => null,
                'reference_type' => 'bank_transfer',
                'reference_id' => $fromId,
                'transaction_date' => $transferDate,
                'note' => $fullNote,
                'created_by' => $userId
            ]);

            if (!$isNested) {
                $db->commit();
            }

            return [
                'from_transaction_id' => $fromTxnId,
                'to_transaction_id' => $toTxnId,
                'from_account' => $fromAccount,
                'to_account' => $toAccount,
                'amount' => $amount,
                'category' => $category
            ];
        } catch (Exception $e) {
            if (!$isNested && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
