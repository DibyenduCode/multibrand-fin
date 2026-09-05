<?php
$pageTitle = 'Record Loan Repayment';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-3xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-6">
                <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Record Loan Repayment</h2>
                    <p class="text-xs text-slate-500">Record a flexible partial or full repayment for an active inter-brand loan.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/repayments/store" method="POST" class="space-y-5">
                <?= Security::csrfField() ?>

                <div>
                    <label for="loan_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Select Loan *</label>
                    <select id="loan_id" name="loan_id" required onchange="window.location.href='<?= BASE_URL ?>/repayments/create?loan_id='+this.value"
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <option value="">-- Select Active Inter-Brand Loan --</option>
                        <?php foreach ($loans as $l): ?>
                            <option value="<?= $l['id'] ?>" <?= ($selectedLoan && (int)$selectedLoan['id'] === (int)$l['id']) ? 'selected' : '' ?>>
                                <?= e($l['loan_number']) ?>: <?= e($l['borrower_brand_name']) ?> owes <?= e($l['lender_brand_name']) ?> (Remaining: <?= Format::currency($l['remaining_amount']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($selectedLoan): ?>
                    <!-- Loan Info Summary Card -->
                    <div class="p-4 bg-sky-50/60 rounded-xl border border-sky-100 text-xs space-y-1">
                        <div class="font-bold text-sky-900">Selected Loan: <?= e($selectedLoan['loan_number']) ?></div>
                        <div class="flex justify-between text-slate-700">
                            <span>Borrower (Payer): <strong><?= e($selectedLoan['borrower_brand_name']) ?></strong></span>
                            <span>Lender (Receiver): <strong><?= e($selectedLoan['lender_brand_name']) ?></strong></span>
                        </div>
                        <div class="flex justify-between text-slate-700 pt-1">
                            <span>Original Amount: <?= Format::currency($selectedLoan['original_amount']) ?></span>
                            <span class="font-extrabold text-amber-700">Outstanding Liability: <?= Format::currency($selectedLoan['remaining_amount']) ?></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Repayment Amount (₹) *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-bold text-sm">₹</div>
                                <input type="number" step="0.01" min="0.01" max="<?= (float)$selectedLoan['remaining_amount'] ?>" id="amount" name="amount" required placeholder="Amount to repay"
                                       class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold text-base focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <span class="text-[11px] text-slate-400 mt-1 block">Maximum allowed: <?= Format::currency($selectedLoan['remaining_amount']) ?></span>
                        </div>

                        <div>
                            <label for="payment_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Payment Date *</label>
                            <input type="date" id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required
                                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="from_bank_account_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">From Bank Account (<?= e($selectedLoan['borrower_brand_name']) ?>) *</label>
                            <select id="from_bank_account_id" name="from_bank_account_id" required
                                    class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <?php foreach ($fromBankAccounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>"><?= e($acc['bank_name']) ?> (Acc: <?= Security::maskAccountNumber($acc['account_number']) ?>) - Avail: <?= Format::currency($acc['current_balance']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label for="to_bank_account_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">To Bank Account (<?= e($selectedLoan['lender_brand_name']) ?>) *</label>
                            <select id="to_bank_account_id" name="to_bank_account_id" required
                                    class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                <?php foreach ($toBankAccounts as $acc): ?>
                                    <option value="<?= $acc['id'] ?>"><?= e($acc['bank_name']) ?> (Acc: <?= Security::maskAccountNumber($acc['account_number']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="payment_reference" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Payment Reference (Optional)</label>
                        <input type="text" id="payment_reference" name="payment_reference" placeholder="e.g. UTR / NEFT / IMPS Reference Number"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Optional Note</label>
                        <textarea id="description" name="description" rows="2" placeholder="Repayment note..."
                                  class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <a href="<?= BASE_URL ?>/loans" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-md transition-all">
                            RECORD REPAYMENT
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
