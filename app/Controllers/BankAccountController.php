<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/BankAccount.php';

class BankAccountController {
    public static function index(): void {
        Auth::requireLogin();

        if (Auth::isSuperAdmin() || Auth::isManager()) {
            $accounts = BankAccount::all();
        } else {
            $userBrandIds = Auth::userBrandIds();
            $accounts = BankAccount::getByBrands($userBrandIds);
        }

        require_once __DIR__ . '/../../views/bank_accounts/index.php';
    }

    public static function create(): void {
        Auth::requireWriteAccess();

        if (Auth::isSuperAdmin()) {
            $brands = Brand::all(true);
        } else {
            $brands = User::getUserBrands(Auth::id());
        }

        require_once __DIR__ . '/../../views/bank_accounts/create.php';
    }

    public static function store(): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/bank-accounts/create');
            exit;
        }

        $brandId = (int)$_POST['brand_id'];
        Auth::authorizeBrandModification($brandId);

        $bankName = trim($_POST['bank_name'] ?? '');
        $accountHolderName = trim($_POST['account_holder_name'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $ifscCode = trim($_POST['ifsc_code'] ?? '');
        $openingBalance = (float)($_POST['opening_balance'] ?? 0.00);

        if (empty($bankName) || empty($accountHolderName) || empty($accountNumber)) {
            Flash::error("Bank Name, Account Holder Name, and Account Number are required.");
            header('Location: ' . BASE_URL . '/bank-accounts/create');
            exit;
        }

        BankAccount::create([
            'brand_id' => $brandId,
            'bank_name' => $bankName,
            'account_holder_name' => $accountHolderName,
            'account_number' => $accountNumber,
            'ifsc_code' => $ifscCode,
            'opening_balance' => $openingBalance,
            'status' => 'active'
        ]);

        Flash::success("Bank Account added successfully!");
        header('Location: ' . BASE_URL . '/bank-accounts');
        exit;
    }

    public static function edit(int $id): void {
        Auth::requireWriteAccess();

        $account = BankAccount::find($id);
        if (!$account) {
            Flash::error("Bank account not found.");
            header('Location: ' . BASE_URL . '/bank-accounts');
            exit;
        }

        Auth::authorizeBrandModification((int)$account['brand_id']);

        require_once __DIR__ . '/../../views/bank_accounts/edit.php';
    }

    public static function update(int $id): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/bank-accounts/' . $id . '/edit');
            exit;
        }

        $account = BankAccount::find($id);
        if (!$account) {
            Flash::error("Bank account not found.");
            header('Location: ' . BASE_URL . '/bank-accounts');
            exit;
        }

        Auth::authorizeBrandModification((int)$account['brand_id']);

        $bankName = trim($_POST['bank_name'] ?? '');
        $accountHolderName = trim($_POST['account_holder_name'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $ifscCode = trim($_POST['ifsc_code'] ?? '');
        $openingBalance = (float)($_POST['opening_balance'] ?? 0.00);
        $status = $_POST['status'] ?? 'active';

        if (empty($bankName) || empty($accountHolderName) || empty($accountNumber)) {
            Flash::error("Bank Name, Account Holder Name, and Account Number are required.");
            header('Location: ' . BASE_URL . '/bank-accounts/' . $id . '/edit');
            exit;
        }

        BankAccount::update($id, [
            'bank_name' => $bankName,
            'account_holder_name' => $accountHolderName,
            'account_number' => $accountNumber,
            'ifsc_code' => $ifscCode,
            'opening_balance' => $openingBalance,
            'status' => $status
        ]);

        Flash::success("Bank Account updated successfully!");
        header('Location: ' . BASE_URL . '/bank-accounts');
        exit;
    }

    public static function delete(int $id): void {
        Auth::requireLogin();

        if (!Auth::isSuperAdmin()) {
            Flash::error("Access denied. Only Super Admin can delete bank accounts.");
            header('Location: ' . BASE_URL . '/bank-accounts');
            exit;
        }

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/bank-accounts');
            exit;
        }

        $account = BankAccount::find($id);
        if (!$account) {
            Flash::error("Bank account not found.");
            header('Location: ' . BASE_URL . '/bank-accounts');
            exit;
        }

        try {
            BankAccount::delete($id);
            Flash::success("Bank Account '" . $account['bank_name'] . "' deleted successfully!");
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        }

        header('Location: ' . BASE_URL . '/bank-accounts');
        exit;
    }
}
