<?php
$pageTitle = 'System Settings';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-5xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <!-- HEADER -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-sky-600"></i>
                    <span>System Settings &amp; Rules</span>
                </h2>
                <p class="text-xs text-slate-500">Global system settings configurable only by Super Admin.</p>
            </div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-purple-100 border border-purple-200 text-purple-800 text-xs font-bold shadow-xs">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Super Admin Access</span>
            </div>
        </div>

        <!-- SETTINGS FORM CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-6 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Fixed Expense Notification Window</h3>
                        <p class="text-xs text-slate-400">Configure advance notice days for brand managers</p>
                    </div>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/settings" method="POST" class="p-6 space-y-6">
                <?= Security::csrfField() ?>

                <div class="space-y-4">
                    <div>
                        <label for="fixed_expense_notification_days" class="block text-sm font-bold text-slate-800 mb-1">
                            Advance Notification Lead Days *
                        </label>
                        <p class="text-xs text-slate-500 mb-3">
                            Set how many days in advance brand users will receive notification bell reminders for upcoming fixed expenses.
                            <br><span class="text-slate-400 italic">Note: Overdue and Due Today expenses are always notified immediately regardless of this setting.</span>
                        </p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                            <?php
                            $options = [
                                3 => ['label' => '3 Days in Advance', 'desc' => 'Short lead time'],
                                5 => ['label' => '5 Days in Advance', 'desc' => 'Standard lead time'],
                                7 => ['label' => '7 Days in Advance (Default)', 'desc' => '1 week prior'],
                                10 => ['label' => '10 Days in Advance', 'desc' => 'Early reminder'],
                                15 => ['label' => '15 Days in Advance', 'desc' => 'Half month lead'],
                                30 => ['label' => '30 Days in Advance', 'desc' => 'Full month lead'],
                                0 => ['label' => 'Entire Month', 'desc' => 'All pending in month'],
                            ];
                            ?>

                            <?php foreach ($options as $val => $opt): ?>
                                <label class="relative flex flex-col p-4 rounded-xl border cursor-pointer transition-all hover:bg-slate-50 <?= (int)$notificationDays === $val ? 'border-sky-500 bg-sky-50/50 ring-2 ring-sky-500/20' : 'border-slate-200' ?>">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-bold text-slate-900"><?= e($opt['label']) ?></span>
                                        <input type="radio" name="fixed_expense_notification_days" value="<?= $val ?>" <?= (int)$notificationDays === $val ? 'checked' : '' ?> class="text-sky-600 focus:ring-sky-500">
                                    </div>
                                    <span class="text-xs text-slate-500"><?= e($opt['desc']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- SAVE ACTION -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-md transition-all">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save System Settings</span>
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
