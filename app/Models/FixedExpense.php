<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Transaction.php';
require_once __DIR__ . '/Setting.php';

class FixedExpense {

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO fixed_expenses 
            (brand_id, bank_account_id, title, amount, category, due_day, status, note, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['brand_id'],
            $data['bank_account_id'],
            $data['title'],
            $data['amount'],
            $data['category'] ?? 'Rent',
            $data['due_day'] ?? 1,
            $data['status'] ?? 'active',
            $data['note'] ?? null,
            $data['created_by']
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE fixed_expenses SET 
            brand_id = ?, bank_account_id = ?, title = ?, amount = ?, category = ?, due_day = ?, status = ?, note = ? 
            WHERE id = ?");
        return $stmt->execute([
            $data['brand_id'],
            $data['bank_account_id'],
            $data['title'],
            $data['amount'],
            $data['category'],
            $data['due_day'],
            $data['status'] ?? 'active',
            $data['note'] ?? null,
            $id
        ]);
    }

    public static function delete(int $id): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM fixed_expenses WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT fe.*, b.brand_name, ba.bank_name, ba.account_number 
            FROM fixed_expenses fe 
            JOIN brands b ON fe.brand_id = b.id 
            JOIN bank_accounts ba ON fe.bank_account_id = ba.id 
            WHERE fe.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function all(array $filters = []): array {
        $db = Database::getConnection();
        $sql = "SELECT fe.*, b.brand_name, ba.bank_name, ba.account_number, u.name as created_by_name 
                FROM fixed_expenses fe 
                JOIN brands b ON fe.brand_id = b.id 
                JOIN bank_accounts ba ON fe.bank_account_id = ba.id 
                LEFT JOIN users u ON fe.created_by = u.id 
                WHERE 1=1";
        $params = [];

        if (!empty($filters['brand_ids']) && is_array($filters['brand_ids'])) {
            $placeholders = implode(',', array_fill(0, count($filters['brand_ids']), '?'));
            $sql .= " AND fe.brand_id IN ($placeholders)";
            foreach ($filters['brand_ids'] as $bId) {
                $params[] = (int)$bId;
            }
        } elseif (!empty($filters['brand_id'])) {
            $sql .= " AND fe.brand_id = ?";
            $params[] = $filters['brand_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND fe.status = ?";
            $params[] = $filters['status'];
        }

        $sql .= " ORDER BY fe.due_day ASC, fe.title ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $expenses = $stmt->fetchAll();

        $currentMonthStart = date('Y-m-01');
        $currentMonthEnd = date('Y-m-t');

        foreach ($expenses as &$exp) {
            // Check if paid this month
            $chkStmt = $db->prepare("SELECT COUNT(*) FROM transactions 
                WHERE brand_id = ? AND reference_type = 'fixed_expense' AND reference_id = ? 
                AND transaction_date BETWEEN ? AND ?");
            $chkStmt->execute([$exp['brand_id'], $exp['id'], $currentMonthStart, $currentMonthEnd]);
            $exp['is_paid_this_month'] = ((int)$chkStmt->fetchColumn()) > 0;
        }

        return $expenses;
    }

    public static function getByBrand(int $brandId): array {
        return self::all(['brand_id' => $brandId]);
    }

    public static function payMonthlyExpense(int $id, string $paymentDate, int $createdById, ?int $bankAccountId = null, ?string $note = null): int {
        $fixedExp = self::find($id);
        if (!$fixedExp) {
            throw new Exception("Fixed Monthly Expense record not found.");
        }

        $targetBankId = ($bankAccountId && $bankAccountId > 0) ? $bankAccountId : (int)$fixedExp['bank_account_id'];
        $paymentNote = !empty($note) ? trim($note) : ('Paid fixed monthly commitment: ' . $fixedExp['title'] . ' for ' . date('F Y', strtotime($paymentDate)));

        return Transaction::create([
            'brand_id' => (int)$fixedExp['brand_id'],
            'bank_account_id' => $targetBankId,
            'type' => 'expense',
            'amount' => (float)$fixedExp['amount'],
            'purpose' => 'Fixed Monthly Expense: ' . $fixedExp['title'],
            'category' => $fixedExp['category'],
            'reference_type' => 'fixed_expense',
            'reference_id' => (int)$fixedExp['id'],
            'transaction_date' => $paymentDate,
            'note' => $paymentNote,
            'created_by' => $createdById
        ]);
    }

    public static function getNotificationsForUser(?int $userId = null): array {
        if (Auth::isSuperAdmin()) {
            return [
                'total_pending_count' => 0,
                'overdue_count' => 0,
                'due_today_count' => 0,
                'due_soon_count' => 0,
                'total_pending_amount' => 0.0,
                'items' => []
            ];
        }

        $userBrandIds = Auth::userBrandIds();
        if (empty($userBrandIds)) {
            return [
                'total_pending_count' => 0,
                'overdue_count' => 0,
                'due_today_count' => 0,
                'due_soon_count' => 0,
                'total_pending_amount' => 0.0,
                'items' => []
            ];
        }

        $allExpenses = self::all(['brand_ids' => $userBrandIds, 'status' => 'active']);
        $currentDay = (int)date('j');
        $advanceDays = (int)Setting::get('fixed_expense_notification_days', 7);
        
        $items = [];
        $overdueCount = 0;
        $dueTodayCount = 0;
        $dueSoonCount = 0;
        $totalPendingAmount = 0.0;

        foreach ($allExpenses as $exp) {
            if (!empty($exp['is_paid_this_month'])) {
                continue;
            }

            $dueDay = (int)$exp['due_day'];
            $expAmount = (float)$exp['amount'];

            if ($currentDay > $dueDay) {
                $statusType = 'overdue';
                $daysDiff = $currentDay - $dueDay;
                $statusText = 'Overdue by ' . $daysDiff . ' ' . ($daysDiff === 1 ? 'day' : 'days');
                $overdueCount++;
                $urgency = 1;
            } elseif ($currentDay === $dueDay) {
                $statusType = 'due_today';
                $statusText = 'Due Today';
                $dueTodayCount++;
                $urgency = 2;
            } else {
                $daysDiff = $dueDay - $currentDay;
                // If advance notification limit is configured (> 0) and days remaining exceeds lead window, hide from notification bell
                if ($advanceDays > 0 && $daysDiff > $advanceDays) {
                    continue;
                }
                $statusType = 'due_soon';
                $statusText = 'Due in ' . $daysDiff . ' ' . ($daysDiff === 1 ? 'day' : 'days');
                $dueSoonCount++;
                $urgency = 3;
            }

            $totalPendingAmount += $expAmount;
            $exp['notification_status'] = $statusType;
            $exp['notification_text'] = $statusText;
            $exp['urgency'] = $urgency;

            $items[] = $exp;
        }


        usort($items, function($a, $b) {
            if ($a['urgency'] !== $b['urgency']) {
                return $a['urgency'] <=> $b['urgency'];
            }
            return $a['due_day'] <=> $b['due_day'];
        });

        return [
            'total_pending_count' => count($items),
            'overdue_count' => $overdueCount,
            'due_today_count' => $dueTodayCount,
            'due_soon_count' => $dueSoonCount,
            'total_pending_amount' => $totalPendingAmount,
            'items' => $items
        ];
    }
}

