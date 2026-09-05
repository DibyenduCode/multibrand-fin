<?php
$pageTitle = 'Inter-Brand Loans Report';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="flex border-b border-slate-200 gap-4 overflow-x-auto text-sm font-semibold">
            <a href="<?= BASE_URL ?>/reports/daily" class="py-2.5 px-4 text-slate-500 hover:text-slate-700">Daily Report</a>
            <a href="<?= BASE_URL ?>/reports/monthly" class="py-2.5 px-4 text-slate-500 hover:text-slate-700">Monthly Report</a>
            <a href="<?= BASE_URL ?>/reports/brand" class="py-2.5 px-4 text-slate-500 hover:text-slate-700">Brand Financial Summary</a>
            <a href="<?= BASE_URL ?>/reports/loans" class="py-2.5 px-4 text-sky-600 border-b-2 border-sky-600">Inter-Brand Loans Report</a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Inter-Brand Loan Position Report</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Lender</th>
                            <th class="px-5 py-3.5">Borrower</th>
                            <th class="px-5 py-3.5 text-right">Original Amount</th>
                            <th class="px-5 py-3.5 text-right">Repaid</th>
                            <th class="px-5 py-3.5 text-right">Remaining Balance</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($loans)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">No loan records available.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($loans as $l): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-5 py-4 font-bold text-slate-900"><?= e($l['lender_brand_name']) ?></td>
                                    <td class="px-5 py-4 font-bold text-slate-900"><?= e($l['borrower_brand_name']) ?></td>
                                    <td class="px-5 py-4 text-right font-semibold text-slate-900"><?= Format::currency($l['original_amount']) ?></td>
                                    <td class="px-5 py-4 text-right font-semibold text-emerald-600"><?= Format::currency($l['total_repaid']) ?></td>
                                    <td class="px-5 py-4 text-right font-extrabold text-amber-600"><?= Format::currency($l['remaining_amount']) ?></td>
                                    <td class="px-5 py-4 text-center">
                                        <?php if ($l['status'] === 'paid'): ?>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Paid</span>
                                        <?php elseif ($l['status'] === 'partially_repaid'): ?>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Partially Repaid</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800">Active</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
