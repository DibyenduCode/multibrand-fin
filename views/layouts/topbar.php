<?php
require_once __DIR__ . '/../../app/Models/Brand.php';
require_once __DIR__ . '/../../app/Models/FixedExpense.php';

$allGroupBrands = Brand::all(true);

$currentBrandId = $_GET['brand_id'] ?? null;

$feNotifications = [
    'total_pending_count' => 0,
    'overdue_count' => 0,
    'due_today_count' => 0,
    'due_soon_count' => 0,
    'total_pending_amount' => 0.0,
    'items' => []
];

if (!Auth::isSuperAdmin()) {
    $feNotifications = FixedExpense::getNotificationsForUser(Auth::id());
}
?>
<!-- Top Header Bar -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-30 flex items-center justify-between px-3 sm:px-6 h-16 shadow-xs">
    <div class="flex items-center gap-3.5 min-w-0">
        <!-- Sidebar Toggle Mobile Button -->
        <button onclick="toggleSidebar()" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-xl hover:bg-slate-100 active:scale-95 transition-all">
            <i class="fa-solid fa-bars-staggered text-lg"></i>
        </button>
        
        <h1 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight truncate">
            <?= e($pageTitle ?? 'Financial Overview') ?>
        </h1>
    </div>

    <div class="flex items-center gap-3 sm:gap-4">
        <!-- Manager Read Only Badge -->
        <?php if (Auth::isManager()): ?>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-300 shadow-xs">
                <i class="fa-solid fa-eye text-amber-600"></i>
                <span>READ ONLY ACCESS</span>
            </div>
        <?php endif; ?>

        <!-- Brand Switcher / Badge -->
        <?php if (Auth::isSuperAdmin()): ?>
            <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-xl border border-slate-200 shadow-xs">
                <i class="fa-solid fa-building text-slate-400 pl-2 text-xs"></i>
                <select onchange="if(this.value){ window.location.href = this.value; }" class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none cursor-pointer pr-2">
                    <option value="<?= BASE_URL ?>/dashboard" <?= empty($currentBrandId) ? 'selected' : '' ?>>
                        🌐 All Brands (Group View)
                    </option>
                    <?php foreach ($allGroupBrands as $gb): ?>
                        <option value="<?= BASE_URL ?>/dashboard?brand_id=<?= $gb['id'] ?>" <?= (string)$currentBrandId === (string)$gb['id'] ? 'selected' : '' ?>>
                            🏢 <?= e($gb['brand_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php elseif (Auth::isBrandUser()): ?>
            <?php $userBrands = User::getUserBrands(Auth::id()); ?>
            <?php if (count($userBrands) > 1): ?>
                <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-xl border border-slate-200 shadow-xs">
                    <i class="fa-solid fa-building text-slate-400 pl-2 text-xs"></i>
                    <select onchange="if(this.value){ window.location.href = this.value; }" class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none cursor-pointer pr-2">
                        <?php foreach ($userBrands as $ub): ?>
                            <option value="<?= BASE_URL ?>/dashboard?brand_id=<?= $ub['id'] ?>" <?= (string)$currentBrandId === (string)$ub['id'] ? 'selected' : '' ?>>
                                🏢 <?= e($ub['brand_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php elseif (count($userBrands) === 1): ?>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-800 shadow-xs">
                    <i class="fa-solid fa-building text-sky-600 text-xs"></i>
                    <span><?= e($userBrands[0]['brand_name']) ?></span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- FIXED EXPENSE NOTIFICATION BELL (BRAND MANAGERS ONLY - NOT SUPER ADMIN) -->
        <?php if (!Auth::isSuperAdmin()): ?>
            <div class="relative" id="notification-dropdown-container">
                <button onclick="toggleNotificationDropdown()" class="relative p-2 text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none" title="Fixed Expense Notifications">
                    <i class="fa-solid fa-bell text-lg"></i>
                    <?php if ($feNotifications['total_pending_count'] > 0): ?>
                        <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-extrabold shadow-xs <?= $feNotifications['overdue_count'] > 0 ? 'bg-rose-600 text-white animate-pulse' : ($feNotifications['due_today_count'] > 0 ? 'bg-amber-500 text-white' : 'bg-sky-500 text-white') ?>">
                            <?= $feNotifications['total_pending_count'] > 99 ? '99+' : $feNotifications['total_pending_count'] ?>
                        </span>
                    <?php endif; ?>
                </button>

                <!-- Notification Dropdown Modal -->
                <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 overflow-hidden transform transition-all duration-150">
                    <!-- Dropdown Header -->
                    <div class="p-4 bg-slate-900 text-white flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check text-rose-400 text-base"></i>
                            <h3 class="text-sm font-bold">Fixed Expense Reminders</h3>
                        </div>
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-white/10 text-slate-200">
                            <?= $feNotifications['total_pending_count'] ?> Pending
                        </span>
                    </div>

                    <!-- Summary Status Pills -->
                    <?php if ($feNotifications['total_pending_count'] > 0): ?>
                        <div class="px-4 py-2 bg-slate-50 border-b border-slate-100 flex items-center justify-between text-[11px] font-semibold text-slate-600">
                            <span>Overdue: <strong class="text-rose-600"><?= $feNotifications['overdue_count'] ?></strong></span>
                            <span>Due Today: <strong class="text-amber-600"><?= $feNotifications['due_today_count'] ?></strong></span>
                            <span>Due Soon: <strong class="text-sky-600"><?= $feNotifications['due_soon_count'] ?></strong></span>
                            <span class="font-bold text-slate-900"><?= Format::currency($feNotifications['total_pending_amount']) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Notification Item List -->
                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
                        <?php if (empty($feNotifications['items'])): ?>
                            <div class="p-6 text-center text-slate-500 space-y-2">
                                <i class="fa-solid fa-circle-check text-emerald-500 text-3xl block"></i>
                                <p class="text-xs font-bold text-slate-700">All fixed expenses paid!</p>
                                <p class="text-[11px] text-slate-400">There are no pending recurring commitments for your brand(s) this month.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($feNotifications['items'] as $item): ?>
                                <div class="p-3.5 hover:bg-slate-50 transition-colors flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-bold text-slate-900 truncate"><?= e($item['title']) ?></span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold truncate"><?= e($item['brand_name']) ?></span>
                                        </div>

                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Due: <?= (int)$item['due_day'] ?><?= date('S', mktime(0,0,0,1,(int)$item['due_day'])) ?> of month &bull; <?= e($item['category']) ?>
                                        </p>

                                        <div class="mt-1.5 flex items-center gap-2">
                                            <?php if ($item['notification_status'] === 'overdue'): ?>
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-800">
                                                    <i class="fa-solid fa-triangle-exclamation text-[9px]"></i> <?= e($item['notification_text']) ?>
                                                </span>
                                            <?php elseif ($item['notification_status'] === 'due_today'): ?>
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800">
                                                    <i class="fa-solid fa-clock text-[9px]"></i> <?= e($item['notification_text']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded bg-sky-100 text-sky-800">
                                                    <i class="fa-solid fa-calendar text-[9px]"></i> <?= e($item['notification_text']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="text-right flex-shrink-0">
                                        <span class="text-xs font-extrabold text-rose-600 block"><?= Format::currency($item['amount']) ?></span>
                                        <a href="<?= BASE_URL ?>/fixed-expenses?brand_id=<?= $item['brand_id'] ?>" class="mt-1.5 inline-block text-[11px] font-bold text-sky-600 hover:text-sky-700 hover:underline">
                                            Pay Expense &rarr;
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Dropdown Footer -->
                    <div class="p-3 bg-slate-50 border-t border-slate-100 text-center">
                        <a href="<?= BASE_URL ?>/fixed-expenses" class="text-xs font-bold text-slate-700 hover:text-rose-600 transition-colors">
                            View All Fixed Monthly Expenses &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <script>
            function toggleNotificationDropdown() {
                const dropdown = document.getElementById('notification-dropdown');
                if (dropdown) {
                    dropdown.classList.toggle('hidden');
                }
            }

            document.addEventListener('click', function(event) {
                const container = document.getElementById('notification-dropdown-container');
                const dropdown = document.getElementById('notification-dropdown');
                if (container && dropdown && !container.contains(event.target)) {
                    dropdown.classList.add('hidden');
                }
            });
            </script>
        <?php endif; ?>

        <!-- My Profile Button -->
        <a href="<?= BASE_URL ?>/profile" class="flex items-center gap-2 p-1.5 px-3 rounded-xl bg-slate-50 hover:bg-purple-50 border border-slate-200 hover:border-purple-200 text-xs font-bold text-slate-700 hover:text-purple-700 transition-colors shadow-xs" title="My Profile">
            <i class="fa-solid fa-circle-user text-purple-600 text-sm"></i>
            <span class="hidden sm:inline"><?= e(Auth::user()['name'] ?? 'Profile') ?></span>
        </a>
    </div>
</header>

