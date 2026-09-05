<?php
$pageTitle = 'Inter-Business Loans';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Inter-Business &amp; Inter-Brand Loans</h2>
                <p class="text-xs text-slate-500">Internal financial liquidity support transfers between sister brands inside the group.</p>
            </div>

            <?php if (!Auth::isManager()): ?>
                <a href="<?= BASE_URL ?>/loans/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                    <i class="fa-solid fa-handshake"></i>
                    <span>Record Inter-Brand Loan</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-sm min-w-[850px]">
                    <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3.5">Loan Number</th>
                            <th class="px-5 py-3.5">Lender</th>
                            <th class="px-5 py-3.5">Borrower</th>
                            <th class="px-5 py-3.5 text-right">Original Amount</th>
                            <th class="px-5 py-3.5 text-right">Repaid</th>
                            <th class="px-5 py-3.5 text-right">Remaining</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($loans)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-handshake text-3xl mb-2 block"></i>
                                    No inter-brand loans recorded yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($loans as $l): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-5 py-4 font-mono font-bold text-slate-900">
                                        <?= e($l['loan_number']) ?>
                                        <span class="block text-[11px] text-slate-400 font-sans font-normal"><?= Format::date($l['loan_date']) ?></span>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-slate-800">
                                        <?= e($l['lender_brand_name']) ?>
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-slate-800">
                                        <?= e($l['borrower_brand_name']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-bold text-slate-900">
                                        <?= Format::currency($l['original_amount']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-emerald-600">
                                        <?= Format::currency($l['total_repaid']) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-extrabold text-amber-600">
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
                                            <span>View Details</span>
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
