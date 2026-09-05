<?php
$pageTitle = 'Edit Transaction #' . $transaction['id'];
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-3xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center justify-between pb-5 border-b border-slate-100 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Edit Transaction Record #<?= $transaction['id'] ?></h2>
                        <p class="text-xs text-slate-500">Update transaction details, amount, category, or bank account.</p>
                    </div>
                </div>

                <a href="<?= BASE_URL ?>/transactions" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to History</span>
                </a>
            </div>

            <form action="<?= BASE_URL ?>/transactions/<?= $transaction['id'] ?>" method="POST" class="space-y-5">
                <?= Security::csrfField() ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="brand_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Brand *</label>
                        <select id="brand_id" name="brand_id" onchange="loadBankAccounts(this.value)" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= (int)$b['id'] === (int)$transaction['brand_id'] ? 'selected' : '' ?>>
                                    <?= e($b['brand_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Transaction Type *</label>
                        <select id="type" name="type" required
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <option value="income" <?= $transaction['type'] === 'income' ? 'selected' : '' ?>>Income (Money In)</option>
                            <option value="expense" <?= $transaction['type'] === 'expense' ? 'selected' : '' ?>>Expense (Money Out)</option>
                            <option value="loan_given" <?= $transaction['type'] === 'loan_given' ? 'selected' : '' ?>>Loan Given</option>
                            <option value="loan_received" <?= $transaction['type'] === 'loan_received' ? 'selected' : '' ?>>Loan Received</option>
                            <option value="loan_repayment" <?= $transaction['type'] === 'loan_repayment' ? 'selected' : '' ?>>Loan Repayment Paid</option>
                            <option value="loan_repayment_received" <?= $transaction['type'] === 'loan_repayment_received' ? 'selected' : '' ?>>Loan Repayment Received</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="amount" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Amount (₹) *</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-bold text-sm">₹</div>
                            <input type="number" step="0.01" min="0.01" id="amount" name="amount" value="<?= htmlspecialchars($transaction['amount']) ?>" required
                                   class="w-full pl-8 pr-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 font-bold text-base focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="transaction_date" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Transaction Date *</label>
                        <input type="date" id="transaction_date" name="transaction_date" value="<?= htmlspecialchars($transaction['transaction_date']) ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="purpose" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Purpose / Details *</label>
                        <input type="text" id="purpose" name="purpose" value="<?= htmlspecialchars($transaction['purpose']) ?>" required placeholder="e.g. Client Payment, Office Rent"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="category" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Category</label>
                        <select id="category" name="category"
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= e($cat) ?>" <?= ($transaction['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                    <?= e($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="bank_account_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Bank Account *</label>
                    <select id="bank_account_id" name="bank_account_id" required
                            class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <?php foreach ($bankAccounts as $acc): ?>
                            <option value="<?= $acc['id'] ?>" <?= (int)$acc['id'] === (int)$transaction['bank_account_id'] ? 'selected' : '' ?>>
                                <?= e($acc['bank_name']) ?> (Acc: <?= Security::maskAccountNumber($acc['account_number']) ?>) - Balance: <?= Format::currency($acc['current_balance']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="note" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Optional Note</label>
                    <textarea id="note" name="note" rows="3" placeholder="Additional reference details..."
                              class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"><?= htmlspecialchars($transaction['note'] ?? '') ?></textarea>
                </div>

                <div class="pt-4 flex items-center justify-between border-t border-slate-100">
                    <button type="button" onclick="confirmDeleteTransaction()" class="px-4 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-sm transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Delete Transaction</span>
                    </button>

                    <div class="flex items-center gap-3">
                        <a href="<?= BASE_URL ?>/transactions" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-md transition-all">
                            UPDATE TRANSACTION
                        </button>
                    </div>
                </div>
            </form>

            <!-- Hidden form for deletion -->
            <form id="deleteTransactionForm" action="<?= BASE_URL ?>/transactions/<?= $transaction['id'] ?>/delete" method="POST" class="hidden">
                <?= Security::csrfField() ?>
            </form>
        </div>
    </main>
</div>

<script>
function loadBankAccounts(brandId) {
    fetch('<?= BASE_URL ?>/api/bank-accounts?brand_id=' + brandId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('bank_account_id');
                select.innerHTML = '';
                data.accounts.forEach(acc => {
                    const opt = document.createElement('option');
                    opt.value = acc.id;
                    opt.textContent = acc.bank_name + ' (' + acc.masked_account + ') - Balance: ₹ ' + parseFloat(acc.current_balance).toLocaleString('en-IN', {minimumFractionDigits: 2});
                    select.appendChild(opt);
                });
            }
        })
        .catch(err => console.error('Error fetching bank accounts:', err));
}

function confirmDeleteTransaction() {
    SwalTheme.fire({
        title: 'Delete Transaction?',
        text: 'Are you sure you want to delete this transaction record? This action will permanently update bank balances and cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete It!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'rounded-2xl border border-slate-200 shadow-2xl p-6',
            title: 'text-slate-900 font-bold text-lg',
            confirmButton: 'px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm rounded-xl shadow-md transition-all focus:outline-none mx-1',
            cancelButton: 'px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition-all focus:outline-none mx-1'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('deleteTransactionForm').submit();
        }
    });
}

</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
