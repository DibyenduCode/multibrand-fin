<?php
$pageTitle = 'Brands Directory & Management';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Group Brands Directory</h2>
                <p class="text-xs text-slate-500">List of all active brands owned and operated under the parent business group.</p>
            </div>

            <?php if (Auth::isSuperAdmin()): ?>
                <a href="<?= BASE_URL ?>/brands/create" class="inline-flex items-center gap-2 px-4 py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow-xs transition-colors">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add New Brand</span>
                </a>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($brands as $b): ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between hover:shadow-md transition-all">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <?= Format::brandLogo($b, 'w-14 h-14', 'text-xl') ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $b['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' ?>">
                                <?= ucfirst(e($b['status'])) ?>
                            </span>
                        </div>

                        <h3 class="text-lg font-bold text-slate-900"><?= e($b['brand_name']) ?></h3>
                        <p class="text-xs text-slate-500 font-medium"><?= e($b['company_name']) ?></p>

                        <div class="mt-4 space-y-1.5 text-xs text-slate-600">
                            <div><i class="fa-solid fa-envelope w-4 text-slate-400"></i> <?= e($b['email'] ?: 'N/A') ?></div>
                            <div><i class="fa-solid fa-phone w-4 text-slate-400"></i> <?= e($b['phone'] ?: 'N/A') ?></div>
                            <div><i class="fa-solid fa-location-dot w-4 text-slate-400"></i> <?= e($b['address'] ?: 'N/A') ?></div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                        <a href="<?= BASE_URL ?>/dashboard?brand_id=<?= $b['id'] ?>" class="text-xs font-bold text-sky-600 hover:text-sky-700">
                            View Dashboard &rarr;
                        </a>

                        <?php if (Auth::canModifyBrandData((int)$b['id'])): ?>
                            <a href="<?= BASE_URL ?>/brands/<?= $b['id'] ?>/edit" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">
                                Edit Profile &amp; Logo
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
