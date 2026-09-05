<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/BankAccount.php';
require_once __DIR__ . '/../Models/Transaction.php';
require_once __DIR__ . '/../Models/User.php';

class ExpenseController {
    public static function create(): void {
        Auth::requireWriteAccess();

        if (Auth::isSuperAdmin()) {
            $brands = Brand::all(true);
        } else {
            $brands = User::getUserBrands(Auth::id());
        }

        $selectedBrandId = $_GET['brand_id'] ?? ($brands[0]['id'] ?? 0);
        $bankAccounts = BankAccount::getByBrand((int)$selectedBrandId, true);
        $categories = ['Marketing', 'Salary', 'Rent', 'Internet', 'Server', 'Software', 'Office', 'Travel', 'Food', 'Grocery', 'Stationery', 'Loan EMI', 'Domain Buy', 'Domain Renew', 'Utilities', 'Maintenance', 'Other'];

        require_once __DIR__ . '/../../views/expenses/create.php';
    }

    public static function store(): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/expenses/create');
            exit;
        }

        $brandId = (int)$_POST['brand_id'];
        Auth::authorizeBrandModification($brandId);

        $amount = (float)$_POST['amount'];
        $purpose = trim($_POST['purpose'] ?? '');
        $category = trim($_POST['category'] ?? 'Other');
        $bankAccountId = (int)$_POST['bank_account_id'];
        $transactionDate = $_POST['transaction_date'] ?? date('Y-m-d');
        $note = trim($_POST['note'] ?? '');

        if ($amount <= 0 || empty($purpose) || empty($bankAccountId)) {
            Flash::error("Please fill in all required fields properly.");
            header('Location: ' . BASE_URL . '/expenses/create');
            exit;
        }

        Transaction::create([
            'brand_id' => $brandId,
            'bank_account_id' => $bankAccountId,
            'type' => 'expense',
            'amount' => $amount,
            'purpose' => $purpose,
            'category' => $category,
            'transaction_date' => $transactionDate,
            'note' => $note,
            'created_by' => Auth::id()
        ]);

        Flash::success("Expense record of ₹ " . number_format($amount, 2) . " successfully recorded!");
        header('Location: ' . BASE_URL . '/dashboard?brand_id=' . $brandId);
        exit;
    }
}
