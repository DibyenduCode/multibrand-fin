<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/Helpers/Auth.php';
require_once __DIR__ . '/../../app/Helpers/Security.php';
require_once __DIR__ . '/../../app/Models/BankAccount.php';

if (!Auth::check()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$brandId = !empty($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
if ($brandId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid brand ID']);
    exit;
}

$accounts = BankAccount::getByBrand($brandId, true);
$formatted = [];
foreach ($accounts as $acc) {
    $formatted[] = [
        'id' => $acc['id'],
        'bank_name' => $acc['bank_name'],
        'masked_account' => Security::maskAccountNumber($acc['account_number']),
        'current_balance' => $acc['current_balance']
    ];
}

echo json_encode(['success' => true, 'accounts' => $formatted]);
exit;
