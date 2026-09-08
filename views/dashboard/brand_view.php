<?php
$pageTitle = e($currentBrand['brand_name']) . ' Dashboard';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$canModify = Auth::canModifyBrandData((int)$currentBrand['id']);
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <!-- BRAND HEADER BAR WITH BRAND LOGO -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-4">
                <?= Format::brandLogo($currentBrand, 'w-14 h-14', 'text-2xl') ?>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                        <span><?= e($currentBrand['brand_name']) ?></span>
                    </h2>
                    <p class="text-xs text-slate-500"><?= e($currentBrand['company_name']) ?> &bull; <?= e($currentBrand['email'] ?? '') ?></p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <?php if (Auth::isSuperAdmin()): ?>
                    <a href="<?= BASE_URL ?>/brands/<?= $currentBrand['id'] ?>/edit" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold active:scale-95 transition-all min-h-[40px]">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Edit Profile</span>
                    </a>
                <?php endif; ?>

                <?php if (Auth::isSuperAdmin() || Auth::isManager()): ?>
                    <a href="<?= BASE_URL ?>/dashboard" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold active:scale-95 transition-all min-h-[40px]">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Group View</span>
                    </a>
                <?php endif; ?>

                <?php if ($canModify): ?>
                    <a href="<?= BASE_URL ?>/money-in/create?brand_id=<?= $currentBrand['id'] ?>" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-xs active:scale-95 transition-all min-h-[40px]">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add Money In</span>
                    </a>
                    <a href="<?= BASE_URL ?>/expenses/create?brand_id=<?= $currentBrand['id'] ?>" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold shadow-xs active:scale-95 transition-all min-h-[40px]">
                        <i class="fa-solid fa-minus"></i>
                        <span>Add Expense</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- 1. MAIN FINANCIAL CARD - CURRENT AVAILABLE MONEY -->
        <div class="bg-gradient-to-r from-sky-900 via-sky-800 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-sky-800/50">
            <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-white/5 skew-x-12 pointer-events-none"></div>
            
            <span class="text-xs font-semibold uppercase tracking-wider text-sky-300 block mb-1">Brand Liquid Cash Reserve</span>
            <div class="text-sm font-medium text-slate-300">CURRENT AVAILABLE MONEY</div>
            <div class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight mt-2">
                <?= Format::currency($brandStats['available_money']) ?>
            </div>
            <p class="text-xs text-sky-200/80 mt-2 flex items-center gap-1.5">
                <i class="fa-solid fa-building-columns"></i>
                <span>Combined real-time balance across <?= count($bankAccounts) ?> active bank account(s)</span>
            </p>
        </div>

        <!-- 2. MONTHLY FINANCIAL SUMMARY CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="w-2 h-full bg-emerald-500 absolute left-0 top-0 bottom-0"></div>
                <div class="pl-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">THIS MONTH MONEY IN</span>
                    <div class="text-2xl font-extrabold text-emerald-600 mt-1">
                        <?= Format::currency($brandStats['month_money_in']) ?>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="w-2 h-full bg-rose-500 absolute left-0 top-0 bottom-0"></div>
                <div class="pl-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">THIS MONTH MONEY OUT</span>
                    <div class="text-2xl font-extrabold text-rose-600 mt-1">
                        <?= Format::currency($brandStats['month_money_out']) ?>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="w-2 h-full bg-sky-500 absolute left-0 top-0 bottom-0"></div>
                <div class="pl-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">WE WILL RECEIVE</span>
                    <div class="text-2xl font-extrabold text-sky-600 mt-1">
                        <?= Format::currency($brandStats['receivable']) ?>
                    </div>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Outstanding Inter-Brand Loan Principal</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="w-2 h-full bg-amber-500 absolute left-0 top-0 bottom-0"></div>
                <div class="pl-2">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">WE NEED TO PAY</span>
                    <div class="text-2xl font-extrabold text-amber-600 mt-1">
                        <?= Format::currency($brandStats['liability']) ?>
                    </div>
                    <span class="text-[11px] text-slate-400 block mt-0.5">Outstanding Inter-Brand Borrowings</span>
                </div>
            </div>
        </div>

        <!-- 3. FIXED MONTHLY EXPENSES WIDGET -->
        <?php if (!empty($fixedExpenses)): ?>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check text-rose-500"></i>
                            <span>Fixed Monthly Expenses &amp; Commitments</span>
                        </h3>
                        <p class="text-xs text-slate-500">Recurring obligations that must be paid every month.</p>
                    </div>
                    <a href="<?= BASE_URL ?>/fixed-expenses?brand_id=<?= $currentBrand['id'] ?>" class="text-xs font-semibold text-rose-600 hover:text-rose-700">
                        Manage Fixed Expenses &rarr;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($fixedExpenses as $fe): ?>
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-slate-900"><?= e($fe['title']) ?></span>
                                    <?php if ($fe['is_paid_this_month']): ?>
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 font-bold text-[10px]">PAID</span>
                                    <?php else: ?>
                                        <?php 
                                            $cDay = (int)date('j');
                                            $dDay = (int)$fe['due_day'];
                                            if (!Auth::isSuperAdmin() && $cDay > $dDay):
                                        ?>
                                            <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-800 font-bold text-[10px] animate-pulse">OVERDUE</span>
                                        <?php elseif (!Auth::isSuperAdmin() && $cDay === $dDay): ?>
                                            <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-bold text-[10px]">DUE TODAY</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 font-bold text-[10px]">PENDING</span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                </div>
                                <p class="text-xs text-slate-500 mt-1">Due: <?= (int)$fe['due_day'] ?><?= date('S', mktime(0,0,0,1,(int)$fe['due_day'])) ?> of month &bull; <?= e($fe['category']) ?></p>
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-200/80 flex items-center justify-between">
                                <span class="text-base font-extrabold text-rose-600"><?= Format::currency($fe['amount']) ?></span>
                                <?php if ($canModify && !$fe['is_paid_this_month']): ?>
                                    <button type="button" onclick="openPayModal(<?= htmlspecialchars(json_encode($fe), ENT_QUOTES) ?>)" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs transition-colors">
                                        Pay Now
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- 4. DAILY SUMMARY & QUICK ACTIONS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                        <h3 class="text-base font-bold text-slate-900">Today's Summary</h3>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-slate-100 font-semibold text-slate-600">
                            <?= date('d M Y') ?>
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-medium">Money In:</span>
                            <span class="font-bold text-emerald-600"><?= Format::currency($brandStats['today_in']) ?></span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-slate-500 font-medium">Money Out:</span>
                            <span class="font-bold text-rose-600"><?= Format::currency($brandStats['today_out']) ?></span>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex justify-between items-center text-base">
                            <span class="font-bold text-slate-800">Net Change:</span>
                            <span class="font-extrabold <?= $brandStats['today_net'] >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
                                <?= $brandStats['today_net'] >= 0 ? '+' : '' ?><?= Format::currency($brandStats['today_net']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
                <h3 class="text-base font-bold text-slate-900 mb-4">Quick Financial Actions</h3>

                <?php if ($canModify): ?>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <a href="<?= BASE_URL ?>/money-in/create?brand_id=<?= $currentBrand['id'] ?>" class="flex flex-col items-center justify-center p-4 rounded-xl border border-emerald-100 bg-emerald-50/50 hover:bg-emerald-100/70 text-emerald-800 transition-all text-center group">
                            <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center mb-2 shadow-xs group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-arrow-down"></i>
                            </div>
                            <span class="text-xs font-bold">Add Money In</span>
                        </a>

                        <a href="<?= BASE_URL ?>/expenses/create?brand_id=<?= $currentBrand['id'] ?>" class="flex flex-col items-center justify-center p-4 rounded-xl border border-rose-100 bg-rose-50/50 hover:bg-rose-100/70 text-rose-800 transition-all text-center group">
                            <div class="w-10 h-10 rounded-full bg-rose-500 text-white flex items-center justify-center mb-2 shadow-xs group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-arrow-up"></i>
                            </div>
                            <span class="text-xs font-bold">Add Expense</span>
                        </a>

                        <a href="<?= BASE_URL ?>/loans/create" class="flex flex-col items-center justify-center p-4 rounded-xl border border-amber-100 bg-amber-50/50 hover:bg-amber-100/70 text-amber-800 transition-all text-center group">
                            <div class="w-10 h-10 rounded-full bg-amber-500 text-white flex items-center justify-center mb-2 shadow-xs group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-handshake"></i>
                            </div>
                            <span class="text-xs font-bold">Record Loan</span>
                        </a>

                        <a href="<?= BASE_URL ?>/repayments/create" class="flex flex-col items-center justify-center p-4 rounded-xl border border-sky-100 bg-sky-50/50 hover:bg-sky-100/70 text-sky-800 transition-all text-center group">
                            <div class="w-10 h-10 rounded-full bg-sky-500 text-white flex items-center justify-center mb-2 shadow-xs group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-rotate-left"></i>
                            </div>
                            <span class="text-xs font-bold">Record Repayment</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="p-6 bg-slate-50 border border-slate-200 rounded-xl text-center">
                        <i class="fa-solid fa-lock text-slate-400 text-2xl mb-2"></i>
                        <p class="text-sm font-semibold text-slate-700">READ ONLY ACCESS</p>
                        <p class="text-xs text-slate-500 mt-1">You are currently viewing this brand in Read Only mode. Action controls are disabled.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BANK ACCOUNTS BREAKDOWN -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-900">Bank Accounts &amp; Liquid Balances</h3>
                <?php if ($canModify): ?>
                    <a href="<?= BASE_URL ?>/bank-accounts/create?brand_id=<?= $currentBrand['id'] ?>" class="text-xs font-semibold text-sky-600 hover:text-sky-700">
                        + Add Bank Account
                    </a>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($bankAccounts as $acc): ?>
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:border-slate-300 transition-all">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900"><?= e($acc['bank_name']) ?></span>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 font-semibold uppercase">Active</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Acc: <?= Security::maskAccountNumber($acc['account_number']) ?></p>
                        <div class="mt-3 pt-3 border-t border-slate-200/60 flex justify-between items-baseline">
                            <span class="text-xs font-medium text-slate-500">Available:</span>
                            <span class="text-lg font-extrabold text-slate-900"><?= Format::currency($acc['current_balance']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<!-- PAY NOW MODAL -->
<div id="payModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
        <div class="p-6 bg-gradient-to-r from-emerald-700 to-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-bold text-lg">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold">Confirm &amp; Record Money Out</h3>
                    <p class="text-xs text-emerald-200">Automatically creates Money Out record in Transaction History</p>
                </div>
            </div>
            <button type="button" onclick="closePayModal()" class="text-white/70 hover:text-white text-xl font-bold">&times;</button>
        </div>

        <form id="payForm" action="" method="POST" class="p-6 space-y-4">
            <?= Security::csrfField() ?>
            <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI']) ?>">

            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Target Brand &amp; Expense</span>
                <span id="modalTitle" class="text-base font-bold text-slate-900 block mt-0.5"></span>
                <span id="modalBrand" class="text-xs font-semibold text-slate-500 block"></span>
                <div class="mt-2 text-2xl font-extrabold text-rose-600" id="modalAmount"></div>
            </div>

            <div>
                <label for="modal_payment_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Payment Date *</label>
                <input type="date" id="modal_payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required
                       class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div>
                <label for="modal_note" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Transaction Note (Optional)</label>
                <input type="text" id="modal_note" name="note" placeholder="Paid for August 2026..."
                       class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closePayModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm shadow-md">
                    CONFIRM &amp; RECORD MONEY OUT
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPayModal(fe) {
    document.getElementById('payForm').action = '<?= BASE_URL ?>/fixed-expenses/' + fe.id + '/pay';
    document.getElementById('modalTitle').innerText = fe.title;
    document.getElementById('modalBrand').innerText = 'Brand: ' + fe.brand_name + ' | Preferred Bank: ' + fe.bank_name;
    document.getElementById('modalAmount').innerText = '₹ ' + parseFloat(fe.amount).toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('modal_note').value = 'Paid fixed monthly commitment for ' + new Date().toLocaleString('en-US', { month: 'long', year: 'numeric' });
    document.getElementById('payModal').classList.remove('hidden');
}

function closePayModal() {
    document.getElementById('payModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
