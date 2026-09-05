<?php
$pageTitle = 'Add New Brand';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-3xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex items-center gap-3 pb-5 border-b border-slate-100 mb-6">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-building"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Create New Brand</h2>
                    <p class="text-xs text-slate-500">Register a new brand entity and upload its official logo.</p>
                </div>
            </div>

            <form action="<?= BASE_URL ?>/brands/store" method="POST" enctype="multipart/form-data" class="space-y-5">
                <?= Security::csrfField() ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="brand_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Brand Name *</label>
                        <input type="text" id="brand_name" name="brand_name" required placeholder="e.g. Zenith Foods"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="company_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Registered Company Name *</label>
                        <input type="text" id="company_name" name="company_name" required placeholder="e.g. Zenith Foods Private Limited"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="logo" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Brand Logo Image (PNG, JPG, WEBP, SVG)</label>
                    <input type="file" id="logo" name="logo" accept="image/*"
                           class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-slate-700 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                    <span class="text-[11px] text-slate-400 mt-1 block">Maximum file size: 5MB. Recommended resolution: 200x200px or SVG.</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Official Email</label>
                        <input type="email" id="email" name="email" placeholder="contact@brand.com"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Official Phone</label>
                        <input type="text" id="phone" name="phone" placeholder="+91 98765 43210"
                               class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Office Address</label>
                    <textarea id="address" name="address" rows="2" placeholder="Full registered office address..."
                              class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3">
                    <a href="<?= BASE_URL ?>/brands" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-semibold text-sm hover:bg-slate-50 transition-colors">Cancel</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-sm shadow-md transition-all">
                        CREATE BRAND
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
