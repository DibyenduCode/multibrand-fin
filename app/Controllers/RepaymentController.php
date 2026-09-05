<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/BankAccount.php';
require_once __DIR__ . '/../Models/Loan.php';
require_once __DIR__ . '/../Models/LoanRepayment.php';

class RepaymentController {
    public static function create(): void {
        Auth::requireWriteAccess();

        $loanId = $_GET['loan_id'] ?? null;
        if (Auth::isSuperAdmin()) {
            $activeLoans = Loan::all(['status' => 'active']);
            $partiallyPaid = Loan::all(['status' => 'partially_repaid']);
        } else {
            $userBrandIds = Auth::userBrandIds();
            $activeLoans = Loan::all(['status' => 'active', 'borrower_brand_ids' => $userBrandIds]);
            $partiallyPaid = Loan::all(['status' => 'partially_repaid', 'borrower_brand_ids' => $userBrandIds]);
        }
        $loans = array_merge($activeLoans, $partiallyPaid);

        $selectedLoan = null;
        $fromBankAccounts = [];
        $toBankAccounts = [];

        if ($loanId) {
            $selectedLoan = Loan::find((int)$loanId);
            if ($selectedLoan) {
                $fromBankAccounts = BankAccount::getByBrand((int)$selectedLoan['borrower_brand_id'], true);
                $toBankAccounts = BankAccount::getByBrand((int)$selectedLoan['lender_brand_id'], true);
            }
        }

        require_once __DIR__ . '/../../views/repayments/create.php';
    }

    public static function store(): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/repayments/create');
            exit;
        }

        $loanId = (int)$_POST['loan_id'];
        $loan = Loan::find($loanId);
        if (!$loan) {
            Flash::error("Loan record not found.");
            header('Location: ' . BASE_URL . '/repayments/create');
            exit;
        }

        // Authorize: Borrower brand user or super admin
        if (!Auth::isSuperAdmin()) {
            Auth::authorizeBrandModification((int)$loan['borrower_brand_id']);
        }

        $amount = (float)$_POST['amount'];
        $fromBankAccountId = (int)$_POST['from_bank_account_id'];
        $toBankAccountId = (int)$_POST['to_bank_account_id'];
        $paymentDate = $_POST['payment_date'] ?? date('Y-m-d');
        $paymentReference = trim($_POST['payment_reference'] ?? '');
        $description = trim($_POST['description'] ?? '');

        try {
            LoanRepayment::create([
                'loan_id' => $loanId,
                'amount' => $amount,
                'from_bank_account_id' => $fromBankAccountId,
                'to_bank_account_id' => $toBankAccountId,
                'payment_date' => $paymentDate,
                'payment_reference' => $paymentReference,
                'description' => $description,
                'created_by' => Auth::id()
            ]);

            Flash::success("Loan Repayment of ₹ " . number_format($amount, 2) . " successfully recorded!");
            header('Location: ' . BASE_URL . '/loans/' . $loanId);
            exit;
        } catch (Exception $e) {
            Flash::error($e->getMessage());
            header('Location: ' . BASE_URL . '/repayments/create?loan_id=' . $loanId);
            exit;
        }
    }
}
