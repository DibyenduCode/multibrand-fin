<?php
$pageTitle = 'Edit Brand - ' . e($brand['brand_name']);
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
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Edit Brand Profile &amp; Logo</h2>
                    <p class="text-xs text-slate-500">Managed by Super Admin &amp; Company Manager.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/brands/<?= $brand['id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-5">
                <?= Security::csrfField() ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="brand_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Brand Name *</label>
                        <?php if (Auth::isSuperAdmin()): ?>
                            <input type="text" id="brand_name" name="brand_name" value="<?= e($brand['brand_name']) ?>" required
                                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <?php else: ?>
                            <input type="text" id="brand_name" name="brand_name" value="<?= e($brand['brand_name']) ?>" readonly
                                   class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 text-sm cursor-not-allowed">
                            <span class="text-[11px] text-amber-700 font-semibold block mt-1"><i class="fa-solid fa-lock text-xs mr-1"></i> Editable by Super Admin only</span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="company_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Registered Company Name *</label>
                        <?php if (Auth::isSuperAdmin()): ?>
                            <input type="text" id="company_name" name="company_name" value="<?= e($brand['company_name']) ?>" required
                                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <?php else: ?>
                            <input type="text" id="company_name" name="company_name" value="<?= e($brand['company_name']) ?>" readonly
                                   class="w-full px-4 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-slate-600 text-sm cursor-not-allowed">
                            <span class="text-[11px] text-amber-700 font-semibold block mt-1"><i class="fa-solid fa-lock text-xs mr-1"></i> Editable by Super Admin only</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- BRAND LOGO UPLOAD & PREVIEW SECTION -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">Brand Logo Image</label>
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <?= Format::brandLogo($brand, 'w-16 h-16', 'text-xl') ?>
                        </div>
                        <div class="flex-1">
                            <input type="file" id="logo" name="logo" accept="image/*"
                                   class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-700 text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                            <span class="text-[11px] text-slate-400 mt-1 block">Upload new image logo to replace current avatar. Max 5MB (PNG, JPG, WEBP, SVG).</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Official Email</label>
                        <input type="email" id="email" name="email" value="<?= e($brand['email'] ?? '') ?>"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Official Phone</label>
                        <input type="text" id="phone" name="phone" value="<?= e($brand['phone'] ?? '') ?>"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Office Address</label>
                    <textarea id="address" name="address" rows="2"
                              class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"><?= e($brand['address'] ?? '') ?></textarea>
                </div>

                <?php if (Auth::isSuperAdmin()): ?>
                    <div>
                        <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Operating Status</label>
                        <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <option value="active" <?= $brand['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $brand['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <a href="<?= BASE_URL ?>/brands" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm shadow-md transition-all">
                        UPDATE BRAND PROFILE &amp; LOGO
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
