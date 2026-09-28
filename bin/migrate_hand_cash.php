<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

echo "Running Hand Cash database migration...\n";

$db = Database::getConnection();

// 1. Check if account_type column exists
$columns = $db->query("SHOW COLUMNS FROM bank_accounts LIKE 'account_type'")->fetchAll();
if (empty($columns)) {
    echo "Adding account_type column to bank_accounts...\n";
    $db->exec("ALTER TABLE `bank_accounts` ADD COLUMN `account_type` ENUM('bank', 'cash') NOT NULL DEFAULT 'bank' AFTER `brand_id`");
    echo "account_type column added successfully.\n";
} else {
    echo "account_type column already exists.\n";
}

// 2. Adjust account_number to allow NULL and default 'CASH'
echo "Updating account_number column constraints...\n";
$db->exec("ALTER TABLE `bank_accounts` MODIFY COLUMN `account_number` VARCHAR(100) NULL DEFAULT 'CASH'");

// 3. Adjust account_holder_name to allow NULL and default 'Cash In Hand'
echo "Updating account_holder_name column constraints...\n";
$db->exec("ALTER TABLE `bank_accounts` MODIFY COLUMN `account_holder_name` VARCHAR(100) NULL DEFAULT 'Cash In Hand'");

// 4. Provision default 'Hand Cash' account for every active brand
echo "Provisioning Hand Cash accounts for brands...\n";
$brands = $db->query("SELECT id, brand_name FROM brands")->fetchAll(PDO::FETCH_ASSOC);

$checkStmt = $db->prepare("SELECT id FROM bank_accounts WHERE brand_id = ? AND account_type = 'cash'");
$insertStmt = $db->prepare("INSERT INTO bank_accounts 
    (brand_id, account_type, bank_name, account_holder_name, account_number, ifsc_code, opening_balance, status, created_at, updated_at) 
    VALUES (?, 'cash', 'Hand Cash', ?, 'CASH', NULL, 0.00, 'active', NOW(), NOW())");

$provisionedCount = 0;
foreach ($brands as $brand) {
    $checkStmt->execute([$brand['id']]);
    $existing = $checkStmt->fetch();
    if (!$existing) {
        $holderName = $brand['brand_name'] . ' Petty Cash';
        $insertStmt->execute([$brand['id'], $holderName]);
        echo " - Created Hand Cash account for brand: {$brand['brand_name']} (ID: {$brand['id']})\n";
        $provisionedCount++;
    } else {
        echo " - Brand {$brand['brand_name']} (ID: {$brand['id']}) already has Hand Cash account (Account ID: {$existing['id']})\n";
    }
}

echo "Migration completed successfully! Provisioned $provisionedCount new Hand Cash accounts.\n";
