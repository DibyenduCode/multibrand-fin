<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/BankAccount.php';
require_once __DIR__ . '/../Models/FixedExpense.php';

class FixedExpenseController {
    public static function index(): void {
        Auth::requireLogin();

        if (Auth::isSuperAdmin() || Auth::isManager()) {
            $brands = Brand::all(true);
            $userBrandIds = [];
        } else {
            $brands = User::getUserBrands(Auth::id());
            $userBrandIds = Auth::userBrandIds();
        }

        $selectedBrandId = $_GET['brand_id'] ?? '';
        $filters = [];

        if (Auth::isBrandUser()) {
            if ($selectedBrandId !== '') {
                if (in_array((int)$selectedBrandId, $userBrandIds)) {
                    $filters['brand_id'] = (int)$selectedBrandId;
                } else {
                    $selectedBrandId = (string)($userBrandIds[0] ?? '');
                    if ($selectedBrandId !== '') {
                        $filters['brand_id'] = (int)$selectedBrandId;
                    }
                }
            } else {
                if (count($userBrandIds) === 1) {
                    $selectedBrandId = (string)$userBrandIds[0];
                    $filters['brand_id'] = $userBrandIds[0];
                } elseif (!empty($userBrandIds)) {
                    $filters['brand_ids'] = $userBrandIds;
                }
            }
        } else {
            if ($selectedBrandId !== '') {
                $filters['brand_id'] = (int)$selectedBrandId;
            }
        }

        $fixedExpenses = FixedExpense::all($filters);

        require_once __DIR__ . '/../../views/fixed_expenses/index.php';
    }

    public static function create(): void {
        Auth::requireWriteAccess();

        if (Auth::isSuperAdmin()) {
            $brands = Brand::all(true);
        } else {
            $brands = User::getUserBrands(Auth::id());
        }

        $selectedBrandId = $_GET['brand_id'] ?? ($brands[0]['id'] ?? 0);
        $bankAccounts = BankAccount::getByBrand((int)$selectedBrandId, true);
        $categories = ['Rent', 'Salary', 'Internet', 'Server', 'Software', 'Marketing', 'Office', 'Travel', 'Food', 'Grocery', 'Stationery', 'Loan EMI', 'Domain Buy', 'Domain Renew', 'Utilities', 'Maintenance', 'Other'];

        require_once __DIR__ . '/../../views/fixed_expenses/create.php';
    }

    public static function store(): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/fixed-expenses/create');
            exit;
        }

        $brandId = (int)$_POST['brand_id'];
        Auth::authorizeBrandModification($brandId);

        $title = trim($_POST['title'] ?? '');
        $amount = (float)$_POST['amount'];
        $category = trim($_POST['category'] ?? 'Rent');
        $bankAccountId = (int)$_POST['bank_account_id'];
        $dueDay = (int)($_POST['due_day'] ?? 1);
        $note = trim($_POST['note'] ?? '');

        if (empty($title) || $amount <= 0 || empty($bankAccountId)) {
            Flash::error("Expense Title, Amount, and Preferred Bank Account are required.");
            header('Location: ' . BASE_URL . '/fixed-expenses/create');
            exit;
        }

        FixedExpense::create([
            'brand_id' => $brandId,
            'bank_account_id' => $bankAccountId,
            'title' => $title,
            'amount' => $amount,
            'category' => $category,
            'due_day' => max(1, min(31, $dueDay)),
            'status' => $_POST['status'] ?? 'active',
            'note' => $note,
            'created_by' => Auth::id()
        ]);

        Flash::success("Fixed Monthly Expense '" . $title . "' successfully added!");
        header('Location: ' . BASE_URL . '/fixed-expenses');
        exit;
    }

    public static function edit(int $id): void {
        Auth::requireWriteAccess();

        $fixedExp = FixedExpense::find($id);
        if (!$fixedExp) {
            Flash::error("Fixed Expense not found.");
            header('Location: ' . BASE_URL . '/fixed-expenses');
            exit;
        }

        Auth::authorizeBrandModification((int)$fixedExp['brand_id']);

        if (Auth::isSuperAdmin()) {
            $brands = Brand::all(true);
        } else {
            $brands = User::getUserBrands(Auth::id());
        }

        $bankAccounts = BankAccount::getByBrand((int)$fixedExp['brand_id'], true);
        $categories = ['Rent', 'Salary', 'Internet', 'Server', 'Software', 'Marketing', 'Office', 'Travel', 'Food', 'Grocery', 'Stationery', 'Loan EMI', 'Domain Buy', 'Domain Renew', 'Utilities', 'Maintenance', 'Other'];

        require_once __DIR__ . '/../../views/fixed_expenses/edit.php';
    }

    public static function update(int $id): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/fixed-expenses/' . $id . '/edit');
            exit;
        }

        $fixedExp = FixedExpense::find($id);
        if (!$fixedExp) {
            Flash::error("Fixed Expense not found.");
            header('Location: ' . BASE_URL . '/fixed-expenses');
            exit;
        }

        $brandId = (int)$_POST['brand_id'];
        Auth::authorizeBrandModification($brandId);

        $title = trim($_POST['title'] ?? '');
        $amount = (float)$_POST['amount'];
        $category = trim($_POST['category'] ?? 'Rent');
        $bankAccountId = (int)$_POST['bank_account_id'];
        $dueDay = (int)($_POST['due_day'] ?? 1);
        $note = trim($_POST['note'] ?? '');

        if (empty($title) || $amount <= 0 || empty($bankAccountId)) {
            Flash::error("Expense Title, Amount, and Bank Account are required.");
            header('Location: ' . BASE_URL . '/fixed-expenses/' . $id . '/edit');
            exit;
        }

        FixedExpense::update($id, [
            'brand_id' => $brandId,
            'bank_account_id' => $bankAccountId,
            'title' => $title,
            'amount' => $amount,
            'category' => $category,
            'due_day' => max(1, min(31, $dueDay)),
            'status' => $_POST['status'] ?? 'active',
            'note' => $note
        ]);

        Flash::success("Fixed Monthly Expense '" . $title . "' updated successfully!");
        header('Location: ' . BASE_URL . '/fixed-expenses');
        exit;
    }

    public static function delete(int $id): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/fixed-expenses');
            exit;
        }

        $fixedExp = FixedExpense::find($id);
        if (!$fixedExp) {
            Flash::error("Fixed Expense not found.");
            header('Location: ' . BASE_URL . '/fixed-expenses');
            exit;
        }

        Auth::authorizeBrandModification((int)$fixedExp['brand_id']);

        FixedExpense::delete($id);
        Flash::success("Fixed Monthly Expense '" . $fixedExp['title'] . "' deleted successfully!");
        header('Location: ' . BASE_URL . '/fixed-expenses');
        exit;
    }

    public static function pay(int $id): void {
        Auth::requireWriteAccess();

        $redirectTo = $_POST['redirect_to'] ?? ($_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/fixed-expenses'));

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . $redirectTo);
            exit;
        }

        $fixedExp = FixedExpense::find($id);
        if (!$fixedExp) {
            Flash::error("Fixed Expense not found.");
            header('Location: ' . $redirectTo);
            exit;
        }

        Auth::authorizeBrandModification((int)$fixedExp['brand_id']);

        try {
            $paymentDate = !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
            $bankAccountId = !empty($_POST['bank_account_id']) ? (int)$_POST['bank_account_id'] : null;
            $note = !empty($_POST['note']) ? trim($_POST['note']) : null;

            $txnId = FixedExpense::payMonthlyExpense($id, $paymentDate, Auth::id(), $bankAccountId, $note);
            
            Flash::success("Monthly payment of ₹ " . number_format($fixedExp['amount'], 2) . " for '" . $fixedExp['title'] . "' recorded successfully! Money Out transaction #" . $txnId . " added to ledger.");
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        }

        header('Location: ' . $redirectTo);
        exit;
    }
}
