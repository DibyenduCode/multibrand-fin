<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Flash.php';
require_once __DIR__ . '/../Helpers/Format.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/BankAccount.php';
require_once __DIR__ . '/../Models/Loan.php';
require_once __DIR__ . '/../Models/LoanRepayment.php';

require_once __DIR__ . '/../Models/User.php';

class LoanController {
    public static function index(): void {
        Auth::requireLogin();

        if (Auth::isSuperAdmin() || Auth::isManager()) {
            $brands = Brand::all(true);
            $loans = Loan::all();
        } else {
            $brands = User::getUserBrands(Auth::id());
            $userBrandIds = Auth::userBrandIds();
            $loans = Loan::all(['brand_ids' => $userBrandIds]);
        }

        require_once __DIR__ . '/../../views/loans/index.php';
    }

    public static function show(int $id): void {
        Auth::requireLogin();

        $loan = Loan::find($id);
        if (!$loan) {
            Flash::error("Loan record not found.");
            header('Location: ' . BASE_URL . '/loans');
            exit;
        }

        if (Auth::isBrandUser()) {
            $userBrandIds = Auth::userBrandIds();
            $lenderId = (int)$loan['lender_brand_id'];
            $borrowerId = (int)$loan['borrower_brand_id'];

            if (!in_array($lenderId, $userBrandIds) && !in_array($borrowerId, $userBrandIds)) {
                Flash::error("Access Denied: You do not have permission to view this loan.");
                header('Location: ' . BASE_URL . '/loans');
                exit;
            }
        }

        $repayments = LoanRepayment::getByLoan($id);

        require_once __DIR__ . '/../../views/loans/show.php';
    }

    public static function create(): void {
        Auth::requireWriteAccess();

        if (Auth::isSuperAdmin() || Auth::isManager()) {
            $lenderBrands = Brand::all(true);
        } else {
            $lenderBrands = User::getUserBrands(Auth::id());
        }

        $allBrands = Brand::all(true);
        $selectedLenderId = isset($_GET['lender_brand_id']) ? (int)$_GET['lender_brand_id'] : (isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0);

        $purposes = [
            'Working Capital Support',
            'Emergency Fund',
            'Project Funding',
            'Operational Support',
            'Temporary Fund Transfer',
            'Other'
        ];

        require_once __DIR__ . '/../../views/loans/create.php';
    }

    public static function store(): void {
        Auth::requireWriteAccess();

        if (!Security::verifyCsrf()) {
            Flash::error("Invalid CSRF token.");
            header('Location: ' . BASE_URL . '/loans/create');
            exit;
        }

        $lenderBrandId = (int)$_POST['lender_brand_id'];
        $borrowerBrandId = (int)$_POST['borrower_brand_id'];

        if ($lenderBrandId === $borrowerBrandId) {
            Flash::error("Lender and Borrower brands must be different.");
            header('Location: ' . BASE_URL . '/loans/create');
            exit;
        }

        // Authorize: User must have write permission for lender brand or super admin
        if (!Auth::isSuperAdmin()) {
            Auth::authorizeBrandModification($lenderBrandId);
        }

        $lender = Brand::find($lenderBrandId);
        $borrower = Brand::find($borrowerBrandId);

        $amount = (float)$_POST['original_amount'];
        $lenderBankId = (int)$_POST['lender_bank_account_id'];
        $borrowerBankId = (int)$_POST['borrower_bank_account_id'];
        $loanDate = $_POST['loan_date'] ?? date('Y-m-d');
        $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $purpose = trim($_POST['purpose'] ?? 'Working Capital Support');
        $description = trim($_POST['description'] ?? '');

        if ($amount <= 0 || empty($lenderBankId) || empty($borrowerBankId)) {
            Flash::error("Please fill in all required loan fields properly.");
            header('Location: ' . BASE_URL . '/loans/create');
            exit;
        }

        try {
            $loanId = Loan::create([
                'lender_brand_id' => $lenderBrandId,
                'borrower_brand_id' => $borrowerBrandId,
                'lender_bank_account_id' => $lenderBankId,
                'borrower_bank_account_id' => $borrowerBankId,
                'original_amount' => $amount,
                'loan_date' => $loanDate,
                'due_date' => $dueDate,
                'purpose' => $purpose,
                'description' => $description,
                'created_by' => Auth::id(),
                'lender_brand_name' => $lender['brand_name'],
                'borrower_brand_name' => $borrower['brand_name']
            ]);

            Flash::success("Inter-Brand Loan of ₹ " . number_format($amount, 2) . " successfully created!");
            header('Location: ' . BASE_URL . '/loans/' . $loanId);
            exit;
        } catch (Exception $e) {
            Flash::error("Error creating loan: " . $e->getMessage());
            header('Location: ' . BASE_URL . '/loans/create');
            exit;
        }
    }
}
