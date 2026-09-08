<?php
$pageTitle = 'Bank Accounts';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Bank Accounts &amp; Treasury</h2>
                <p class="text-xs text-slate-500">Manage bank accounts, opening balances, and real-time available liquid funds.</p>
            </div>

            <?php if (!Auth::isManager()): ?>
                <a href="<?= BASE_URL ?>/bank-accounts/create" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-95 min-h-[42px]">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Bank Account</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($accounts as $acc): ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between hover:shadow-md transition-shadow">
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                            <span class="text-xs font-bold text-sky-600 uppercase tracking-wider"><?= e($acc['brand_name'] ?? '') ?></span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full <?= ($acc['status'] ?? 'active') === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?> font-semibold uppercase">
                                <?= e(ucfirst($acc['status'] ?? 'active')) ?>
                            </span>
                        </div>
                        
                        <h3 class="text-lg font-bold text-slate-900"><?= e($acc['bank_name']) ?></h3>
                        <p class="text-xs text-slate-500 mt-1">Holder: <?= e($acc['account_holder_name']) ?></p>
                        
                        <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Account Number:</span>
                                <span class="font-mono font-semibold text-slate-700"><?= Security::maskAccountNumber($acc['account_number']) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">IFSC Code:</span>
                                <span class="font-mono text-slate-600"><?= e($acc['ifsc_code'] ?: 'N/A') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Opening Balance:</span>
                                <span class="font-semibold text-slate-700"><?= Format::currency($acc['opening_balance']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                        <div>
                            <span class="text-[11px] font-semibold text-slate-400 block uppercase tracking-wider">Available Balance</span>
                            <span class="text-lg font-extrabold text-slate-900"><?= Format::currency($acc['current_balance']) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <?php if (Auth::canModifyBrandData((int)$acc['brand_id'])): ?>
                                <a href="<?= BASE_URL ?>/bank-accounts/<?= $acc['id'] ?>/edit" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                                </a>
                            <?php endif; ?>

                            <?php if (Auth::isSuperAdmin()): ?>
                                <form action="<?= BASE_URL ?>/bank-accounts/<?= $acc['id'] ?>/delete" method="POST" onsubmit="return confirmDeleteForm(event, this, 'Delete Bank Account?', 'Are you sure you want to delete bank account <?= e($acc['bank_name']) ?>?')">
                                    <?= Security::csrfField() ?>
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition-colors">
                                        <i class="fa-solid fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
