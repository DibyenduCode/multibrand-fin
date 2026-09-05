<?php
$pageTitle = 'Edit User - ' . e($userObj['name']);
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
$assignedIds = $userObj['managed_brand_ids'] ?? [];
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-3xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-6">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Edit User Profile &amp; Brand Permissions</h2>
                    <p class="text-xs text-slate-500">Update account credentials or assign 1 or more brands to manage.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/users/<?= $userObj['id'] ?>" method="POST" class="space-y-5">
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

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">New Password (Leave blank to keep unchanged)</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="••••••••"
                                   class="w-full pl-4 pr-11 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-purple-600 transition-colors" title="Toggle Password Visibility">
                                <i id="passwordEyeIcon" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="role" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">System Role *</label>
                        <select id="role" name="role" required onchange="toggleBrandSelector()"
                                class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            <option value="brand_user" <?= $userObj['role'] === 'brand_user' ? 'selected' : '' ?>>Brand User (Manage Assigned Brands)</option>
                            <option value="super_admin" <?= $userObj['role'] === 'super_admin' ? 'selected' : '' ?>>Super Admin (Full CRUD System-Wide)</option>
                            <option value="manager" <?= $userObj['role'] === 'manager' ? 'selected' : '' ?>>Manager (Read-Only System-Wide)</option>
                        </select>
                    </div>
                </div>

                <!-- MULTI-BRAND ASSIGNMENT SELECTOR -->
                <div id="brand_selector_container" class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2" style="<?= $userObj['role'] === 'brand_user' ? '' : 'display:none;' ?>">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Assign Managed Brands (Select 1 or More) *</label>
                    <p class="text-xs text-slate-500 mb-3">Check all brands that this user is authorized to manage.</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <?php foreach ($brands as $b): ?>
                            <?php $isChecked = in_array((int)$b['id'], $assignedIds); ?>
                            <label class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-lg hover:border-purple-300 cursor-pointer transition-colors">
                                <input type="checkbox" name="brand_ids[]" value="<?= $b['id'] ?>" <?= $isChecked ? 'checked' : '' ?> class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                                <div>
                                    <span class="text-sm font-bold text-slate-900 block"><?= e($b['brand_name']) ?></span>
                                    <span class="text-[11px] text-slate-400 block"><?= e($b['company_name']) ?></span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Account Status</label>
                    <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none">
                        <option value="active" <?= $userObj['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $userObj['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <a href="<?= BASE_URL ?>/users" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-sm shadow-md transition-all">
                        UPDATE USER
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
function toggleBrandSelector() {
    const role = document.getElementById('role').value;
    const container = document.getElementById('brand_selector_container');
    if (role === 'brand_user') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('passwordEyeIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
