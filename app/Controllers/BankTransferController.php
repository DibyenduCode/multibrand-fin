<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Helpers/Format.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/BankAccount.php';
require_once __DIR__ . '/../Models/User.php';

class BankTransferController {
    public static function create(): void {
        Auth::requireWriteAccess();

        if (Auth::isSuperAdmin()) {
            $brands = Brand::all(true);
        } else {
            $brands = User::getUserBrands(Auth::id());
        }

        if (empty($brands)) {
            Flash::error("No brand assigned to your account to perform funds transfer.");
            header('Location: ' . BASE_URL . '/bank-accounts');
            exit;
        }

        if (Auth::isSuperAdmin()) {
            $selectedBrandId = !empty($_GET['brand_id']) ? (int)$_GET['brand_id'] : (int)$brands[0]['id'];
        } else {
            $userBrandIds = Auth::userBrandIds();
            $selectedBrandId = !empty($_GET['brand_id']) ? (int)$_GET['brand_id'] : (int)$userBrandIds[0];
            if (!in_array($selectedBrandId, $userBrandIds)) {
                $selectedBrandId = (int)$userBrandIds[0];
            }
        }

        $bankAccounts = BankAccount::getByBrand($selectedBrandId, true);
        $fromBankAccountId = !empty($_GET['from_id']) ? (int)$_GET['from_id'] : null;

        require_once __DIR__ . '/../../views/bank_transfers/create.php';
    }

    public static function store(): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/bank-transfers/create');
            exit;
        }

        $brandId = (int)($_POST['brand_id'] ?? 0);
        Auth::authorizeBrandModification($brandId);

        $fromId = (int)($_POST['from_bank_account_id'] ?? 0);
        $toId = (int)($_POST['to_bank_account_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $transactionDate = !empty($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d');
        $referenceNumber = trim($_POST['reference_number'] ?? '');
        $note = trim($_POST['note'] ?? '');

        if ($fromId <= 0 || $toId <= 0) {
            Flash::error("Please select both source and destination accounts.");
            header("Location: " . BASE_URL . "/bank-transfers/create?brand_id=" . $brandId);
            exit;
        }

        if ($fromId === $toId) {
            Flash::error("Source and Destination accounts must be different.");
            header("Location: " . BASE_URL . "/bank-transfers/create?brand_id=" . $brandId);
            exit;
        }

        if ($amount <= 0) {
            Flash::error("Transfer amount must be greater than zero.");
            header("Location: " . BASE_URL . "/bank-transfers/create?brand_id=" . $brandId);
            exit;
        }

        try {
            $result = BankAccount::transfer([
                'brand_id' => $brandId,
                'from_bank_account_id' => $fromId,
                'to_bank_account_id' => $toId,
                'amount' => $amount,
                'transaction_date' => $transactionDate,
                'reference_number' => $referenceNumber,
                'note' => $note,
                'created_by' => Auth::id()
            ]);

            Flash::success(sprintf(
                "%s of %s successfully executed from %s to %s!",
                $result['category'] ?? 'Transfer',
                Format::currency($amount),
                $result['from_account']['bank_name'],
                $result['to_account']['bank_name']
            ));

            header('Location: ' . BASE_URL . '/transactions?brand_id=' . $brandId . '&type=transfer_out');
            exit;
        } catch (Exception $e) {
            Flash::error($e->getMessage());
            header("Location: " . BASE_URL . "/bank-transfers/create?brand_id=" . $brandId . "&from_id=" . $fromId);
            exit;
        }
    }
}
