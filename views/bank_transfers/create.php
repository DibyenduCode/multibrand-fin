<?php
$pageTitle = 'Bank to Bank Transfer';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-4xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="<?= BASE_URL ?>/bank-accounts" class="text-xs font-semibold text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Bank Accounts
                    </a>
                </div>
                <h2 class="text-xl font-bold text-slate-900">Funds &amp; Cash Transfer</h2>
                <p class="text-xs text-slate-500">Move funds between bank accounts and hand cash drawer without affecting profit/loss (Contra Voucher).</p>
            </div>

            <a href="<?= BASE_URL ?>/transactions?type=transfer_out" class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Transfer History</span>
            </a>
        </div>

        <?php if (count($bankAccounts) < 2): ?>
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto text-xl">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">At least 2 Accounts Required</h3>
                <p class="text-xs text-slate-600 max-w-md mx-auto">
                    The selected brand currently has <?= count($bankAccounts) ?> active treasury account. To execute an internal transfer, you need at least two accounts (such as a Bank Account and a Hand Cash drawer).
                </p>
                <div class="pt-2">
                    <a href="<?= BASE_URL ?>/bank-accounts/create?brand_id=<?= $selectedBrandId ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add Another Account</span>
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Internal Fund Transfer (Contra Voucher)</h3>
                            <p class="text-xs text-slate-500">Supports Bank-to-Bank transfers, ATM Cash Withdrawals, and Cash Deposits.</p>
                        </div>
                    </div>
                </div>

                <form action="<?= BASE_URL ?>/bank-transfers/store" method="POST" id="transferForm" class="p-6 space-y-6">
                    <?= Security::csrfField() ?>

                    <!-- Brand Selector -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                            Brand Entity <span class="text-rose-500">*</span>
                        </label>
                        <select name="brand_id" id="brandSelector" onchange="window.location.href='<?= BASE_URL ?>/bank-transfers/create?brand_id=' + this.value" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= (int)$selectedBrandId === (int)$b['id'] ? 'selected' : '' ?>>
                                    <?= e($b['brand_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Transfers can be executed between accounts belonging to the same brand.</p>
                    </div>

                    <!-- Source & Destination Account Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Source Account (From) -->
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold uppercase tracking-wider text-rose-600 flex items-center gap-1.5">
                                    <i class="fa-solid fa-arrow-up-from-bracket"></i> Transfer From (Source)
                                </label>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">Debit (-)</span>
                            </div>

                            <select name="from_bank_account_id" id="fromAccountSelect" required class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-rose-500 focus:outline-none transition-all">
                                <option value="">-- Select Source Account --</option>
                                <?php foreach ($bankAccounts as $acc): 
                                    $isCash = ($acc['account_type'] ?? 'bank') === 'cash';
                                    $label = $isCash ? '💵 Hand Cash (Petty Cash)' : '🏦 ' . e($acc['bank_name']) . ' (' . Security::maskAccountNumber($acc['account_number']) . ')';
                                ?>
                                    <option value="<?= $acc['id'] ?>" 
                                            data-balance="<?= (float)$acc['current_balance'] ?>"
                                            data-name="<?= e($acc['bank_name']) ?>"
                                            data-type="<?= $acc['account_type'] ?? 'bank' ?>"
                                            <?= ((int)($fromBankAccountId ?? 0) === (int)$acc['id']) ? 'selected' : '' ?>>
                                        <?= $label ?> — Available: <?= Format::currency($acc['current_balance']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="p-3 bg-white rounded-lg border border-slate-100 text-xs space-y-1">
                                <div class="flex justify-between text-slate-500">
                                    <span>Current Balance:</span>
                                    <span id="fromCurrentBalance" class="font-bold text-slate-900">₹ 0.00</span>
                                </div>
                                <div class="flex justify-between text-rose-600 font-semibold" id="fromAfterRow" style="display:none;">
                                    <span>After Transfer:</span>
                                    <span id="fromAfterBalance">₹ 0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Destination Account (To) -->
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold uppercase tracking-wider text-emerald-600 flex items-center gap-1.5">
                                    <i class="fa-solid fa-arrow-down-to-bracket"></i> Transfer To (Destination)
                                </label>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Credit (+)</span>
                            </div>

                            <select name="to_bank_account_id" id="toAccountSelect" required class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:outline-none transition-all">
                                <option value="">-- Select Destination Account --</option>
                                <?php foreach ($bankAccounts as $acc): 
                                    $isCash = ($acc['account_type'] ?? 'bank') === 'cash';
                                    $label = $isCash ? '💵 Hand Cash (Petty Cash)' : '🏦 ' . e($acc['bank_name']) . ' (' . Security::maskAccountNumber($acc['account_number']) . ')';
                                ?>
                                    <option value="<?= $acc['id'] ?>" 
                                            data-balance="<?= (float)$acc['current_balance'] ?>"
                                            data-name="<?= e($acc['bank_name']) ?>"
                                            data-type="<?= $acc['account_type'] ?? 'bank' ?>">
                                        <?= $label ?> — Available: <?= Format::currency($acc['current_balance']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <div class="p-3 bg-white rounded-lg border border-slate-100 text-xs space-y-1">
                                <div class="flex justify-between text-slate-500">
                                    <span>Current Balance:</span>
                                    <span id="toCurrentBalance" class="font-bold text-slate-900">₹ 0.00</span>
                                </div>
                                <div class="flex justify-between text-emerald-600 font-semibold" id="toAfterRow" style="display:none;">
                                    <span>After Transfer:</span>
                                    <span id="toAfterBalance">₹ 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Details Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Amount -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Transfer Amount (INR) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 text-slate-400 font-bold text-sm">₹</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="transferAmount" required placeholder="0.00" class="w-full pl-8 pr-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                            </div>
                            <div id="insufficientWarning" class="hidden mt-1.5 text-xs text-rose-600 font-semibold flex items-center gap-1">
                                <i class="fa-solid fa-circle-exclamation text-xs"></i>
                                <span>Amount exceeds available balance in source account!</span>
                            </div>
                        </div>

                        <!-- Transfer Date -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Transfer Date <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="transaction_date" value="<?= date('Y-m-d') ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                        </div>

                        <!-- Reference / UTR Number -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Reference / UTR / Cheque No. <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <input type="text" name="reference_number" placeholder="e.g. UTR12345678, IMPS, NEFT" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                        </div>

                        <!-- Note / Remarks -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                                Remarks / Note <span class="text-slate-400 font-normal">(Optional)</span>
                            </label>
                            <input type="text" name="note" placeholder="e.g. Liquidity rebalancing, vendor payment pool" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:ring-2 focus:ring-indigo-500 focus:outline-none transition-all">
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="<?= BASE_URL ?>/bank-accounts" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                            Cancel
                        </a>
                        <button type="submit" id="submitTransferBtn" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 cursor-pointer active:scale-95">
                            <i class="fa-solid fa-arrow-right-arrow-left"></i>
                            <span>Execute Transfer</span>
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromSelect = document.getElementById('fromAccountSelect');
    const toSelect = document.getElementById('toAccountSelect');
    const amountInput = document.getElementById('transferAmount');
    const fromCurBalSpan = document.getElementById('fromCurrentBalance');
    const toCurBalSpan = document.getElementById('toCurrentBalance');
    const fromAfterRow = document.getElementById('fromAfterRow');
    const toAfterRow = document.getElementById('toAfterRow');
    const fromAfterSpan = document.getElementById('fromAfterBalance');
    const toAfterSpan = document.getElementById('toAfterBalance');
    const warningDiv = document.getElementById('insufficientWarning');
    const submitBtn = document.getElementById('submitTransferBtn');
    const form = document.getElementById('transferForm');

    function formatINR(val) {
        return '₹ ' + Number(val).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateCalculations() {
        if (!fromSelect || !toSelect || !amountInput) return;

        const fromOpt = fromSelect.options[fromSelect.selectedIndex];
        const toOpt = toSelect.options[toSelect.selectedIndex];

        const fromBal = fromOpt && fromOpt.value ? parseFloat(fromOpt.dataset.balance || 0) : null;
        const toBal = toOpt && toOpt.value ? parseFloat(toOpt.dataset.balance || 0) : null;
        const amount = parseFloat(amountInput.value || 0);

        if (fromBal !== null) {
            fromCurBalSpan.innerText = formatINR(fromBal);
        } else {
            fromCurBalSpan.innerText = '₹ 0.00';
        }

        if (toBal !== null) {
            toCurBalSpan.innerText = formatINR(toBal);
        } else {
            toCurBalSpan.innerText = '₹ 0.00';
        }

        if (amount > 0 && fromBal !== null) {
            const newFrom = fromBal - amount;
            fromAfterSpan.innerText = formatINR(newFrom);
            fromAfterRow.style.display = 'flex';

            if (newFrom < 0) {
                warningDiv.classList.remove('hidden');
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                warningDiv.classList.add('hidden');
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        } else {
            fromAfterRow.style.display = 'none';
            warningDiv.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        if (amount > 0 && toBal !== null) {
            const newTo = toBal + amount;
            toAfterSpan.innerText = formatINR(newTo);
            toAfterRow.style.display = 'flex';
        } else {
            toAfterRow.style.display = 'none';
        }
    }

    fromSelect?.addEventListener('change', function() {
        // Prevent picking same account
        if (this.value && this.value === toSelect.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Selection',
                text: 'Source and Destination bank accounts cannot be the same.',
                confirmButtonColor: '#4f46e5'
            });
            this.value = '';
        }
        updateCalculations();
    });

    toSelect?.addEventListener('change', function() {
        // Prevent picking same account
        if (this.value && this.value === fromSelect.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Selection',
                text: 'Destination account cannot be identical to Source account.',
                confirmButtonColor: '#4f46e5'
            });
            this.value = '';
        }
        updateCalculations();
    });

    amountInput?.addEventListener('input', updateCalculations);

    updateCalculations();

    form?.addEventListener('submit', function(e) {
        if (!fromSelect.value || !toSelect.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Missing Accounts',
                text: 'Please choose both source and destination bank accounts.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        if (fromSelect.value === toSelect.value) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Accounts',
                text: 'Source and destination accounts must be different.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        const amt = parseFloat(amountInput.value || 0);
        if (amt <= 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Amount',
                text: 'Transfer amount must be greater than zero.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        e.preventDefault();
        const fromName = fromSelect.options[fromSelect.selectedIndex].dataset.name || 'Source';
        const toName = toSelect.options[toSelect.selectedIndex].dataset.name || 'Destination';

        Swal.fire({
            title: 'Confirm Fund Transfer?',
            html: `Execute transfer of <b>${formatINR(amt)}</b><br>from <b>${fromName}</b><br>to <b>${toName}</b>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Execute Transfer'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
