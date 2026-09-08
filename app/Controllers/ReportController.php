<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Format.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/Report.php';
require_once __DIR__ . '/../Models/Loan.php';

require_once __DIR__ . '/../Models/User.php';

class ReportController {
    public static function daily(): void {
        Auth::requireLogin();
        
        $brands = Brand::all(true);
        $selectedBrandId = !empty($_GET['brand_id']) ? (int)$_GET['brand_id'] : null;
        $date = !empty($_GET['date']) ? $_GET['date'] : date('Y-m-d');

        $dailyReport = Report::getDailyReport($selectedBrandId, $date);
        $groupStats = Brand::getGroupFinancialStats();

        require_once __DIR__ . '/../../views/reports/daily.php';
    }

    public static function monthly(): void {
        Auth::requireLogin();

        $brands = Brand::all(true);
        $selectedBrandId = !empty($_GET['brand_id']) ? (int)$_GET['brand_id'] : null;
        $yearMonth = !empty($_GET['year_month']) ? $_GET['year_month'] : date('Y-m');

        $monthlyReport = Report::getMonthlyReport($selectedBrandId, $yearMonth);
        $groupStats = Brand::getGroupFinancialStats();

        require_once __DIR__ . '/../../views/reports/monthly.php';
    }

    public static function brandReport(): void {
        Auth::requireLogin();

        $brands = Brand::all(true);
        $reports = [];
        $groupStats = Brand::getGroupFinancialStats();

        $groupTotals = [
            'available_money' => 0.0,
            'total_income' => 0.0,
            'total_expenses' => 0.0,
            'loans_taken' => 0.0,
            'loans_given' => 0.0,
            'outstanding_liability' => 0.0,
            'outstanding_receivable' => 0.0
        ];

        foreach ($brands as $b) {
            $r = Report::getBrandReport((int)$b['id']);
            if (!empty($r)) {
                $reports[] = $r;

                $groupTotals['available_money'] += (float)$r['available_money'];
                $groupTotals['total_income'] += (float)$r['total_income'];
                $groupTotals['total_expenses'] += (float)$r['total_expenses'];
                $groupTotals['loans_taken'] += (float)$r['loans_taken'];
                $groupTotals['loans_given'] += (float)$r['loans_given'];
                $groupTotals['outstanding_liability'] += (float)$r['outstanding_liability'];
                $groupTotals['outstanding_receivable'] += (float)$r['outstanding_receivable'];
            }
        }

        require_once __DIR__ . '/../../views/reports/brand.php';
    }

    public static function loansReport(): void {
        Auth::requireLogin();

        $loans = Loan::all();
        $groupStats = Brand::getGroupFinancialStats();

        require_once __DIR__ . '/../../views/reports/loans.php';
    }
}
