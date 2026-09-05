<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/BankAccount.php';
require_once __DIR__ . '/../Models/Transaction.php';
require_once __DIR__ . '/../Models/FixedExpense.php';
require_once __DIR__ . '/../Models/User.php';

class DashboardController {
    public static function index(): void {
        Auth::requireLogin();

        $selectedBrandId = $_GET['brand_id'] ?? null;

        if (Auth::isBrandUser()) {
            $userBrandIds = Auth::userBrandIds();
            if (empty($userBrandIds)) {
                die("Access Denied: No brand assigned to your account.");
            }
            if ($selectedBrandId) {
                if (!in_array((int)$selectedBrandId, $userBrandIds)) {
                    $selectedBrandId = $userBrandIds[0];
                }
            } else {
                $selectedBrandId = $userBrandIds[0];
            }
            self::renderBrandDashboard((int)$selectedBrandId);
            return;
        }

        if ($selectedBrandId) {
            self::renderBrandDashboard((int)$selectedBrandId);
        } else {
            self::renderGroupDashboard();
        }
    }

    private static function renderGroupDashboard(): void {
        $groupStats = Brand::getGroupFinancialStats();
        $brandOverview = [];
        $brands = Brand::all(true);

        foreach ($brands as $b) {
            $stats = Brand::getBrandFinancialStats((int)$b['id']);
            $brandOverview[] = array_merge($b, $stats);
        }

        $recentActivity = Transaction::all(['limit' => 6]);

        require_once __DIR__ . '/../../views/dashboard/group_view.php';
    }

    private static function renderBrandDashboard(int $brandId): void {
        if (Auth::isBrandUser()) {
            $userBrandIds = Auth::userBrandIds();
            if (!in_array($brandId, $userBrandIds)) {
                $brandId = $userBrandIds[0] ?? $brandId;
            }
            $brands = User::getUserBrands(Auth::id());
        } else {
            $brands = Brand::all(true);
        }

        $currentBrand = Brand::find($brandId);
        if (!$currentBrand) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }

        $brandStats = Brand::getBrandFinancialStats($brandId);
        $bankAccounts = BankAccount::getByBrand($brandId, true);
        $recentActivity = Transaction::all(['brand_id' => $brandId, 'limit' => 6]);
        $fixedExpenses = FixedExpense::getByBrand($brandId);

        $brandOverview = [];
        foreach ($brands as $b) {
            $stats = Brand::getBrandFinancialStats((int)$b['id']);
            $brandOverview[] = array_merge($b, $stats);
        }

        require_once __DIR__ . '/../../views/dashboard/brand_view.php';
    }
}
