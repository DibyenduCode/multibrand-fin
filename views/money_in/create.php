<?php
$pageTitle = 'Add Money In';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-3xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-6">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-circle-arrow-down"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Record Incoming Money (Money In)</h2>
                    <p class="text-xs text-slate-500">Add client payment, sales revenue, or other incoming funds.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/money-in/store" method="POST" class="space-y-5">
                <?= Security::csrfField() ?>

                <div>
                    <label for="brand_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Target Brand *</label>
                    <select id="brand_id" name="brand_id" onchange="window.location.href='<?= BASE_URL ?>/money-in/create?brand_id='+this.value" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= (int)$b['id'] === (int)$selectedBrandId ? 'selected' : '' ?>>
                                <?= e($b['brand_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Amount (₹) *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-bold text-sm">₹</div>
                            <input type="number" step="0.01" min="0.01" id="amount" name="amount" required placeholder="50000"
                                   class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold text-base focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="transaction_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Date *</label>
                        <input type="date" id="transaction_date" name="transaction_date" value="<?= date('Y-m-d') ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="purpose" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Source or Purpose *</label>
                    <input type="text" id="purpose" name="purpose" required placeholder="e.g. Client Payment, Product Sales, Subscription Revenue"
                           class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label for="bank_account_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Target Bank Account *</label>
                    <select id="bank_account_id" name="bank_account_id" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">-- Select Bank Account --</option>
                        <?php foreach ($bankAccounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>">
                                <?= e($acc['bank_name']) ?> (Acc: <?= Security::maskAccountNumber($acc['account_number']) ?>) - Avail: <?= Format::currency($acc['current_balance']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="note" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Optional Note</label>
                    <textarea id="note" name="note" rows="2" placeholder="Additional details or reference number..."
                              class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <a href="<?= BASE_URL ?>/dashboard" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-md transition-all">
                        ADD MONEY IN
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
