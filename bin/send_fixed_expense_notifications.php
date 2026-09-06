<?php
/**
 * CLI Automation Script - Send Fixed Expense Email Notifications to Brand Admins
 * 
 * Usage:
 *   php bin/send_fixed_expense_notifications.php
 * 
 * Can be scheduled via Windows Task Scheduler or Linux Cron (e.g. daily at 08:00 AM).
 */

if (php_sapi_name() !== 'cli') {
    die("Access Denied: This script can only be executed via PHP CLI command line.\n");
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Models/Setting.php';
require_once __DIR__ . '/../app/Helpers/Mailer.php';
require_once __DIR__ . '/../app/Models/Brand.php';
require_once __DIR__ . '/../app/Models/FixedExpense.php';

echo "[" . date('Y-m-d H:i:s') . "] Starting Fixed Expense Email Notification Dispatch...\n";

if (!Mailer::isEnabled()) {
    echo "[" . date('Y-m-d H:i:s') . "] WARNING: SMTP is currently disabled or incomplete in settings. Aborting.\n";
    exit(1);
}

$res = FixedExpense::sendNotificationsForAllBrands();

echo "[" . date('Y-m-d H:i:s') . "] Dispatch Finished. Total emails sent: " . ($res['total_sent'] ?? 0) . "\n";
echo "Details:\n";
foreach ($res['details'] as $item) {
    echo " - Brand: " . $item['brand_name'] . " | Status: " . ($item['res']['message'] ?? 'Done') . "\n";
}

exit(0);
