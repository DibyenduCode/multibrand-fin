<?php
$pageTitle = 'Company Directory';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto space-y-6">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div>
            <h2 class="text-xl font-bold text-slate-900">Group Company Directory</h2>
            <p class="text-xs text-slate-500">Contact information, registered addresses, and corporate profiles for all brands.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($brands as $b): ?>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 flex flex-col justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
                            <?= Format::brandLogo($b, 'w-14 h-14', 'text-2xl') ?>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900"><?= e($b['brand_name']) ?></h3>
                                <p class="text-xs text-slate-500 font-medium"><?= e($b['company_name']) ?></p>
                            </div>
                        </div>

                        <div class="space-y-2.5 text-xs text-slate-700">
                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-envelope text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Email Address</span>
                                    <span class="font-medium text-slate-800"><?= e($b['email'] ?: 'Not Provided') ?></span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-phone text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Phone Number</span>
                                    <span class="font-medium text-slate-800"><?= e($b['phone'] ?: 'Not Provided') ?></span>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <i class="fa-solid fa-location-dot text-slate-400 mt-0.5 w-4 text-center"></i>
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Registered Office Address</span>
                                    <span class="font-medium text-slate-800"><?= e($b['address'] ?: 'Not Provided') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (Auth::isSuperAdmin()): ?>
                        <div class="mt-6 pt-4 border-t border-slate-100 text-right">
                            <a href="<?= BASE_URL ?>/brands/<?= $b['id'] ?>/edit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit Profile &amp; Logo</span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
