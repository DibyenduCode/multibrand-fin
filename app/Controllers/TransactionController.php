<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Format.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Helpers/Pagination.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/BankAccount.php';
require_once __DIR__ . '/../Models/Transaction.php';
require_once __DIR__ . '/../Models/User.php';

class TransactionController {
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

        $filters = [
            'type' => $_GET['type'] ?? '',
            'date_range' => $_GET['date_range'] ?? '',
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
        ];

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

        $totalItems = Transaction::count($filters);
        $paginationParams = Pagination::getParams($totalItems, 10);

        $filters['offset'] = $paginationParams['offset'];
        $filters['limit'] = $paginationParams['limit'];

        $transactions = Transaction::all($filters);
        $paginationHtml = Pagination::render($paginationParams, BASE_URL . '/transactions');

        require_once __DIR__ . '/../../views/transactions/index.php';
    }

    public static function edit(int $id): void {
        Auth::requireWriteAccess();

        $transaction = Transaction::find($id);
        if (!$transaction) {
            Flash::error("Transaction record not found.");
            header('Location: ' . BASE_URL . '/transactions');
            exit;
        }

        Auth::authorizeBrandModification((int)$transaction['brand_id']);

        if (Auth::isSuperAdmin()) {
            $brands = Brand::all(true);
        } else {
            $brands = User::getUserBrands(Auth::id());
        }

        $bankAccounts = BankAccount::getByBrand((int)$transaction['brand_id'], true);
        $categories = ['Money In', 'Marketing', 'Salary', 'Rent', 'Internet', 'Server', 'Software', 'Office', 'Travel', 'Food', 'Grocery', 'Stationery', 'Loan EMI', 'Domain Buy', 'Domain Renew', 'Utilities', 'Maintenance', 'Inter-Brand Loan', 'Loan Repayment', 'Other'];

        require_once __DIR__ . '/../../views/transactions/edit.php';
    }

    public static function update(int $id): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/transactions/' . $id . '/edit');
            exit;
        }

        $transaction = Transaction::find($id);
        if (!$transaction) {
            Flash::error("Transaction record not found.");
            header('Location: ' . BASE_URL . '/transactions');
            exit;
        }

        $brandId = (int)$_POST['brand_id'];
        Auth::authorizeBrandModification($brandId);

        $type = $_POST['type'] ?? $transaction['type'];
        $amount = (float)$_POST['amount'];
        $purpose = trim($_POST['purpose'] ?? '');
        $category = trim($_POST['category'] ?? 'Other');
        $bankAccountId = (int)$_POST['bank_account_id'];
        $transactionDate = $_POST['transaction_date'] ?? date('Y-m-d');
        $note = trim($_POST['note'] ?? '');

        if ($amount <= 0 || empty($purpose) || empty($bankAccountId)) {
            Flash::error("Amount, Purpose/Details, and Bank Account are required.");
            header('Location: ' . BASE_URL . '/transactions/' . $id . '/edit');
            exit;
        }

        Transaction::update($id, [
            'brand_id' => $brandId,
            'bank_account_id' => $bankAccountId,
            'type' => $type,
            'amount' => $amount,
            'purpose' => $purpose,
            'category' => $category,
            'transaction_date' => $transactionDate,
            'note' => $note
        ]);

        Flash::success("Transaction #" . $id . " updated successfully!");
        header('Location: ' . BASE_URL . '/transactions');
        exit;
    }

    public static function delete(int $id): void {
        Auth::requireWriteAccess();

        $redirectTo = $_POST['redirect_to'] ?? ($_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/transactions'));

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . $redirectTo);
            exit;
        }

        $transaction = Transaction::find($id);
        if (!$transaction) {
            Flash::error("Transaction record not found.");
            header('Location: ' . $redirectTo);
            exit;
        }

        Auth::authorizeBrandModification((int)$transaction['brand_id']);

        Transaction::delete($id);

        Flash::success("Transaction record of ₹ " . number_format($transaction['amount'], 2) . " deleted successfully!");
        header('Location: ' . $redirectTo);
        exit;
    }
}
