<?php
$pageTitle = 'Liabilities Management';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <!-- PROMINENT TOTAL OUTSTANDING LIABILITY CARD -->
        <div class="bg-gradient-to-r from-amber-900 via-slate-900 to-amber-950 rounded-2xl p-6 sm:p-8 text-white shadow-xl border border-amber-800/40 relative overflow-hidden">
            <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-amber-500/10 skew-x-12 pointer-events-none"></div>
            
            <span class="text-xs font-semibold uppercase tracking-wider text-amber-300 block mb-1">MONEY THAT WE NEED TO PAY</span>
            <div class="text-sm font-medium text-slate-300">TOTAL OUTSTANDING LIABILITY</div>
            <div class="text-3xl sm:text-5xl font-extrabold text-amber-400 tracking-tight mt-2">
                <?= Format::currency($totalOutstanding) ?>
            </div>
            <p class="text-xs text-amber-200/80 mt-2">Sum of active inter-brand loan borrowings remaining to be repaid.</p>
        </div>

        <!-- ACTIVE LIABILITIES LIST -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-900">Active Brand Borrowings &amp; Liabilities</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Loan Ref</th>
                            <th class="px-5 py-3.5">Lender (Owed To)</th>
                            <th class="px-5 py-3.5">Borrower</th>
                            <th class="px-5 py-3.5 text-right">Original Loan</th>
                            <th class="px-5 py-3.5 text-right">Total Repaid</th>
                            <th class="px-5 py-3.5 text-right">Outstanding Balance</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($liabilities)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-circle-check text-3xl mb-2 text-emerald-500 block"></i>
                                    No outstanding liabilities currently registered.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($liabilities as $l): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                        <?= e($l['loan_number']) ?>
                                    </td>
                                    <td class="px-5 py-4 font-bold text-slate-900">
                                        <?= e($l['lender_brand_name']) ?>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-slate-700">
                                        <?= e($l['borrower_brand_name']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-slate-700">
                                        <?= Format::currency($l['original_amount']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-emerald-600">
                                        <?= Format::currency($l['total_repaid']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-extrabold text-amber-600 text-base">
                                        <?= Format::currency($l['remaining_amount']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <?php if ($l['status'] === 'paid'): ?>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Paid</span>
                                        <?php elseif ($l['status'] === 'partially_repaid'): ?>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Partially Repaid</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-sky-100 text-sky-800">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <a href="<?= BASE_URL ?>/loans/<?= $l['id'] ?>" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                                            <span>VIEW DETAILS</span>
                                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                        </a>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
