<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/BankAccount.php';
require_once __DIR__ . '/../Models/Transaction.php';
require_once __DIR__ . '/../Models/User.php';

class MoneyInController {
    public static function create(): void {
        Auth::requireWriteAccess();

        if (Auth::isSuperAdmin()) {
            $brands = Brand::all(true);
        } else {
            $brands = User::getUserBrands(Auth::id());
        }

        $selectedBrandId = $_GET['brand_id'] ?? ($brands[0]['id'] ?? 0);
        $bankAccounts = BankAccount::getByBrand((int)$selectedBrandId, true);

        require_once __DIR__ . '/../../views/money_in/create.php';
    }

    public static function store(): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/money-in/create');
            exit;
        }

        $brandId = (int)$_POST['brand_id'];
        Auth::authorizeBrandModification($brandId);

        $amount = (float)$_POST['amount'];
        $purpose = trim($_POST['purpose'] ?? '');
        $bankAccountId = (int)$_POST['bank_account_id'];
        $transactionDate = $_POST['transaction_date'] ?? date('Y-m-d');
        $note = trim($_POST['note'] ?? '');

        if ($amount <= 0 || empty($purpose) || empty($bankAccountId)) {
            Flash::error("Please fill in all required fields properly.");
            header('Location: ' . BASE_URL . '/money-in/create');
            exit;
        }

        Transaction::create([
            'brand_id' => $brandId,
            'bank_account_id' => $bankAccountId,
            'type' => 'income',
            'amount' => $amount,
            'purpose' => $purpose,
            'category' => 'Money In',
            'transaction_date' => $transactionDate,
            'note' => $note,
            'created_by' => Auth::id()
        ]);

        Flash::success("Money In record of ₹ " . number_format($amount, 2) . " successfully added!");
        header('Location: ' . BASE_URL . '/dashboard?brand_id=' . $brandId);
        exit;
    }
}
