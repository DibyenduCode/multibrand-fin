<?php
$pageTitle = 'Add Bank Account';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-3xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-6">
                <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-building-columns"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Add New Bank Account</h2>
                    <p class="text-xs text-slate-500">Add a bank account for storing liquid balances and transactions.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/bank-accounts/store" method="POST" class="space-y-5">
                <?= Security::csrfField() ?>

                <?php if (Auth::isSuperAdmin()): ?>
                    <div>
                        <label for="brand_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Select Brand *</label>
                        <select id="brand_id" name="brand_id" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['brand_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="brand_id" value="<?= Auth::userBrandId() ?>">
                <?php endif; ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="bank_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Bank Name *</label>
                        <input type="text" id="bank_name" name="bank_name" required placeholder="e.g. HDFC Bank, ICICI Bank, SBI"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="account_holder_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Account Holder Name *</label>
                        <input type="text" id="account_holder_name" name="account_holder_name" required placeholder="e.g. Brand Name Private Limited"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="account_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Account Number *</label>
                        <input type="text" id="account_number" name="account_number" required placeholder="e.g. 50100987654321"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-mono text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="ifsc_code" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">IFSC Code (Optional)</label>
                        <input type="text" id="ifsc_code" name="ifsc_code" placeholder="e.g. HDFC0001234"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-mono text-sm uppercase focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="opening_balance" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Opening Balance (₹)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-bold text-sm">₹</div>
                        <input type="number" step="0.01" min="0" id="opening_balance" name="opening_balance" value="0.00"
                               class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold text-base focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <a href="<?= BASE_URL ?>/bank-accounts" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-md transition-all">
                        SAVE BANK ACCOUNT
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
