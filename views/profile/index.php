<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';

$userBrands = $userObj['managed_brands'] ?? [];
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-3xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <!-- PROFILE HEADER CARD -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-purple-950 rounded-2xl p-6 text-white shadow-lg border border-slate-800 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl font-black shadow-md border-2 border-purple-400">
                    <?= substr(e($userObj['name']), 0, 2) ?>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white tracking-tight"><?= e($userObj['name']) ?></h2>
                    <p class="text-xs text-slate-300 flex items-center gap-2 mt-0.5">
                        <i class="fa-solid fa-envelope text-purple-400"></i>
                        <span><?= e($userObj['email']) ?></span>
                    </p>
                </div>
            </div>
            <div>
                <?php if (Auth::isSuperAdmin()): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">Super Admin</span>
                <?php elseif (Auth::isManager()): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">Manager</span>
                <?php else: ?>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Brand User</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- PROFILE EDIT FORM CARD -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-6">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Account Profile &amp; Credentials</h3>
                    <p class="text-xs text-slate-500">Update your full name, email address, or security password.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/profile/update" method="POST" class="space-y-6">
                <?= Security::csrfField() ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Full Name *</label>
                        <input type="text" id="name" name="name" value="<?= e($userObj['name']) ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Email Address *</label>
                        <input type="email" id="email" name="email" value="<?= e($userObj['email']) ?>" required
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                </div>

                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-4">
                    <div class="flex items-center gap-2 text-slate-800 text-xs font-bold uppercase tracking-wider">
                        <i class="fa-solid fa-lock text-purple-600"></i>
                        <span>Change Password (Optional)</span>
                    </div>
                    <p class="text-xs text-slate-500">Leave both fields blank if you do not wish to change your current password.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-1">
                        <div>
                            <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">New Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" placeholder="••••••••"
                                       class="w-full pl-4 pr-11 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                <button type="button" onclick="toggleVisibility('password', 'passwordEye')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-purple-600 transition-colors" title="Toggle Password Visibility">
                                    <i id="passwordEye" class="fa-solid fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="confirm_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••"
                                       class="w-full pl-4 pr-11 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                                <button type="button" onclick="toggleVisibility('confirm_password', 'confirmEye')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-purple-600 transition-colors" title="Toggle Password Visibility">
                                    <i id="confirmEye" class="fa-solid fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- READ ONLY SYSTEM ROLE & BRAND ASSIGNMENTS -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Assigned System Role &amp; Brand Access</label>
                        <span class="text-[11px] text-slate-400 italic">Read Only</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">System Role</span>
                            <div class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm font-bold text-slate-800">
                                <?php if ($userObj['role'] === 'super_admin'): ?>
                                    <span class="text-purple-600"><i class="fa-solid fa-shield-halved mr-1.5"></i> Super Admin (Full Access)</span>
                                <?php elseif ($userObj['role'] === 'manager'): ?>
                                    <span class="text-amber-600"><i class="fa-solid fa-eye mr-1.5"></i> Manager (Read-Only)</span>
                                <?php else: ?>
                                    <span class="text-emerald-600"><i class="fa-solid fa-building-user mr-1.5"></i> Brand User</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block mb-1">Managed Brands</span>
                            <div class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm">
                                <?php if ($userObj['role'] === 'super_admin' || $userObj['role'] === 'manager'): ?>
                                    <span class="text-xs font-semibold text-slate-600 italic">All Group Brands Authorized</span>
                                <?php elseif (!empty($userBrands)): ?>
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($userBrands as $ub): ?>
                                            <span class="px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 text-[11px] font-semibold">
                                                <?= e($ub['brand_name']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">None Assigned</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500 flex items-center gap-1.5 pt-1">
                        <i class="fa-solid fa-circle-info text-sky-500"></i>
                        <span>System role and assigned brand permissions can only be altered by a Super Admin.</span>
                    </p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <a href="<?= BASE_URL ?>/dashboard" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-sm shadow-md transition-all">
                        SAVE PROFILE CHANGES
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
function toggleVisibility(inputId, eyeId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    if (input.type === 'password') {
        input.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
