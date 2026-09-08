<?php
$pageTitle = 'Fixed Monthly Expenses';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$totalMonthlyCommitment = 0;
foreach ($fixedExpenses as $fe) {
    if ($fe['status'] === 'active') {
        $totalMonthlyCommitment += (float)$fe['amount'];
    }
}
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Fixed Monthly Expenses &amp; Recurring Commitments</h2>
                <p class="text-xs text-slate-500">Manage fixed recurring obligations (Office Rent, Salaries, Servers, Internet) due every month.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 w-full md:w-auto">
                <!-- Brand Filter -->
                <form action="<?= BASE_URL ?>/fixed-expenses" method="GET" class="flex items-center gap-2 bg-white p-1.5 rounded-xl border border-slate-200 shadow-xs w-full sm:w-auto">
                    <select name="brand_id" onchange="this.form.submit()" class="w-full sm:w-auto px-3 py-2 sm:py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700">
                        <?php if (count($brands) > 1 || Auth::isSuperAdmin() || Auth::isManager()): ?>
                            <option value="">All Brands</option>
                        <?php endif; ?>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= (string)$selectedBrandId === (string)$b['id'] ? 'selected' : '' ?>>
                                <?= e($b['brand_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <?php if (!Auth::isManager()): ?>
                    <form action="<?= BASE_URL ?>/fixed-expenses/send-notifications" method="POST" class="w-full sm:w-auto inline-block" onsubmit="return confirm('Send email notification reminders for pending fixed expenses to brand admin email address(es)?')">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="brand_id" value="<?= e($selectedBrandId) ?>">
                        <input type="hidden" name="redirect_to" value="<?= e($_SERVER['REQUEST_URI']) ?>">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-95 min-h-[42px]">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Send Email Reminders</span>
                        </button>
                    </form>

                    <a href="<?= BASE_URL ?>/fixed-expenses/create" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-xs transition-all active:scale-95 min-h-[42px]">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add Fixed Expense</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>


        <?php if (!Auth::isSuperAdmin() && !empty($feNotifications) && $feNotifications['total_pending_count'] > 0): ?>
            <!-- FIXED EXPENSE NOTIFICATION BANNER -->
            <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-2xl p-4 shadow-xs">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-amber-100 rounded-xl text-amber-700 font-bold text-lg">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-amber-900">
                            Fixed Expense Reminders (<?= $feNotifications['total_pending_count'] ?> Pending for Current Month)
                        </h3>
                        <p class="text-xs text-amber-700 mt-0.5">
                            You have 
                            <?php if ($feNotifications['overdue_count'] > 0): ?>
                                <strong class="text-rose-700"><?= $feNotifications['overdue_count'] ?> overdue</strong> expense(s)
                            <?php endif; ?>
                            <?php if ($feNotifications['overdue_count'] > 0 && $feNotifications['due_today_count'] > 0): ?> and <?php endif; ?>
                            <?php if ($feNotifications['due_today_count'] > 0): ?>
                                <strong class="text-amber-800"><?= $feNotifications['due_today_count'] ?> expense(s) due today</strong>
                            <?php endif; ?>
                            totalling <strong><?= Format::currency($feNotifications['total_pending_amount']) ?></strong>.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>


        <!-- TOTAL MONTHLY COMMITMENT HIGHLIGHT CARD -->
        <div class="bg-gradient-to-r from-rose-900 via-slate-900 to-rose-950 rounded-2xl p-6 text-white shadow-lg border border-rose-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-300 block mb-1">MANDATORY MONTHLY OBLIGATIONS</span>
                <div class="text-sm font-medium text-slate-300">TOTAL FIXED MONTHLY COMMITMENTS</div>
                <div class="text-3xl sm:text-4xl font-extrabold text-rose-400 tracking-tight mt-1">
                    <?= Format::currency($totalMonthlyCommitment) ?> <span class="text-xs text-rose-300 font-normal">/ month</span>
                </div>
            </div>

            <div class="text-xs text-slate-300 bg-white/10 p-3 rounded-xl border border-white/10 space-y-1">
                <div><i class="fa-solid fa-circle-check text-emerald-400 mr-1.5"></i> One-click auto Money Out entry</div>
                <div><i class="fa-solid fa-calendar text-rose-300 mr-1.5"></i> Automatic due-day reminders</div>
            </div>
        </div>

        <!-- FIXED EXPENSES LIST -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm min-w-[900px]">
                    <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Expense Title</th>
                            <th class="px-5 py-3.5">Brand</th>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5 text-right">Monthly Amount</th>
                            <th class="px-5 py-3.5 text-center">Due Day</th>
                            <th class="px-5 py-3.5 text-center">Current Month Status</th>
                            <th class="px-5 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($fixedExpenses)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block"></i>
                                    No fixed monthly expenses configured yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fixedExpenses as $fe): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-slate-900 text-sm"><?= e($fe['title']) ?></div>
                                        <p class="text-xs text-slate-500">Bank: <?= e($fe['bank_name']) ?> (<?= Security::maskAccountNumber($fe['account_number']) ?>)</p>
                                        <?php if ($fe['note']): ?>
                                            <p class="text-xs text-slate-400 italic mt-0.5"><?= e($fe['note']) ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 font-bold text-slate-900">
                                        <?= e($fe['brand_name']) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            <?= e($fe['category']) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right font-extrabold text-rose-600 text-base">
                                        <?= Format::currency($fe['amount']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-center font-bold text-slate-700">
                                        <?= (int)$fe['due_day'] ?><?= date('S', mktime(0,0,0,1,(int)$fe['due_day'])) ?> of month
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold <?= $fe['status_badge_class'] ?? 'bg-amber-100 text-amber-800' ?>">
                                            <i class="<?= $fe['status_icon'] ?? 'fa-solid fa-clock' ?>"></i> <?= e($fe['status_text'] ?? 'Pending for ' . date('F')) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- ONE-CLICK RECORD PAYMENT BUTTON -->
                                            <?php if (!Auth::isManager() && Auth::canModifyBrandData((int)$fe['brand_id']) && !$fe['is_paid_this_month'] && ($fe['status_type'] ?? '') !== 'future_start'): ?>
                                                <button type="button" onclick="openPayModal(<?= htmlspecialchars(json_encode($fe), ENT_QUOTES) ?>)" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-xs transition-colors inline-flex items-center gap-1.5">
                                                    <i class="fa-solid fa-check"></i> Pay Now
                                                </button>
                                            <?php endif; ?>

                                            <?php if (Auth::canModifyBrandData((int)$fe['brand_id'])): ?>
                                                <a href="<?= BASE_URL ?>/fixed-expenses/<?= $fe['id'] ?>/edit" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <form action="<?= BASE_URL ?>/fixed-expenses/<?= $fe['id'] ?>/delete" method="POST" onsubmit="return confirmDeleteForm(event, this, 'Delete Fixed Expense?', 'Are you sure you want to delete this fixed expense commitment?')">
                                                    <?= Security::csrfField() ?>
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>

                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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
