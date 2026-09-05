<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Format.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/Loan.php';

class LiabilityController {
    public static function index(): void {
        Auth::requireLogin();

        if (Auth::isSuperAdmin() || Auth::isManager()) {
            $liabilities = Loan::all();
            $totalOutstanding = 0;
            foreach ($liabilities as $l) {
                if (in_array($l['status'], ['active', 'partially_repaid'])) {
                    $totalOutstanding += (float)$l['remaining_amount'];
                }
            }
        } else {
            $userBrandIds = Auth::userBrandIds();
            if (count($userBrandIds) === 1) {
                $liabilities = Loan::getLiabilities($userBrandIds[0]);
            } else {
                $liabilities = Loan::all(['borrower_brand_ids' => $userBrandIds]);
            }
            $totalOutstanding = 0;
            foreach ($liabilities as $l) {
                if (in_array($l['status'], ['active', 'partially_repaid'])) {
                    $totalOutstanding += (float)$l['remaining_amount'];
                }
            }
        }

        require_once __DIR__ . '/../../views/liabilities/index.php';
    }
}
