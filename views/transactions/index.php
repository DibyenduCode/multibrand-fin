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

            <?php if (!Auth::isManager()): ?>
                <div class="flex items-center gap-2">
                    <a href="<?= BASE_URL ?>/money-in/create" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                        <i class="fa-solid fa-plus"></i>
                        <span>Money In</span>
                    </a>
                    <a href="<?= BASE_URL ?>/expenses/create" class="inline-flex items-center gap-2 px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                        <i class="fa-solid fa-minus"></i>
                        <span>Expense</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- FILTER BAR CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
            <form action="<?= BASE_URL ?>/transactions" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
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
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Type</label>
                    <select name="type" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <option value="">All Types</option>
                        <option value="income" <?= ($filters['type'] ?? '') === 'income' ? 'selected' : '' ?>>Income (Money In)</option>
                        <option value="expense" <?= ($filters['type'] ?? '') === 'expense' ? 'selected' : '' ?>>Expense (Money Out)</option>
                        <option value="loan_given" <?= ($filters['type'] ?? '') === 'loan_given' ? 'selected' : '' ?>>Loan Given</option>
                        <option value="loan_received" <?= ($filters['type'] ?? '') === 'loan_received' ? 'selected' : '' ?>>Loan Received</option>
                        <option value="loan_repayment" <?= ($filters['type'] ?? '') === 'loan_repayment' ? 'selected' : '' ?>>Loan Repayment Paid</option>
                        <option value="loan_repayment_received" <?= ($filters['type'] ?? '') === 'loan_repayment_received' ? 'selected' : '' ?>>Loan Repayment Received</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Date Period</label>
                    <select name="date_range" onchange="this.form.submit()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <option value="">All Time</option>
                        <option value="today" <?= ($filters['date_range'] ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                        <option value="7_days" <?= ($filters['date_range'] ?? '') === '7_days' ? 'selected' : '' ?>>Last 7 Days</option>
                        <option value="this_month" <?= ($filters['date_range'] ?? '') === 'this_month' ? 'selected' : '' ?>>This Month</option>
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-2 flex items-end gap-2">
                    <a href="<?= BASE_URL ?>/transactions" class="w-full py-2 px-4 rounded-xl border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs text-center transition-colors">
                        Clear Filters
                    </a>
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
                                            <?php if (in_array($t['type'], ['income', 'loan_received', 'loan_repayment_received'])): ?>
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 uppercase">
                                                    <?= str_replace('_', ' ', e($t['type'])) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 uppercase">
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
                                    <td class="px-6 py-4 text-xs font-semibold text-slate-700 whitespace-nowrap">
                                        <?= e($t['bank_name']) ?> (<?= Security::maskAccountNumber($t['account_number']) ?>)
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap font-extrabold text-base min-w-[140px] <?= in_array($t['type'], ['income', 'loan_received', 'loan_repayment_received']) ? 'text-emerald-600' : 'text-rose-600' ?>">
                                        <?= in_array($t['type'], ['income', 'loan_received', 'loan_repayment_received']) ? '+' : '-' ?> <?= Format::currency($t['amount']) ?>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
