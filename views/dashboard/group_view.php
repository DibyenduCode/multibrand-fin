<?php
$pageTitle = Auth::isManager() ? 'Manager Group Dashboard' : 'Super Admin Group Dashboard';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <!-- GROUP FINANCIAL HIGHLIGHT CARD -->
        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-slate-800">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-700/60 pb-6 mb-6">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-sky-400 block mb-1">Group Combined Total</span>
                    <h2 class="text-sm font-medium text-slate-300">TOTAL AVAILABLE MONEY</h2>
                    <div class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mt-1">
                        <?= Format::currency($groupStats['total_available']) ?>
                    </div>
                </div>
                
                <?php if (Auth::isSuperAdmin()): ?>
                    <div class="flex items-center gap-3">
                        <a href="<?= BASE_URL ?>/brands/create" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold transition-all shadow-md hover:shadow-sky-500/20">
                            <i class="fa-solid fa-plus"></i>
                            <span>Create Brand</span>
                        </a>
                        <a href="<?= BASE_URL ?>/users/create" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-sm font-semibold transition-all shadow-md hover:shadow-purple-500/20">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Add User</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Group Monthly Summary Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-800/60 backdrop-blur-xs rounded-xl p-4 border border-slate-700/50">
                    <div class="text-xs font-medium text-slate-400 mb-1">TOTAL MONEY IN (THIS MONTH)</div>
                    <div class="text-lg sm:text-xl font-bold text-emerald-400">
                        <?= Format::currency($groupStats['total_month_in']) ?>
                    </div>
                </div>
                <div class="bg-slate-800/60 backdrop-blur-xs rounded-xl p-4 border border-slate-700/50">
                    <div class="text-xs font-medium text-slate-400 mb-1">TOTAL MONEY OUT (THIS MONTH)</div>
                    <div class="text-lg sm:text-xl font-bold text-rose-400">
                        <?= Format::currency($groupStats['total_month_out']) ?>
                    </div>
                </div>
                <div class="bg-slate-800/60 backdrop-blur-xs rounded-xl p-4 border border-slate-700/50">
                    <div class="text-xs font-medium text-slate-400 mb-1">INTERNAL RECEIVABLES</div>
                    <div class="text-lg sm:text-xl font-bold text-sky-400">
                        <?= Format::currency($groupStats['total_receivable']) ?>
                    </div>
                </div>
                <div class="bg-slate-800/60 backdrop-blur-xs rounded-xl p-4 border border-slate-700/50">
                    <div class="text-xs font-medium text-slate-400 mb-1">INTERNAL LIABILITIES</div>
                    <div class="text-lg sm:text-xl font-bold text-amber-400">
                        <?= Format::currency($groupStats['total_liability']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- GROUP BRAND FINANCIAL OVERVIEW TABLE -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 sm:px-6 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Brand Financial Comparison Matrix</h3>
                    <p class="text-xs text-slate-500">Live breakdown of cash, receivables, and liabilities across all group brands.</p>
                </div>
                <a href="<?= BASE_URL ?>/reports/brand" class="text-xs font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1">
                    <span>Full Brand Report</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider border-b border-slate-200/80">
                        <tr>
                            <th class="px-6 py-3.5">Brand</th>
                            <th class="px-6 py-3.5 text-right">Available Money</th>
                            <th class="px-6 py-3.5 text-right">This Month In</th>
                            <th class="px-6 py-3.5 text-right">This Month Out</th>
                            <th class="px-6 py-3.5 text-right">We Will Receive</th>
                            <th class="px-6 py-3.5 text-right">We Need To Pay</th>
                            <th class="px-6 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($brandOverview)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-building text-3xl mb-2 block"></i>
                                    No brands registered yet. <a href="<?= BASE_URL ?>/brands/create" class="text-sky-600 font-bold hover:underline">Create your first Brand &rarr;</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($brandOverview as $bo): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 py-4 font-semibold text-slate-900">
                                        <div class="flex items-center gap-3">
                                            <?= Format::brandLogo($bo, 'w-9 h-9', 'text-xs') ?>
                                            <div>
                                                <span class="block text-sm font-bold text-slate-900"><?= e($bo['brand_name']) ?></span>
                                                <span class="block text-xs text-slate-400 font-normal"><?= e($bo['company_name']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-slate-900">
                                        <?= Format::currency($bo['available_money']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-emerald-600">
                                        <?= Format::currency($bo['month_money_in']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold text-rose-600">
                                        <?= Format::currency($bo['month_money_out']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-sky-600">
                                        <?= Format::currency($bo['receivable']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-amber-600">
                                        <?= Format::currency($bo['liability']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="<?= BASE_URL ?>/dashboard?brand_id=<?= $bo['id'] ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                                            <span>View Dashboard</span>
                                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RECENT RELEVANT ACTIVITY STREAM -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <h3 class="text-base font-bold text-slate-900 mb-4">Recent Group Financial Activity</h3>
            
            <?php if (empty($recentActivity)): ?>
                <div class="text-center py-8 text-slate-400 text-sm">
                    <i class="fa-solid fa-receipt text-3xl mb-2 block"></i>
                    No transactions recorded yet.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recentActivity as $act): ?>
                        <div class="flex items-center justify-between p-3.5 rounded-xl border border-slate-100 hover:border-slate-200 bg-slate-50/50 transition-all">
                            <div class="flex items-center gap-3">
                                <?php if (in_array($act['type'], ['income', 'loan_received', 'loan_repayment_received'])): ?>
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm font-bold flex-shrink-0">
                                        <i class="fa-solid fa-arrow-down-left"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-sm font-bold flex-shrink-0">
                                        <i class="fa-solid fa-arrow-up-right"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-slate-900"><?= e($act['brand_name']) ?></span>
                                        <span class="text-[11px] px-2 py-0.5 rounded-md uppercase font-semibold bg-slate-200/70 text-slate-600">
                                            <?= str_replace('_', ' ', e($act['type'])) ?>
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5"><?= e($act['purpose']) ?> (Bank: <?= e($act['bank_name']) ?>)</p>
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-sm font-bold <?= in_array($act['type'], ['income', 'loan_received', 'loan_repayment_received']) ? 'text-emerald-600' : 'text-rose-600' ?>">
                                    <?= in_array($act['type'], ['income', 'loan_received', 'loan_repayment_received']) ? '+' : '-' ?> <?= Format::currency($act['amount']) ?>
                                </div>
                                <span class="text-[11px] text-slate-400 block"><?= Format::date($act['transaction_date']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
