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

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <?php if (Auth::isBrandUser()): ?>
                    <a href="<?= BASE_URL ?>/bank-transfers/create" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-95 min-h-[42px]">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>
                        <span>Transfer Funds</span>
                    </a>
                <?php endif; ?>
                <?php if (!Auth::isManager()): ?>
                    <a href="<?= BASE_URL ?>/bank-accounts/create" class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-95 min-h-[42px]">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add Account</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($accounts as $acc): 
                $isCash = ($acc['account_type'] ?? 'bank') === 'cash';
            ?>
                <div class="bg-white rounded-2xl border <?= $isCash ? 'border-emerald-300 ring-1 ring-emerald-100 shadow-sm' : 'border-slate-200/80 shadow-xs' ?> p-5 sm:p-6 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden">
                    <?php if ($isCash): ?>
                        <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-full -mr-8 -mt-8 pointer-events-none z-0"></div>
                    <?php endif; ?>

                    <div class="relative z-10">
                        <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-2 pb-3 border-b border-slate-100 mb-3">
                            <span class="text-xs font-bold text-sky-600 uppercase tracking-wider min-w-0" title="<?= e($acc['brand_name'] ?? '') ?>"><?= e($acc['brand_name'] ?? '') ?></span>
                            <div class="flex items-center gap-1.5 shrink-0 ml-auto">
                                <?php if ($isCash): ?>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold uppercase tracking-wider inline-flex items-center gap-1 whitespace-nowrap">
                                        <i class="fa-solid fa-money-bill-wave text-[10px]"></i> Hand Cash
                                    </span>
                                <?php else: ?>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 font-semibold uppercase tracking-wider inline-flex items-center gap-1 whitespace-nowrap">
                                        <i class="fa-solid fa-building-columns text-[10px]"></i> Bank
                                    </span>
                                <?php endif; ?>

                                <span class="text-[10px] px-2 py-0.5 rounded-full <?= ($acc['status'] ?? 'active') === 'active' ? 'bg-slate-100 text-slate-700' : 'bg-rose-100 text-rose-800' ?> font-semibold uppercase inline-flex items-center whitespace-nowrap">
                                    <?= e(ucfirst($acc['status'] ?? 'active')) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl <?= $isCash ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-600' ?> flex items-center justify-center text-lg flex-shrink-0">
                                <i class="fa-solid <?= $isCash ? 'fa-wallet' : 'fa-building-columns' ?>"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 leading-tight"><?= e($acc['bank_name']) ?></h3>
                                <p class="text-xs text-slate-500 mt-0.5">Holder: <?= e($acc['account_holder_name']) ?></p>
                            </div>
                        </div>
                        
                        <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 space-y-1 text-xs">
                            <?php if (!$isCash): ?>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Account Number:</span>
                                    <span class="font-mono font-semibold text-slate-700"><?= Security::maskAccountNumber($acc['account_number']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">IFSC Code:</span>
                                    <span class="font-mono text-slate-600"><?= e($acc['ifsc_code'] ?: 'N/A') ?></span>
                                </div>
                            <?php else: ?>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Account Type:</span>
                                    <span class="font-semibold text-emerald-700">Physical Counter / Drawer Cash</span>
                                </div>
                            <?php endif; ?>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Opening Balance:</span>
                                <span class="font-semibold text-slate-700"><?= Format::currency($acc['opening_balance']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between gap-2 relative z-10">
                        <div class="min-w-0">
                            <span class="text-[10px] font-semibold text-slate-400 block uppercase tracking-wider">Available Balance</span>
                            <span class="text-lg font-extrabold <?= $isCash ? 'text-emerald-700' : 'text-slate-900' ?>"><?= Format::currency($acc['current_balance']) ?></span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <?php if ((Auth::isSuperAdmin() || Auth::isBrandUser()) && Auth::canModifyBrandData((int)$acc['brand_id'])): ?>
                                <a href="<?= BASE_URL ?>/bank-transfers/create?brand_id=<?= $acc['brand_id'] ?>&from_id=<?= $acc['id'] ?>" title="Transfer or Deposit from this Account" class="px-2.5 py-1.5 rounded-lg <?= $isCash ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700' : 'bg-indigo-50 hover:bg-indigo-100 text-indigo-600' ?> text-xs font-bold transition-colors">
                                    <i class="fa-solid fa-arrow-right-arrow-left"></i> Transfer
                                </a>
                            <?php endif; ?>
                            <?php if (Auth::canModifyBrandData((int)$acc['brand_id'])): ?>
                                <a href="<?= BASE_URL ?>/bank-accounts/<?= $acc['id'] ?>/edit" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                            <?php endif; ?>

                            <?php if (Auth::isSuperAdmin() && !$isCash): ?>
                                <form action="<?= BASE_URL ?>/bank-accounts/<?= $acc['id'] ?>/delete" method="POST" onsubmit="return confirmDeleteForm(event, this, 'Delete Bank Account?', 'Are you sure you want to delete bank account <?= e($acc['bank_name']) ?>?')">
                                    <?= Security::csrfField() ?>
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-bold transition-colors" title="Delete Account">
                                        <i class="fa-solid fa-trash"></i>
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
