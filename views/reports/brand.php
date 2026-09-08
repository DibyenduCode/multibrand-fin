<?php
$pageTitle = 'Brand Financial Summary Report';
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
            <a href="<?= BASE_URL ?>/reports/brand" class="py-2.5 px-4 text-sky-600 border-b-2 border-sky-600">Brand Financial Summary</a>
            <a href="<?= BASE_URL ?>/reports/loans" class="py-2.5 px-4 text-slate-500 hover:text-slate-700">Inter-Brand Loans Report</a>
        </div>

        <!-- FINANCIAL HIGHLIGHT CARD -->
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-700/60 pb-6">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-sky-400 block mb-1">
                        ENTIRE BUSINESS GROUP FINANCIAL SUMMARY
                    </span>
                    <h2 class="text-sm font-medium text-slate-300">
                        TOTAL GROUP COMBINED FUNDS (AVAILABLE CASH)
                    </h2>
                    <div class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mt-1">
                        <?= Format::currency($groupTotals['available_money']) ?>
                    </div>
                </div>
                <div class="text-xs text-slate-300 bg-white/10 p-3.5 rounded-xl border border-white/10 space-y-1">
                    <div><i class="fa-solid fa-layer-group text-sky-400 mr-1.5"></i> Combined across <?= count($reports) ?> Active Brands</div>
                    <div><i class="fa-solid fa-shield-halved text-emerald-400 mr-1.5"></i> Real-time multi-brand audit report</div>
                </div>
            </div>

            <!-- Group Financial Metrics Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-800/60 backdrop-blur-xs rounded-xl p-4 border border-slate-700/50">
                    <div class="text-xs font-medium text-slate-400 mb-1">TOTAL INCOME</div>
                    <div class="text-lg sm:text-xl font-bold text-emerald-400">
                        <?= Format::currency($groupTotals['total_income']) ?>
                    </div>
                </div>
                <div class="bg-slate-800/60 backdrop-blur-xs rounded-xl p-4 border border-slate-700/50">
                    <div class="text-xs font-medium text-slate-400 mb-1">TOTAL EXPENSES</div>
                    <div class="text-lg sm:text-xl font-bold text-rose-400">
                        <?= Format::currency($groupTotals['total_expenses']) ?>
                    </div>
                </div>
                <div class="bg-slate-800/60 backdrop-blur-xs rounded-xl p-4 border border-slate-700/50">
                    <div class="text-xs font-medium text-slate-400 mb-1">TOTAL RECEIVABLES</div>
                    <div class="text-lg sm:text-xl font-bold text-sky-400">
                        <?= Format::currency($groupTotals['outstanding_receivable']) ?>
                    </div>
                </div>
                <div class="bg-slate-800/60 backdrop-blur-xs rounded-xl p-4 border border-slate-700/50">
                    <div class="text-xs font-medium text-slate-400 mb-1">TOTAL LIABILITIES</div>
                    <div class="text-lg sm:text-xl font-bold text-amber-400">
                        <?= Format::currency($groupTotals['outstanding_liability']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Individual Brand Financial Audit Breakdown</h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Brand</th>
                            <th class="px-5 py-3.5 text-right">Available Cash</th>
                            <th class="px-5 py-3.5 text-right">Total Income</th>
                            <th class="px-5 py-3.5 text-right">Total Expenses</th>
                            <th class="px-5 py-3.5 text-right">Loans Taken</th>
                            <th class="px-5 py-3.5 text-right">Loans Given</th>
                            <th class="px-5 py-3.5 text-right">Outstanding Liability</th>
                            <th class="px-5 py-3.5 text-right">Outstanding Receivable</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($reports as $r): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-4 font-bold text-slate-900">
                                    <?= e($r['brand']['brand_name']) ?>
                                </td>
                                <td class="px-5 py-4 text-right font-extrabold text-slate-900"><?= Format::currency($r['available_money']) ?></td>
                                <td class="px-5 py-4 text-right font-semibold text-emerald-600"><?= Format::currency($r['total_income']) ?></td>
                                <td class="px-5 py-4 text-right font-semibold text-rose-600"><?= Format::currency($r['total_expenses']) ?></td>
                                <td class="px-5 py-4 text-right text-slate-700"><?= Format::currency($r['loans_taken']) ?></td>
                                <td class="px-5 py-4 text-right text-slate-700"><?= Format::currency($r['loans_given']) ?></td>
                                <td class="px-5 py-4 text-right font-bold text-amber-600"><?= Format::currency($r['outstanding_liability']) ?></td>
                                <td class="px-5 py-4 text-right font-bold text-sky-600"><?= Format::currency($r['outstanding_receivable']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-slate-900 text-white font-extrabold text-xs uppercase tracking-wider">
                        <tr>
                            <td class="px-5 py-4 font-extrabold">TOTAL ENTIRE BUSINESS GROUP</td>
                            <td class="px-5 py-4 text-right text-sky-300 font-extrabold text-sm"><?= Format::currency($groupTotals['available_money']) ?></td>
                            <td class="px-5 py-4 text-right text-emerald-400 font-extrabold"><?= Format::currency($groupTotals['total_income']) ?></td>
                            <td class="px-5 py-4 text-right text-rose-400 font-extrabold"><?= Format::currency($groupTotals['total_expenses']) ?></td>
                            <td class="px-5 py-4 text-right text-slate-200"><?= Format::currency($groupTotals['loans_taken']) ?></td>
                            <td class="px-5 py-4 text-right text-slate-200"><?= Format::currency($groupTotals['loans_given']) ?></td>
                            <td class="px-5 py-4 text-right text-amber-400"><?= Format::currency($groupTotals['outstanding_liability']) ?></td>
                            <td class="px-5 py-4 text-right text-sky-400"><?= Format::currency($groupTotals['outstanding_receivable']) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
