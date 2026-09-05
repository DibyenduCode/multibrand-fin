<?php
$pageTitle = 'Loan Details - ' . e($loan['loan_number']);
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-5xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <!-- HEADER & ACTIONS -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
            <div>
                <div class="flex items-center gap-3">
                    <span class="font-mono font-extrabold text-2xl text-slate-900"><?= e($loan['loan_number']) ?></span>
                    <?php if ($loan['status'] === 'paid'): ?>
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800">Paid in Full</span>
                    <?php elseif ($loan['status'] === 'partially_repaid'): ?>
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800">Partially Repaid</span>
                    <?php else: ?>
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-sky-100 text-sky-800">Active</span>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-slate-500 mt-1">Inter-Business Financial Liquidity Support</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="<?= BASE_URL ?>/loans" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i> All Loans
                </a>
                <?php if (!Auth::isManager() && (float)$loan['remaining_amount'] > 0): ?>
                    <a href="<?= BASE_URL ?>/repayments/create?loan_id=<?= $loan['id'] ?>" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-md transition-colors">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Record Repayment
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- MAIN STATS CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">ORIGINAL LOAN AMOUNT</span>
                <div class="text-2xl font-extrabold text-slate-900 mt-1"><?= Format::currency($loan['original_amount']) ?></div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">TOTAL REPAID SO FAR</span>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1"><?= Format::currency($loan['total_repaid']) ?></div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">OUTSTANDING LIABILITY</span>
                <div class="text-2xl font-extrabold text-amber-600 mt-1"><?= Format::currency($loan['remaining_amount']) ?></div>
            </div>
        </div>

        <!-- DETAILED LOAN INFORMATION GRID -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8 space-y-6">
            <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Loan Parameters &amp; Details</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div class="space-y-3">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Lender Brand:</span>
                        <span class="font-bold text-slate-900"><?= e($loan['lender_brand_name']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Lender Bank Account:</span>
                        <span class="font-semibold text-slate-700"><?= e($loan['lender_bank_name']) ?> (<?= Security::maskAccountNumber($loan['lender_account_number']) ?>)</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Borrower Brand:</span>
                        <span class="font-bold text-slate-900"><?= e($loan['borrower_brand_name']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Borrower Bank Account:</span>
                        <span class="font-semibold text-slate-700"><?= e($loan['borrower_bank_name']) ?> (<?= Security::maskAccountNumber($loan['borrower_account_number']) ?>)</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Loan Date:</span>
                        <span class="font-bold text-slate-900"><?= Format::date($loan['loan_date']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Due Date:</span>
                        <span class="font-semibold text-slate-700"><?= Format::date($loan['due_date']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Purpose:</span>
                        <span class="font-bold text-sky-600"><?= e($loan['purpose']) ?></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-50">
                        <span class="text-slate-500 font-medium">Created By:</span>
                        <span class="font-semibold text-slate-700"><?= e($loan['created_by_name'] ?? 'System') ?></span>
                    </div>
                </div>
            </div>

            <?php if ($loan['description']): ?>
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Description</span>
                    <p class="text-xs text-slate-700 leading-relaxed"><?= e($loan['description']) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- REPAYMENT HISTORY TABLE -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <h3 class="text-base font-bold text-slate-900 mb-4">Repayment History</h3>

            <?php if (empty($repayments)): ?>
                <div class="text-center py-8 text-slate-400 text-sm">No repayments recorded for this loan yet.</div>
            <?php else: ?>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left text-sm min-w-[700px]">
                        <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Repayment Ref</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3">From Bank</th>
                                <th class="px-4 py-3">To Bank</th>
                                <th class="px-4 py-3">Payment Reference</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($repayments as $r): ?>
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 font-medium text-slate-600"><?= Format::date($r['payment_date']) ?></td>
                                    <td class="px-4 py-3 font-mono font-bold text-slate-900"><?= e($r['repayment_number']) ?></td>
                                    <td class="px-4 py-3 text-right font-extrabold text-emerald-600"><?= Format::currency($r['amount']) ?></td>
                                    <td class="px-4 py-3 text-xs text-slate-600"><?= e($r['from_bank_name']) ?></td>
                                    <td class="px-4 py-3 text-xs text-slate-600"><?= e($r['to_bank_name']) ?></td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-500"><?= e($r['payment_reference'] ?: 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- LOAN ACTIVITY TIMELINE -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <h3 class="text-base font-bold text-slate-900 mb-6">Loan Event Timeline</h3>

            <ol class="relative border-l border-slate-200 ml-4 space-y-6">
                <!-- Timeline Event 1: Loan Created -->
                <li class="pl-6 relative">
                    <span class="absolute -left-3.5 top-0.5 w-7 h-7 rounded-full bg-amber-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                        <i class="fa-solid fa-handshake"></i>
                    </span>
                    <h4 class="text-sm font-bold text-slate-900">LOAN CREATED &amp; APPROVED</h4>
                    <span class="text-xs text-slate-400 block mb-1"><?= Format::date($loan['loan_date']) ?></span>
                    <p class="text-xs text-slate-600">
                        <?= e($loan['lender_brand_name']) ?> provided <?= Format::currency($loan['original_amount']) ?> internal loan to <?= e($loan['borrower_brand_name']) ?>.
                    </p>
                </li>

                <!-- Timeline Event 2: Repayments -->
                <?php foreach ($repayments as $idx => $r): ?>
                    <li class="pl-6 relative">
                        <span class="absolute -left-3.5 top-0.5 w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                            <i class="fa-solid fa-rotate-left"></i>
                        </span>
                        <h4 class="text-sm font-bold text-slate-900">REPAYMENT #<?= $idx + 1 ?> RECORDED</h4>
                        <span class="text-xs text-slate-400 block mb-1"><?= Format::date($r['payment_date']) ?></span>
                        <p class="text-xs text-slate-600">
                            <?= e($loan['borrower_brand_name']) ?> repaid <?= Format::currency($r['amount']) ?> to <?= e($loan['lender_brand_name']) ?>. (Ref: <?= e($r['repayment_number']) ?>)
                        </p>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
