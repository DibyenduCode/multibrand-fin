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
        <!-- SMTP SETTINGS CARD (SUPER ADMIN ONLY) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-6 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-400 border border-sky-500/30 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">SMTP Mail Configuration</h3>
                        <p class="text-xs text-slate-400">Configure email server credentials to dispatch notifications to brand admins</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <?php if (!empty($smtpSettings['enabled']) && $smtpSettings['enabled'] === '1' && !empty($smtpSettings['host'])): ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> SMTP Active
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span> Disabled / Incomplete
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/settings/smtp" method="POST" class="p-6 space-y-6">
                <?= Security::csrfField() ?>

                <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div>
                        <span class="text-sm font-bold text-slate-900 block">Enable SMTP Email Service</span>
                        <p class="text-xs text-slate-500">Turn on/off automated email notifications across the system</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="smtp_enabled" value="1" <?= (!empty($smtpSettings['enabled']) && $smtpSettings['enabled'] === '1') ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-600"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <!-- SMTP Host -->
                    <div>
                        <label for="smtp_host" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            SMTP Host *
                        </label>
                        <input type="text" id="smtp_host" name="smtp_host" value="<?= e($smtpSettings['host'] ?? '') ?>" placeholder="e.g. smtp.gmail.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                    </div>

                    <!-- SMTP Port -->
                    <div>
                        <label for="smtp_port" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            SMTP Port *
                        </label>
                        <input type="number" id="smtp_port" name="smtp_port" value="<?= e($smtpSettings['port'] ?? '587') ?>" placeholder="587 / 465 / 25" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                    </div>

                    <!-- Encryption -->
                    <div>
                        <label for="smtp_encryption" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Encryption Protocol *
                        </label>
                        <select id="smtp_encryption" name="smtp_encryption" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all bg-white">
                            <option value="tls" <?= ($smtpSettings['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (STARTTLS - Port 587)</option>
                            <option value="ssl" <?= ($smtpSettings['encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL (Port 465)</option>
                            <option value="none" <?= ($smtpSettings['encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None (Plain Text - Port 25)</option>
                        </select>
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="smtp_username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            SMTP Username *
                        </label>
                        <input type="text" id="smtp_username" name="smtp_username" value="<?= e($smtpSettings['username'] ?? '') ?>" placeholder="user@example.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="smtp_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            SMTP Password *
                        </label>
                        <input type="password" id="smtp_password" name="smtp_password" value="<?= e($smtpSettings['password'] ?? '') ?>" placeholder="••••••••••••" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                    </div>

                    <!-- Sender Name -->
                    <div>
                        <label for="smtp_from_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Sender Display Name
                        </label>
                        <input type="text" id="smtp_from_name" name="smtp_from_name" value="<?= e($smtpSettings['from_name'] ?? 'Fin App Notifications') ?>" placeholder="e.g. Fin Group Notifications" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                    </div>

                    <!-- Sender Email -->
                    <div class="sm:col-span-2">
                        <label for="smtp_from_email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Sender Email Address (From Email)
                        </label>
                        <input type="email" id="smtp_from_email" name="smtp_from_email" value="<?= e($smtpSettings['from_email'] ?? '') ?>" placeholder="notifications@yourdomain.com (Defaults to Username if empty)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none transition-all">
                    </div>
                </div>

                <!-- SAVE SMTP ACTION -->
                <div class="pt-6 border-t border-slate-100 flex items-center justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm shadow-md transition-all">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Save SMTP Credentials</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- TEST SMTP CONNECTION CARD (SUPER ADMIN ONLY) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-6 bg-slate-100 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/20 text-purple-600 border border-purple-500/30 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-vial"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Test SMTP Connection</h3>
                        <p class="text-xs text-slate-500">Send an immediate test email to verify configured credentials</p>
                    </div>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/settings/smtp-test" method="POST" class="p-6 flex flex-col sm:flex-row items-end gap-4">
                <?= Security::csrfField() ?>

                <div class="flex-1 w-full">
                    <label for="test_email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                        Test Recipient Email Address
                    </label>
                    <input type="email" id="test_email" name="test_email" value="<?= e(Auth::user()['email'] ?? '') ?>" placeholder="admin@example.com" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all">
                </div>

                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-sm shadow-md transition-all">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Send Test Email</span>
                </button>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

