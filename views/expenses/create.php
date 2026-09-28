<?php
$pageTitle = 'Add Expense';
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
                    <i class="fa-solid fa-circle-arrow-up"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Record Brand Expense</h2>
                    <p class="text-xs text-slate-500">Track day-to-day operational spendings, marketing, or salaries.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/expenses/store" method="POST" class="space-y-5">
                <?= Security::csrfField() ?>

                <div>
                    <label for="brand_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Target Brand *</label>
                    <select id="brand_id" name="brand_id" onchange="window.location.href='<?= BASE_URL ?>/expenses/create?brand_id='+this.value" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
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
                            <input type="number" step="0.01" min="0.01" id="amount" name="amount" required placeholder="5000"
                                   class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold text-base focus:ring-2 focus:ring-rose-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="transaction_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Date *</label>
                        <input type="date" id="transaction_date" name="transaction_date" value="<?= date('Y-m-d') ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="purpose" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Expense Purpose *</label>
                        <input type="text" id="purpose" name="purpose" required placeholder="e.g. Facebook Advertising, Office Rent"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Expense Category *</label>
                        <select id="category" name="category" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat ?>"><?= $cat ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Payment Method Toggle & Account Selection -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2.5">
                        Payment Medium <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <label id="mode_bank_label" onclick="selectPaymentMode('bank')" class="cursor-pointer border-2 border-indigo-600 bg-indigo-50/70 rounded-xl p-3 flex items-center justify-center gap-2.5 transition-all shadow-xs">
                            <input type="radio" name="payment_mode" value="bank" id="radio_bank" checked class="hidden">
                            <i class="fa-solid fa-building-columns text-indigo-600 text-base"></i>
                            <div class="text-left">
                                <span class="text-xs font-bold text-slate-900 block leading-tight">Bank Account</span>
                                <span class="text-[10px] text-slate-500 block">IMPS / NEFT / UPI</span>
                            </div>
                        </label>

                        <label id="mode_cash_label" onclick="selectPaymentMode('cash')" class="cursor-pointer border-2 border-slate-200 bg-white hover:border-slate-300 rounded-xl p-3 flex items-center justify-center gap-2.5 transition-all shadow-xs">
                            <input type="radio" name="payment_mode" value="cash" id="radio_cash" class="hidden">
                            <i class="fa-solid fa-money-bill-wave text-emerald-600 text-base"></i>
                            <div class="text-left">
                                <span class="text-xs font-bold text-slate-900 block leading-tight">Hand Cash</span>
                                <span class="text-[10px] text-slate-500 block">Physical Cash In Hand</span>
                            </div>
                        </label>
                    </div>

                    <!-- Bank Account Dropdown (Visible when Bank selected) -->
                    <div id="bank_account_wrapper">
                        <label for="bank_select" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Select Bank Account *</label>
                        <select id="bank_select" onchange="syncSelectedAccount(this.value)"
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none">
                            <option value="">-- Select Bank Account --</option>
                            <?php foreach ($bankAccounts as $acc): ?>
                                <option value="<?= $acc['id'] ?>">
                                    <?= e($acc['bank_name']) ?> (Acc: <?= Security::maskAccountNumber($acc['account_number']) ?>) — Available: <?= Format::currency($acc['current_balance']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Hand Cash Banner (Visible when Cash selected) -->
                    <div id="cash_account_wrapper" class="hidden">
                        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-base">
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-emerald-900 block">Hand Cash Drawer</span>
                                    <span class="text-[11px] text-emerald-700">Deducts immediately from physical cash in hand</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-emerald-600 block uppercase font-semibold">Available Cash</span>
                                <span class="text-sm font-extrabold text-emerald-800">
                                    <?= Format::currency($handCashAccount['current_balance'] ?? 0.00) ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Input Submitted with Form -->
                    <input type="hidden" id="bank_account_id" name="bank_account_id" value="<?= !empty($bankAccounts[0]['id']) ? $bankAccounts[0]['id'] : ($handCashAccount['id'] ?? '') ?>" required>
                </div>

                <div>
                    <label for="note" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Optional Note</label>
                    <textarea id="note" name="note" rows="2" placeholder="Campaign name, invoice number..."
                              class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <a href="<?= BASE_URL ?>/dashboard" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm shadow-md transition-all">
                        ADD EXPENSE
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
const cashAccountId = "<?= $handCashAccount['id'] ?? '' ?>";

function selectPaymentMode(mode) {
    const bankLabel = document.getElementById('mode_bank_label');
    const cashLabel = document.getElementById('mode_cash_label');
    const bankWrapper = document.getElementById('bank_account_wrapper');
    const cashWrapper = document.getElementById('cash_account_wrapper');
    const hiddenInput = document.getElementById('bank_account_id');
    const bankSelect = document.getElementById('bank_select');

    if (mode === 'cash') {
        document.getElementById('radio_cash').checked = true;
        cashLabel.className = 'cursor-pointer border-2 border-emerald-600 bg-emerald-50/70 rounded-xl p-3 flex items-center justify-center gap-2.5 transition-all shadow-xs';
        bankLabel.className = 'cursor-pointer border-2 border-slate-200 bg-white hover:border-slate-300 rounded-xl p-3 flex items-center justify-center gap-2.5 transition-all shadow-xs';
        bankWrapper.classList.add('hidden');
        cashWrapper.classList.remove('hidden');
        hiddenInput.value = cashAccountId;
        bankSelect.removeAttribute('required');
    } else {
        document.getElementById('radio_bank').checked = true;
        bankLabel.className = 'cursor-pointer border-2 border-indigo-600 bg-indigo-50/70 rounded-xl p-3 flex items-center justify-center gap-2.5 transition-all shadow-xs';
        cashLabel.className = 'cursor-pointer border-2 border-slate-200 bg-white hover:border-slate-300 rounded-xl p-3 flex items-center justify-center gap-2.5 transition-all shadow-xs';
        cashWrapper.classList.add('hidden');
        bankWrapper.classList.remove('hidden');
        hiddenInput.value = bankSelect.value;
        bankSelect.setAttribute('required', 'required');
    }
}

function syncSelectedAccount(val) {
    document.getElementById('bank_account_id').value = val;
}

// Initial sync
document.addEventListener('DOMContentLoaded', function() {
    const bankSelect = document.getElementById('bank_select');
    if (bankSelect.options.length > 1) {
        bankSelect.selectedIndex = 1;
        syncSelectedAccount(bankSelect.value);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
