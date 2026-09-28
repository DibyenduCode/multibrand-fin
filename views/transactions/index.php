<?php
$pageTitle = 'Transaction History & Ledger';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Transaction History &amp; Audit Trail</h2>
                <p class="text-xs text-slate-500">View detailed financial logs for income, expenses, loans, and repayments.</p>
            </div>

            <div class="flex items-center gap-2">
                <?php
                $exportQueryParams = $_GET;
                $exportQueryParams['export'] = 'csv';
                $csvExportUrl = BASE_URL . '/transactions?' . http_build_query($exportQueryParams);
                ?>
                <a href="<?= e($csvExportUrl) ?>" class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                    <i class="fa-solid fa-file-csv text-sm"></i>
                    <span>Export CSV</span>
                </a>
                <?php if (!Auth::isManager()): ?>
                    <a href="<?= BASE_URL ?>/money-in/create" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                        <i class="fa-solid fa-plus"></i>
                        <span>Money In</span>
                    </a>
                    <a href="<?= BASE_URL ?>/expenses/create" class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                        <i class="fa-solid fa-minus"></i>
                        <span>Expense</span>
                    </a>
                <?php endif; ?>
            </div>

        </div>

        <!-- FILTER BAR CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 space-y-4">
            <form action="<?= BASE_URL ?>/transactions" method="GET" id="transactionFilterForm" class="space-y-4">
                <!-- Row 1: Brand, Medium, Type, Category, Specific Account -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Brand</label>
                        <select name="brand_id" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <?php if (count($brands) > 1 || Auth::isSuperAdmin() || Auth::isManager()): ?>
                                <option value="">All Brands</option>
                            <?php endif; ?>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= (string)($selectedBrandId ?? '') === (string)$b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['brand_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                            <i class="fa-solid fa-wallet text-slate-400 mr-1"></i> Medium
                        </label>
                        <select name="account_type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
                            <option value="">All Mediums</option>
                            <option value="bank" <?= ($filters['account_type'] ?? '') === 'bank' ? 'selected' : '' ?>>🏦 Bank Accounts Only</option>
                            <option value="cash" <?= ($filters['account_type'] ?? '') === 'cash' ? 'selected' : '' ?>>💵 Hand Cash Only</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                            <i class="fa-solid fa-tag text-slate-400 mr-1"></i> Type
                        </label>
                        <select name="type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
                            <option value="">All Types</option>
                            <option value="income" <?= ($filters['type'] ?? '') === 'income' ? 'selected' : '' ?>>Income (Money In)</option>
                            <option value="expense" <?= ($filters['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Expense (Money Out)</option>
                            <option value="transfer_in" <?= ($filters['type'] ?? '') === 'transfer_in' ? 'selected' : '' ?>>Transfer In (Bank/Cash)</option>
                            <option value="transfer_out" <?= ($filters['type'] ?? '') === 'transfer_out' ? 'selected' : '' ?>>Transfer Out (Bank/Cash)</option>
                            <option value="loan_given" <?= ($filters['type'] ?? '') === 'loan_given' ? 'selected' : '' ?>>Loan Given</option>
                            <option value="loan_received" <?= ($filters['type'] ?? '') === 'loan_received' ? 'selected' : '' ?>>Loan Received</option>
                            <option value="loan_repayment" <?= ($filters['type'] ?? '') === 'loan_repayment' ? 'selected' : '' ?>>Loan Repayment Paid</option>
                            <option value="loan_repayment_received" <?= ($filters['type'] ?? '') === 'loan_repayment_received' ? 'selected' : '' ?>>Loan Repayment Received</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                            <i class="fa-solid fa-layer-group text-slate-400 mr-1"></i> Category
                        </label>
                        <select name="category" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= ($filters['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                    <?= e($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                            <i class="fa-solid fa-building-columns text-slate-400 mr-1"></i> Account
                        </label>
                        <select name="bank_account_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
                            <option value="">All Accounts</option>
                            <?php foreach ($filterBankAccounts as $ba): 
                                $isCash = ($ba['account_type'] ?? 'bank') === 'cash';
                                $accLabel = $isCash ? '💵 Hand Cash' : '🏦 ' . e($ba['bank_name']) . ' (' . Security::maskAccountNumber($ba['account_number']) . ')';
                            ?>
                                <option value="<?= $ba['id'] ?>" <?= (string)($filters['bank_account_id'] ?? '') === (string)$ba['id'] ? 'selected' : '' ?>>
                                    <?= $accLabel ?> <?= (!empty($ba['brand_name']) && empty($selectedBrandId)) ? '- ' . e($ba['brand_name']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Row 2: Purpose / Details, Amount Range, Date to Date -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                    <!-- Purpose / Details Search -->
                    <div class="lg:col-span-4">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 mr-1"></i> Purpose / Details
                        </label>
                        <div class="relative">
                            <input type="text" name="search" value="<?= e($filters['search'] ?? '') ?>" placeholder="Search purpose, note, details..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Amount Range -->
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                            <i class="fa-solid fa-indian-rupee-sign text-slate-400 mr-1"></i> Amount Range
                        </label>
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <span class="absolute left-2.5 top-2 text-slate-400 text-xs font-semibold">₹</span>
                                <input type="number" step="any" name="min_amount" value="<?= e($filters['min_amount'] !== '' ? $filters['min_amount'] : '') ?>" placeholder="Min" class="w-full pl-6 pr-2 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
                            </div>
                            <span class="text-slate-400 text-xs font-medium">-</span>
                            <div class="relative flex-1">
                                <span class="absolute left-2.5 top-2 text-slate-400 text-xs font-semibold">₹</span>
                                <input type="number" step="any" name="max_amount" value="<?= e($filters['max_amount'] !== '' ? $filters['max_amount'] : '') ?>" placeholder="Max" class="w-full pl-6 pr-2 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Date Period Preset & Date to Date -->
                    <div class="lg:col-span-5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                            <i class="fa-solid fa-calendar-days text-slate-400 mr-1"></i> Date to Date
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <select name="date_range" id="filterDateRangeSelect" class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
                                <option value="">Period / Preset</option>
                                <option value="today" <?= ($filters['date_range'] ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                                <option value="7_days" <?= ($filters['date_range'] ?? '') === '7_days' ? 'selected' : '' ?>>Last 7 Days</option>
                                <option value="this_month" <?= ($filters['date_range'] ?? '') === 'this_month' ? 'selected' : '' ?>>This Month</option>
                                <option value="custom" <?= ($filters['date_range'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                            </select>
                            <input type="date" name="start_date" value="<?= e($filters['start_date'] ?? '') ?>" title="From Date (Date to Date)" class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
                            <input type="date" name="end_date" value="<?= e($filters['end_date'] ?? '') ?>" title="To Date (Date to Date)" class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-sky-500 focus:outline-none transition-all">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons & Active Filter Tags -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-3 border-t border-slate-100">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <?php
                        $activeCount = 0;
                        if (!empty($filters['type'])) $activeCount++;
                        if (!empty($filters['category'])) $activeCount++;
                        if (!empty($filters['bank_account_id'])) $activeCount++;
                        if (!empty($filters['search'])) $activeCount++;
                        if ($filters['min_amount'] !== '' || $filters['max_amount'] !== '') $activeCount++;
                        if (!empty($filters['start_date']) || !empty($filters['end_date']) || !empty($filters['date_range'])) $activeCount++;
                        ?>
                        <?php if ($activeCount > 0): ?>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-700 font-bold border border-sky-200/80">
                                <i class="fa-solid fa-filter text-[10px]"></i> <?= $activeCount ?> Filter<?= $activeCount > 1 ? 's' : '' ?> Active
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="<?= BASE_URL ?>/transactions<?= (!empty($selectedBrandId) ? '?brand_id=' . urlencode($selectedBrandId) : '') ?>" class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                            <span>Clear Filters</span>
                        </a>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            <span>Apply Filters</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- TRANSACTIONS TABLE -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm min-w-[900px]">
                    <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5 w-32">Date</th>
                            <th class="px-6 py-3.5 w-36">Brand</th>
                            <th class="px-6 py-3.5 w-48">Type &amp; Category</th>
                            <th class="px-6 py-3.5">Purpose / Details</th>
                            <th class="px-6 py-3.5 w-56">Bank Account</th>
                            <th class="px-6 py-3.5 text-right w-44 whitespace-nowrap min-w-[140px]">Amount</th>
                            <th class="px-6 py-3.5 text-center w-28">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-receipt text-3xl mb-2 block"></i>
                                    No transaction records found matching criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $t): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-6 py-4 font-medium text-slate-800 whitespace-nowrap">
                                        <?= Format::date($t['transaction_date']) ?>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">
                                        <?= e($t['brand_name']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1 items-start">
                                            <?php if (in_array($t['type'], ['income', 'loan_received', 'loan_repayment_received', 'transfer_in'])): ?>
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $t['type'] === 'transfer_in' ? 'bg-indigo-100 text-indigo-800' : 'bg-emerald-100 text-emerald-800' ?> uppercase">
                                                    <?= str_replace('_', ' ', e($t['type'])) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold <?= $t['type'] === 'transfer_out' ? 'bg-indigo-100 text-indigo-800' : 'bg-rose-100 text-rose-800' ?> uppercase">
                                                    <?= str_replace('_', ' ', e($t['type'])) ?>
                                                </span>
                                            <?php endif; ?>

                                            <?php if ($t['category']): ?>
                                                <span class="text-[11px] text-slate-400 font-medium pl-1"><?= e($t['category']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-slate-900 block text-sm"><?= e($t['purpose']) ?></span>
                                        <?php if ($t['related_brand_name']): ?>
                                            <span class="text-xs text-sky-600 font-medium block mt-0.5">Related Sister Brand: <?= e($t['related_brand_name']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($t['note']): ?>
                                            <span class="text-xs text-slate-400 italic block mt-0.5"><?= e($t['note']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-semibold whitespace-nowrap">
                                        <?php if (($t['account_type'] ?? 'bank') === 'cash'): ?>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-bold border border-emerald-200">
                                                <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                                                <span>Hand Cash</span>
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1.5 text-slate-700">
                                                <i class="fa-solid fa-building-columns text-slate-400"></i>
                                                <span><?= e($t['bank_name']) ?> <span class="text-slate-400 font-mono text-[11px]">(<?= Security::maskAccountNumber($t['account_number']) ?>)</span></span>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap font-extrabold text-base min-w-[140px] <?= in_array($t['type'], ['income', 'loan_received', 'loan_repayment_received', 'transfer_in']) ? 'text-emerald-600' : ($t['type'] === 'transfer_out' ? 'text-indigo-600' : 'text-rose-600') ?>">
                                        <?= in_array($t['type'], ['income', 'loan_received', 'loan_repayment_received', 'transfer_in']) ? '+' : '-' ?> <?= Format::currency($t['amount']) ?>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <?php if (Auth::canModifyBrandData((int)$t['brand_id'])): ?>
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="<?= BASE_URL ?>/transactions/<?= $t['id'] ?>/edit" title="Edit Transaction" class="p-2 rounded-lg text-slate-400 hover:text-sky-600 hover:bg-sky-50 transition-colors">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <form action="<?= BASE_URL ?>/transactions/<?= $t['id'] ?>/delete" method="POST" class="inline" onsubmit="return confirmDeleteForm(event, this, 'Delete Transaction?', 'Are you sure you want to delete this transaction record? This action cannot be undone.');">
                                                    <?= Security::csrfField() ?>
                                                    <button type="submit" title="Delete Transaction" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>

                                            </div>
                                        <?php else: ?>
                                            <span class="text-xs text-slate-300" title="Read Only Access"><i class="fa-solid fa-lock text-slate-300"></i></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION LINKS -->
            <?= $paginationHtml ?>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateRangeSelect = document.getElementById('filterDateRangeSelect');
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');

    if (dateRangeSelect && startDateInput && endDateInput) {
        dateRangeSelect.addEventListener('change', function() {
            const val = this.value;
            const now = new Date();
            const formatDate = (d) => {
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };

            if (val === 'today') {
                const todayStr = formatDate(now);
                startDateInput.value = todayStr;
                endDateInput.value = todayStr;
            } else if (val === '7_days') {
                const past = new Date();
                past.setDate(past.getDate() - 7);
                startDateInput.value = formatDate(past);
                endDateInput.value = formatDate(now);
            } else if (val === 'this_month') {
                const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
                const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                startDateInput.value = formatDate(firstDay);
                endDateInput.value = formatDate(lastDay);
            }
        });

        [startDateInput, endDateInput].forEach(el => {
            el.addEventListener('change', function() {
                if (startDateInput.value || endDateInput.value) {
                    dateRangeSelect.value = 'custom';
                }
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
