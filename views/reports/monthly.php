<?php
$pageTitle = 'Monthly Financial Report';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-5xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <!-- REPORT TYPE NAVIGATION TABS -->
        <div class="flex border-b border-slate-200 gap-4 overflow-x-auto text-sm font-semibold">
            <a href="<?= BASE_URL ?>/reports/daily" class="py-2.5 px-4 text-slate-500 hover:text-slate-700">Daily Report</a>
            <a href="<?= BASE_URL ?>/reports/monthly" class="py-2.5 px-4 text-sky-600 border-b-2 border-sky-600">Monthly Report</a>
            <a href="<?= BASE_URL ?>/reports/brand" class="py-2.5 px-4 text-slate-500 hover:text-slate-700">Brand Financial Summary</a>
            <a href="<?= BASE_URL ?>/reports/loans" class="py-2.5 px-4 text-slate-500 hover:text-slate-700">Inter-Brand Loans Report</a>
        </div>

        <!-- FILTER BAR -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <h2 class="text-lg font-bold text-slate-900">Monthly Cash Movement Summary</h2>

            <form action="<?= BASE_URL ?>/reports/monthly" method="GET" class="flex flex-wrap items-center gap-2">
                <select name="brand_id" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700">
                    <option value="">Entire Business Group</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= (string)$selectedBrandId === (string)$b['id'] ? 'selected' : '' ?>>
                            <?= e($b['brand_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="month" name="year_month" value="<?= e($monthlyReport['year_month']) ?>" class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700">

                <button type="submit" class="px-4 py-1.5 bg-sky-600 hover:bg-sky-500 text-white rounded-lg text-xs font-bold transition-colors">
                    Generate Report
                </button>
            </form>
        </div>

        <?php if (!empty($groupStats)): ?>
        <!-- ENTIRE BUSINESS GROUP TOTAL FUND BANNER -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 p-6 sm:p-8 rounded-2xl text-white shadow-lg border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs text-sky-400 font-bold uppercase tracking-wider block mb-1">ENTIRE BUSINESS GROUP TOTAL FUND</span>
                <div class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight"><?= Format::currency($groupStats['total_available']) ?></div>
                <p class="text-xs text-slate-300 mt-1">Combined liquid cash reserve across all group brands</p>
            </div>
            <div class="text-xs text-slate-300 bg-white/10 p-3.5 rounded-xl border border-white/10 space-y-1.5 sm:text-right">
                <div>Total Group Receivables: <span class="font-bold text-sky-400"><?= Format::currency($groupStats['total_receivable']) ?></span></div>
                <div>Total Group Liabilities: <span class="font-bold text-amber-400"><?= Format::currency($groupStats['total_liability']) ?></span></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- MONTHLY REPORT RESULTS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="text-center pb-6 border-b border-slate-100">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">REPORT PERIOD</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1"><?= e($monthlyReport['month']) ?></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="p-6 rounded-2xl bg-emerald-50/70 border border-emerald-100 text-center space-y-2">
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">TOTAL MONEY IN</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600">
                        <?= Format::currency($monthlyReport['money_in']) ?>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-rose-50/70 border border-rose-100 text-center space-y-2">
                    <span class="text-xs font-bold text-rose-800 uppercase tracking-wider block">TOTAL MONEY OUT</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-rose-600">
                        <?= Format::currency($monthlyReport['money_out']) ?>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-sky-50/70 border border-sky-100 text-center space-y-2">
                    <span class="text-xs font-bold text-sky-800 uppercase tracking-wider block">NET DIFFERENCE</span>
                    <div class="text-2xl sm:text-3xl font-extrabold <?= $monthlyReport['difference'] >= 0 ? 'text-sky-600' : 'text-rose-600' ?>">
                        <?= $monthlyReport['difference'] >= 0 ? '+' : '' ?><?= Format::currency($monthlyReport['difference']) ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
