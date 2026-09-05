<?php
$pageTitle = 'User Management';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">User Administration</h2>
                <p class="text-xs text-slate-500">Manage super admins, brand users, managers, and multi-brand assignments.</p>
            </div>

            <a href="<?= BASE_URL ?>/users/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                <i class="fa-solid fa-user-plus"></i>
                <span>Add New User</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3.5">User</th>
                            <th class="px-6 py-3.5">Role</th>
                            <th class="px-6 py-3.5">Managed Brands</th>
                            <th class="px-6 py-3.5 text-center">Status</th>
                            <th class="px-6 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs uppercase">
                                            <?= substr(e($u['name']), 0, 2) ?>
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block"><?= e($u['name']) ?></span>
                                            <span class="text-xs text-slate-400 block"><?= e($u['email']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($u['role'] === 'super_admin'): ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800">Super Admin</span>
                                    <?php elseif ($u['role'] === 'manager'): ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Manager (Read Only)</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Brand User</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($u['role'] === 'super_admin' || $u['role'] === 'manager'): ?>
                                        <span class="text-xs font-medium text-slate-500 italic">All Group Brands</span>
                                    <?php elseif (!empty($u['managed_brands'])): ?>
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach ($u['managed_brands'] as $mb): ?>
                                                <span class="px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 text-[11px] font-semibold">
                                                    <?= e($mb['brand_name']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">None Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $u['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                                        <?= ucfirst(e($u['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="<?= BASE_URL ?>/users/<?= $u['id'] ?>/edit" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                                            Edit User
                                        </a>
                                        <?php if ((int)$u['id'] !== (int)Auth::id()): ?>
                                            <form action="<?= BASE_URL ?>/users/<?= $u['id'] ?>/delete" method="POST" onsubmit="return confirmDeleteForm(event, this, 'Delete User?', 'Are you sure you want to delete user \'<?= e(addslashes($u['name'])) ?>\'? All past financial records created by this user will be preserved.')">
                                                <?= Security::csrfField() ?>
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold transition-colors">
                                                    <i class="fa-solid fa-trash mr-1"></i> Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
