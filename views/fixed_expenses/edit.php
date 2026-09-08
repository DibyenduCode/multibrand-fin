<?php
$pageTitle = 'Edit Fixed Monthly Expense - ' . e($fixedExp['title']);
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-3xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-6">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Edit Fixed Monthly Expense</h2>
                    <p class="text-xs text-slate-500">Update amount, payment bank account, due day, or notes.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/fixed-expenses/<?= $fixedExp['id'] ?>" method="POST" class="space-y-5">
                <?= Security::csrfField() ?>

                <div>
                    <label for="brand_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Target Brand *</label>
                    <select id="brand_id" name="brand_id" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= (int)$b['id'] === (int)$fixedExp['brand_id'] ? 'selected' : '' ?>>
                                <?= e($b['brand_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Expense Title *</label>
                        <input type="text" id="title" name="title" value="<?= e($fixedExp['title']) ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Monthly Amount (₹) *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-bold text-sm">₹</div>
                            <input type="number" step="0.01" min="0.01" id="amount" name="amount" value="<?= (float)$fixedExp['amount'] ?>" required
                                   class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold text-base focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        </div>
                    </div>
                </div>

<?php
$startMonthValue = !empty($fixedExp['start_month']) ? $fixedExp['start_month'] : (!empty($fixedExp['effective_start_month']) ? $fixedExp['effective_start_month'] : date('Y-m'));
?>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Expense Category *</label>
                        <select id="category" name="category" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>" <?= $fixedExp['category'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="due_day" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Due Day of Month *</label>
                        <input type="number" min="1" max="31" id="due_day" name="due_day" value="<?= (int)$fixedExp['due_day'] ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="start_month" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">First Due Month *</label>
                        <input type="month" id="start_month" name="start_month" value="<?= e($startMonthValue) ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="bank_account_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Preferred Bank Account *</label>
                    <select id="bank_account_id" name="bank_account_id" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <?php foreach ($bankAccounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>" <?= (int)$acc['id'] === (int)$fixedExp['bank_account_id'] ? 'selected' : '' ?>>
                                <?= e($acc['bank_name']) ?> (Acc: <?= Security::maskAccountNumber($acc['account_number']) ?>) - Avail: <?= Format::currency($acc['current_balance']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="note" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Optional Note</label>
                    <textarea id="note" name="note" rows="2"
                              class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none"><?= e($fixedExp['note'] ?? '') ?></textarea>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        <option value="active" <?= $fixedExp['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $fixedExp['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <a href="<?= BASE_URL ?>/fixed-expenses" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm shadow-md transition-all">
                        UPDATE FIXED EXPENSE
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
