<?php
$pageTitle = 'Record Inter-Brand Loan';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-3xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-6">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Create Inter-Brand Loan</h2>
                    <p class="text-xs text-slate-500">Record financial support or internal liquidity transfer from one brand to another.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/loans/store" method="POST" class="space-y-5" id="loanForm">
                <?= Security::csrfField() ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Lender Brand -->
                    <div>
                        <label for="lender_brand_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Lender Brand (Money Provider) *</label>
                        <select id="lender_brand_id" name="lender_brand_id" required onchange="onLenderBrandChange()"
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="">-- Select Lender Brand --</option>
                            <?php foreach ($lenderBrands as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($selectedLenderId ?? 0) == $b['id'] ? 'selected' : '' ?>><?= e($b['brand_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Borrower Brand -->
                    <div>
                        <label for="borrower_brand_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Borrower Brand (Money Receiver) *</label>
                        <select id="borrower_brand_id" name="borrower_brand_id" required onchange="onBorrowerBrandChange()"
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="">-- Select Borrower Brand --</option>
                            <?php foreach ($allBrands as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= e($b['brand_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Lender Bank Account -->
                    <div>
                        <label for="lender_bank_account_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Lender Bank Account *</label>
                        <select id="lender_bank_account_id" name="lender_bank_account_id" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="">-- Select Lender Brand First --</option>
                        </select>
                    </div>

                    <!-- Borrower Bank Account -->
                    <div>
                        <label for="borrower_bank_account_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Borrower Bank Account *</label>
                        <select id="borrower_bank_account_id" name="borrower_bank_account_id" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="">-- Select Borrower Brand First --</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label for="original_amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Loan Amount (₹) *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-bold text-sm">₹</div>
                            <input type="number" step="0.01" min="1" id="original_amount" name="original_amount" required placeholder="100000"
                                   class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold text-base focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="loan_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Loan Date *</label>
                        <input type="date" id="loan_date" name="loan_date" value="<?= date('Y-m-d') ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="due_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Due Date (Optional)</label>
                        <input type="date" id="due_date" name="due_date"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="purpose" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Loan Purpose *</label>
                    <select id="purpose" name="purpose" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        <?php foreach ($purposes as $p): ?>
                            <option value="<?= $p ?>"><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Description / Notes</label>
                    <textarea id="description" name="description" rows="3" placeholder="Provide background detail for this internal loan..."
                              class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <a href="<?= BASE_URL ?>/loans" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-sm shadow-md transition-all">
                        CREATE INTER-BRAND LOAN
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
function syncBrandDropdowns() {
    const lenderId = document.getElementById('lender_brand_id').value;
    const borrowerId = document.getElementById('borrower_brand_id').value;

    const lenderSelect = document.getElementById('lender_brand_id');
    const borrowerSelect = document.getElementById('borrower_brand_id');

    // Filter Borrower dropdown options
    Array.from(borrowerSelect.options).forEach(opt => {
        if (!opt.value) return;
        if (lenderId && opt.value === lenderId) {
            opt.disabled = true;
            opt.hidden = true;
            if (opt.selected) {
                borrowerSelect.value = '';
                resetBankAccount('borrower');
            }
        } else {
            opt.disabled = false;
            opt.hidden = false;
        }
    });

    // Filter Lender dropdown options
    Array.from(lenderSelect.options).forEach(opt => {
        if (!opt.value) return;
        if (borrowerId && opt.value === borrowerId) {
            opt.disabled = true;
            opt.hidden = true;
            if (opt.selected) {
                lenderSelect.value = '';
                resetBankAccount('lender');
            }
        } else {
            opt.disabled = false;
            opt.hidden = false;
        }
    });
}

function resetBankAccount(type) {
    const select = document.getElementById(type + '_bank_account_id');
    const label = type === 'lender' ? 'Lender' : 'Borrower';
    select.innerHTML = `<option value="">-- Select ${label} Brand First --</option>`;
}

async function onLenderBrandChange() {
    syncBrandDropdowns();
    await loadBankAccounts('lender');
}

async function onBorrowerBrandChange() {
    syncBrandDropdowns();
    await loadBankAccounts('borrower');
}

async function loadBankAccounts(type) {
    const brandId = document.getElementById(type + '_brand_id').value;
    const select = document.getElementById(type + '_bank_account_id');
    const label = type === 'lender' ? 'Lender' : 'Borrower';

    if (!brandId) {
        select.innerHTML = `<option value="">-- Select ${label} Brand First --</option>`;
        return;
    }

    select.innerHTML = '<option value="">Loading accounts...</option>';

    try {
        const response = await fetch(`<?= BASE_URL ?>/api/bank-accounts?brand_id=${brandId}`);
        const data = await response.json();
        if (data.success && data.accounts.length > 0) {
            let html = `<option value="">-- Select ${label} Bank Account --</option>`;
            data.accounts.forEach(acc => {
                const balFormatted = parseFloat(acc.current_balance).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                html += `<option value="${acc.id}">${acc.bank_name} (Acc: ${acc.masked_account}) - Avail: ₹ ${balFormatted}</option>`;
            });
            select.innerHTML = html;
        } else {
            select.innerHTML = '<option value="">No active bank accounts found for this brand</option>';
        }
    } catch (e) {
        select.innerHTML = '<option value="">Failed to load accounts</option>';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    syncBrandDropdowns();
    if (document.getElementById('lender_brand_id').value) {
        loadBankAccounts('lender');
    }
    if (document.getElementById('borrower_brand_id').value) {
        loadBankAccounts('borrower');
    }

    document.getElementById('loanForm').addEventListener('submit', function(e) {
        const lenderId = document.getElementById('lender_brand_id').value;
        const borrowerId = document.getElementById('borrower_brand_id').value;
        if (lenderId && borrowerId && lenderId === borrowerId) {
            e.preventDefault();
            alert('Lender Brand and Borrower Brand must be different!');
            return false;
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
