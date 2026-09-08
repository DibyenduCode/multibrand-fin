<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/Transaction.php';
require_once __DIR__ . '/Setting.php';

class FixedExpense {

    private static bool $migrationChecked = false;

    public static function ensureStartMonthColumn(): void {
        if (self::$migrationChecked) return;
        self::$migrationChecked = true;
        try {
            $db = Database::getConnection();
            $db->exec("ALTER TABLE fixed_expenses ADD COLUMN IF NOT EXISTS start_month VARCHAR(7) DEFAULT NULL AFTER due_day");
        } catch (Throwable $e) {
            // Ignore if column exists or fails gracefully
        }
    }

    public static function calculateDefaultStartMonth(int $dueDay): string {
        $currentDay = (int)date('j');
        if ($currentDay > $dueDay) {
            return date('Y-m', strtotime('first day of next month'));
        }
        return date('Y-m');
    }

    public static function create(array $data): int {
        self::ensureStartMonthColumn();
        $dueDay = max(1, min(31, (int)($data['due_day'] ?? 1)));
        $startMonth = !empty($data['start_month']) ? trim($data['start_month']) : self::calculateDefaultStartMonth($dueDay);

        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO fixed_expenses 
            (brand_id, bank_account_id, title, amount, category, due_day, start_month, status, note, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['brand_id'],
            $data['bank_account_id'],
            $data['title'],
            $data['amount'],
            $data['category'] ?? 'Rent',
            $dueDay,
            $startMonth,
            $data['status'] ?? 'active',
            $data['note'] ?? null,
            $data['created_by']
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        self::ensureStartMonthColumn();
        $dueDay = max(1, min(31, (int)($data['due_day'] ?? 1)));
        $startMonth = !empty($data['start_month']) ? trim($data['start_month']) : null;

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE fixed_expenses SET 
            brand_id = ?, bank_account_id = ?, title = ?, amount = ?, category = ?, due_day = ?, start_month = ?, status = ?, note = ? 
            WHERE id = ?");
        return $stmt->execute([
            $data['brand_id'],
            $data['bank_account_id'],
            $data['title'],
            $data['amount'],
            $data['category'],
            $dueDay,
            $startMonth,
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
        self::ensureStartMonthColumn();
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
        self::ensureStartMonthColumn();
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

        $currentMonth = date('Y-m');
        $currentDay = (int)date('j');
        $currentMonthStart = date('Y-m-01');
        $currentMonthEnd = date('Y-m-t');

        foreach ($expenses as &$exp) {
            $dueDay = (int)$exp['due_day'];

            // Determine effective start month
            if (!empty($exp['start_month'])) {
                $effectiveStartMonth = $exp['start_month'];
            } else {
                // Legacy records fallback logic
                if (!empty($exp['created_at'])) {
                    $createdMonth = date('Y-m', strtotime($exp['created_at']));
                    $createdDay = (int)date('j', strtotime($exp['created_at']));
                    if ($createdMonth === $currentMonth && $createdDay > $dueDay) {
                        $effectiveStartMonth = date('Y-m', strtotime('first day of next month', strtotime($exp['created_at'])));
                    } else {
                        $effectiveStartMonth = $createdMonth;
                    }
                } else {
                    $effectiveStartMonth = $currentMonth;
                }
            }
            $exp['effective_start_month'] = $effectiveStartMonth;

            // Check if paid this month
            $chkStmt = $db->prepare("SELECT COUNT(*) FROM transactions 
                WHERE brand_id = ? AND reference_type = 'fixed_expense' AND reference_id = ? 
                AND transaction_date BETWEEN ? AND ?");
            $chkStmt->execute([$exp['brand_id'], $exp['id'], $currentMonthStart, $currentMonthEnd]);
            $isPaid = ((int)$chkStmt->fetchColumn()) > 0;
            $exp['is_paid_this_month'] = $isPaid;

            // Calculate human-friendly status badge & text
            if ($effectiveStartMonth > $currentMonth) {
                $targetMonthName = date('F', strtotime($effectiveStartMonth . '-01'));
                $exp['status_type'] = 'future_start';
                $exp['status_text'] = 'Pending for ' . $targetMonthName;
                $exp['status_badge_class'] = 'bg-sky-100 text-sky-800';
                $exp['status_icon'] = 'fa-solid fa-clock';
            } elseif ($isPaid) {
                $exp['status_type'] = 'paid';
                $exp['status_text'] = 'Paid for ' . date('F');
                $exp['status_badge_class'] = 'bg-emerald-100 text-emerald-800';
                $exp['status_icon'] = 'fa-solid fa-circle-check';
            } else {
                if ($currentDay > $dueDay) {
                    $exp['status_type'] = 'overdue';
                    $exp['status_text'] = 'Overdue for ' . date('F');
                    $exp['status_badge_class'] = 'bg-rose-100 text-rose-800';
                    $exp['status_icon'] = 'fa-solid fa-triangle-exclamation';
                } elseif ($currentDay === $dueDay) {
                    $exp['status_type'] = 'due_today';
                    $exp['status_text'] = 'Due Today (' . date('F') . ')';
                    $exp['status_badge_class'] = 'bg-amber-100 text-amber-800';
                    $exp['status_icon'] = 'fa-solid fa-clock';
                } else {
                    $exp['status_type'] = 'pending';
                    $exp['status_text'] = 'Pending for ' . date('F');
                    $exp['status_badge_class'] = 'bg-amber-100 text-amber-800';
                    $exp['status_icon'] = 'fa-solid fa-clock';
                }
            }
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

        $currentMonth = date('Y-m');
        foreach ($allExpenses as $exp) {
            if (!empty($exp['is_paid_this_month']) || (!empty($exp['effective_start_month']) && $exp['effective_start_month'] > $currentMonth)) {
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

    /**
     * Retrieve all recipient email addresses for a specific brand (brand users + brand contact email)
     */
    public static function getBrandRecipients(int $brandId): array {
        $db = Database::getConnection();
        $emails = [];

        // 1. Get emails of users assigned to this brand
        $sql = "SELECT DISTINCT u.email 
                FROM users u 
                LEFT JOIN user_brands ub ON u.id = ub.user_id 
                WHERE u.status = 'active' AND (ub.brand_id = ? OR u.brand_id = ?) AND u.email IS NOT NULL AND u.email != ''";
        $stmt = $db->prepare($sql);
        $stmt->execute([$brandId, $brandId]);
        $userEmails = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($userEmails as $e) {
            $e = trim(strtolower($e));
            if (filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $e;
            }
        }

        // 2. Get contact email from brands table
        $bStmt = $db->prepare("SELECT email FROM brands WHERE id = ?");
        $bStmt->execute([$brandId]);
        $brandEmail = trim(strtolower((string)$bStmt->fetchColumn()));
        if (!empty($brandEmail) && filter_var($brandEmail, FILTER_VALIDATE_EMAIL)) {
            $emails[] = $brandEmail;
        }

        return array_values(array_unique($emails));
    }

    /**
     * Send email notifications for pending fixed expenses of a specific brand
     */
    public static function sendNotificationsForBrand(int $brandId): array {
        require_once __DIR__ . '/../Helpers/Mailer.php';
        require_once __DIR__ . '/Brand.php';

        $brand = Brand::find($brandId);
        if (!$brand) {
            return ['success' => false, 'sent' => 0, 'message' => "Brand ID {$brandId} not found."];
        }

        $allExpenses = self::all(['brand_id' => $brandId, 'status' => 'active']);
        $pendingExpenses = [];
        $currentDay = (int)date('j');
        $advanceDays = (int)Setting::get('fixed_expense_notification_days', 7);

        $overdueCount = 0;
        $dueTodayCount = 0;
        $dueSoonCount = 0;
        $totalPendingAmount = 0.0;

        $currentMonth = date('Y-m');
        foreach ($allExpenses as $exp) {
            if (!empty($exp['is_paid_this_month']) || (!empty($exp['effective_start_month']) && $exp['effective_start_month'] > $currentMonth)) {
                continue;
            }

            $dueDay = (int)$exp['due_day'];
            $expAmount = (float)$exp['amount'];

            if ($currentDay > $dueDay) {
                $statusType = 'Overdue';
                $statusBadgeClass = 'background-color:#fee2e2; color:#991b1b;';
                $overdueCount++;
            } elseif ($currentDay === $dueDay) {
                $statusType = 'Due Today';
                $statusBadgeClass = 'background-color:#fef3c7; color:#92400e;';
                $dueTodayCount++;
            } else {
                $daysDiff = $dueDay - $currentDay;
                if ($advanceDays > 0 && $daysDiff > $advanceDays) {
                    continue;
                }
                $statusType = "Due in {$daysDiff} day(s)";
                $statusBadgeClass = 'background-color:#e0f2fe; color:#075985;';
                $dueSoonCount++;
            }

            $totalPendingAmount += $expAmount;
            $exp['status_label'] = $statusType;
            $exp['status_style'] = $statusBadgeClass;
            $pendingExpenses[] = $exp;
        }

        if (empty($pendingExpenses)) {
            return ['success' => true, 'sent' => 0, 'message' => "No pending fixed expenses for " . $brand['brand_name'] . "."];
        }

        $recipients = self::getBrandRecipients($brandId);
        if (empty($recipients)) {
            return ['success' => false, 'sent' => 0, 'message' => "No valid email addresses found for " . $brand['brand_name'] . " admins."];
        }

        $monthYear = date('F Y');
        $brandName = htmlspecialchars($brand['brand_name']);
        $totalAmountFormatted = "₹ " . number_format($totalPendingAmount, 2);

        // Build HTML Table Rows
        $tableRowsHtml = "";
        foreach ($pendingExpenses as $item) {
            $title = htmlspecialchars($item['title']);
            $cat = htmlspecialchars($item['category']);
            $dueDay = (int)$item['due_day'];
            $amt = "₹ " . number_format((float)$item['amount'], 2);
            $bank = htmlspecialchars($item['bank_name'] . ' (' . $item['account_number'] . ')');
            $statusLabel = htmlspecialchars($item['status_label']);
            $statusStyle = $item['status_style'];

            $tableRowsHtml .= "
                <tr style='border-bottom: 1px solid #f1f5f9;'>
                    <td style='padding: 12px; font-weight: bold; color: #0f172a;'>{$title}</td>
                    <td style='padding: 12px; color: #475569; font-size: 13px;'>{$cat}</td>
                    <td style='padding: 12px; color: #475569; font-size: 13px;'>{$dueDay}th of month</td>
                    <td style='padding: 12px; color: #475569; font-size: 13px;'>{$bank}</td>
                    <td style='padding: 12px; font-size: 12px;'><span style='display: inline-block; padding: 3px 8px; border-radius: 6px; font-weight: bold; {$statusStyle}'>{$statusLabel}</span></td>
                    <td style='padding: 12px; font-weight: bold; color: #e11d48; text-align: right;'>{$amt}</td>
                </tr>
            ";
        }

        $appUrl = (defined('BASE_URL') ? BASE_URL : 'http://localhost/fin') . '/fixed-expenses?brand_id=' . $brandId;

        $subject = "Fixed Expense Payment Reminders - {$brandName} ({$monthYear})";
        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 680px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 16px; background-color: #ffffff;'>
                <div style='background-color: #0f172a; padding: 20px; text-align: center; border-radius: 12px 12px 0 0;'>
                    <h2 style='color: #ffffff; margin: 0; font-size: 22px; font-weight: 800;'>{$brandName}</h2>
                    <p style='color: #94a3b8; margin: 6px 0 0 0; font-size: 13px;'>Fixed Monthly Expense Reminder &bull; {$monthYear}</p>
                </div>

                <div style='padding: 24px; color: #334155; line-height: 1.6;'>
                    <p style='font-size: 15px; margin-top: 0;'>Hello Brand Admin,</p>
                    <p style='font-size: 14px; color: #475569;'>Here is your brand's current fixed monthly commitments breakdown for <strong>{$monthYear}</strong>. Please ensure prompt payment to avoid service interruptions.</p>

                    <!-- Summary Box -->
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;'>
                        <tr>
                            <td style='padding: 14px; font-size: 13px; color: #64748b;'>Total Pending Expenses: <strong style='color: #0f172a;'>" . count($pendingExpenses) . "</strong></td>
                            <td style='padding: 14px; font-size: 13px; color: #64748b;'>Overdue: <strong style='color: #dc2626;'>{$overdueCount}</strong></td>
                            <td style='padding: 14px; font-size: 13px; color: #64748b;'>Due Today: <strong style='color: #d97706;'>{$dueTodayCount}</strong></td>
                            <td style='padding: 14px; font-size: 13px; color: #64748b; text-align: right;'>Total Due: <strong style='color: #e11d48; font-size: 16px;'>{$totalAmountFormatted}</strong></td>
                        </tr>
                    </table>

                    <!-- Expenses Table -->
                    <table style='width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 24px;'>
                        <thead>
                            <tr style='background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1; font-size: 12px; text-transform: uppercase; color: #475569;'>
                                <th style='padding: 10px 12px;'>Expense Title</th>
                                <th style='padding: 10px 12px;'>Category</th>
                                <th style='padding: 10px 12px;'>Due Date</th>
                                <th style='padding: 10px 12px;'>Bank Account</th>
                                <th style='padding: 10px 12px;'>Status</th>
                                <th style='padding: 10px 12px; text-align: right;'>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$tableRowsHtml}
                        </tbody>
                    </table>

                    <!-- CTA Button -->
                    <div style='text-align: center; margin: 28px 0;'>
                        <a href='{$appUrl}' style='background-color: #0284c7; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 14px; display: inline-block; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);'>
                            View & Pay Fixed Expenses &rarr;
                        </a>
                    </div>
                </div>

                <div style='text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 16px;'>
                    Sent automatically by <strong>Fin Group Management System</strong> &bull; Please do not reply directly to this email.
                </div>
            </div>
        ";

        $sentCount = 0;
        $errors = [];

        foreach ($recipients as $recipientEmail) {
            $res = Mailer::send($recipientEmail, $subject, $htmlBody, $brandName . " Admin");
            if ($res['success']) {
                $sentCount++;
            } else {
                $errors[] = "Failed for {$recipientEmail}: " . $res['message'];
            }
        }

        if ($sentCount > 0) {
            return [
                'success' => true,
                'sent' => $sentCount,
                'recipients' => count($recipients),
                'message' => "Successfully dispatched notification emails to {$sentCount} recipient(s) for {$brandName}."
            ];
        } else {
            return [
                'success' => false,
                'sent' => 0,
                'recipients' => count($recipients),
                'message' => "Failed to send email to recipients for {$brandName}. Error: " . implode("; ", $errors)
            ];
        }
    }

    /**
     * Dispatch email notifications for all active brands
     */
    public static function sendNotificationsForAllBrands(?array $brandIds = null): array {
        require_once __DIR__ . '/Brand.php';
        
        if (!empty($brandIds)) {
            $brands = [];
            foreach ($brandIds as $id) {
                $b = Brand::find((int)$id);
                if ($b) $brands[] = $b;
            }
        } else {
            $brands = Brand::all(true);
        }

        $results = [];
        $totalSent = 0;

        foreach ($brands as $brand) {
            $res = self::sendNotificationsForBrand((int)$brand['id']);
            $results[] = [
                'brand_id' => $brand['id'],
                'brand_name' => $brand['brand_name'],
                'res' => $res
            ];
            $totalSent += ($res['sent'] ?? 0);
        }

        return [
            'total_sent' => $totalSent,
            'details' => $results
        ];
    }
}


