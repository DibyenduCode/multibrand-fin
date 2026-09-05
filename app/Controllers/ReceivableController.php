<?php
require_once __DIR__ . '/../Helpers/Auth.php';
require_once __DIR__ . '/../Helpers/Security.php';
require_once __DIR__ . '/../Helpers/Format.php';
require_once __DIR__ . '/../Models/Brand.php';
require_once __DIR__ . '/../Models/Loan.php';

class ReceivableController {
    public static function index(): void {
        Auth::requireLogin();

        if (Auth::isSuperAdmin() || Auth::isManager()) {
            $receivables = Loan::all();
            $totalOutstanding = 0;
            foreach ($receivables as $r) {
                if (in_array($r['status'], ['active', 'partially_repaid'])) {
                    $totalOutstanding += (float)$r['remaining_amount'];
                }
            }
        } else {
            $userBrandIds = Auth::userBrandIds();
            if (count($userBrandIds) === 1) {
                $receivables = Loan::getReceivables($userBrandIds[0]);
            } else {
                $receivables = Loan::all(['lender_brand_ids' => $userBrandIds]);
            }
            $totalOutstanding = 0;
            foreach ($receivables as $r) {
                if (in_array($r['status'], ['active', 'partially_repaid'])) {
                    $totalOutstanding += (float)$r['remaining_amount'];
                }
            }
        }

        require_once __DIR__ . '/../../views/receivables/index.php';
    }
}
