<?php
$currentRoute = $_SERVER['REQUEST_URI'] ?? '';
function isActive($path, $currentRoute) {
    return (strpos($currentRoute, $path) !== false) ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white';
}

$sidebarBrandLabel = '';
if (Auth::isBrandUser()) {
    $activeBrandId = $_GET['brand_id'] ?? null;
    if ($activeBrandId) {
        $activeBrandObj = Brand::find((int)$activeBrandId);
        if ($activeBrandObj) {
            $sidebarBrandLabel = $activeBrandObj['brand_name'];
        }
    }
    if (empty($sidebarBrandLabel)) {
        $userBrands = User::getUserBrands(Auth::id());
        if (count($userBrands) === 1) {
            $sidebarBrandLabel = $userBrands[0]['brand_name'];
        } elseif (count($userBrands) > 1) {
            $sidebarBrandLabel = $userBrands[0]['brand_name'];
        } else {
            $sidebarBrandLabel = Auth::user()['brand_name'] ?? 'Brand User';
        }
    }
}
?>
<!-- Sidebar Overlay for Mobile -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 hidden md:hidden transition-opacity duration-300" onclick="toggleSidebar()"></div>

<!-- Main Navigation Sidebar (App Drawer on Mobile, Sticky Sidebar on Desktop) -->
<aside id="main-sidebar" class="w-72 sm:w-80 md:w-64 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col fixed inset-y-0 left-0 z-50 transform -translate-x-full md:translate-x-0 md:sticky md:top-0 md:h-screen transition-transform duration-300 ease-in-out border-r border-slate-800 shadow-2xl md:shadow-none">
    <!-- Brand Logo / System Header -->
    <div class="h-16 flex items-center justify-between px-5 bg-slate-950 border-b border-slate-800 flex-shrink-0">
        <a href="<?= BASE_URL ?>/dashboard" class="flex items-center gap-3">
            <div class="bg-white p-1 rounded-lg flex items-center justify-center">
                <img src="<?= BASE_URL ?>/uploads/group_logo.png" alt="BISWAS COMPANY" class="h-7 w-auto object-contain">
            </div>
            <div>
                <span class="font-extrabold text-white tracking-wide text-sm block leading-none">BISWAS CO.</span>
                <span class="text-[9px] text-sky-400 font-bold tracking-wider uppercase">Multi-Brand Hub</span>
            </div>
        </a>
        <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>

    <!-- User Role Badge -->
    <div class="px-6 py-3 bg-slate-900/80 border-b border-slate-800/80 flex items-center gap-2 flex-shrink-0">
        <?php if (Auth::isSuperAdmin()): ?>
            <span class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></span>
            <span class="text-xs font-semibold text-purple-400 tracking-wider uppercase">Super Admin</span>
        <?php elseif (Auth::isManager()): ?>
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span class="text-xs font-semibold text-amber-400 tracking-wider uppercase">Manager (Read Only)</span>
        <?php else: ?>
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-xs font-semibold text-emerald-400 tracking-wider uppercase truncate" title="<?= e($sidebarBrandLabel) ?>">
                <?= e($sidebarBrandLabel) ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
        <!-- Dashboard -->
        <a href="<?= BASE_URL ?>/dashboard" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/dashboard', $currentRoute) ?>">
            <i class="fa-solid fa-table-columns w-5 text-center text-sky-400"></i>
            <span>Dashboard</span>
        </a>

        <?php if (Auth::isSuperAdmin()): ?>
            <!-- SUPER ADMIN MENU -->
            <a href="<?= BASE_URL ?>/brands" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/brands', $currentRoute) ?>">
                <i class="fa-solid fa-building-user w-5 text-center text-emerald-400"></i>
                <span>Brands</span>
            </a>
            <a href="<?= BASE_URL ?>/users" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/users', $currentRoute) ?>">
                <i class="fa-solid fa-users-gear w-5 text-center text-purple-400"></i>
                <span>Users</span>
            </a>
            <a href="<?= BASE_URL ?>/transactions" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/transactions', $currentRoute) ?>">
                <i class="fa-solid fa-list-check w-5 text-center text-indigo-400"></i>
                <span>All Transactions</span>
            </a>
            <a href="<?= BASE_URL ?>/fixed-expenses" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/fixed-expenses', $currentRoute) ?>">
                <i class="fa-solid fa-calendar-check w-5 text-center text-rose-400"></i>
                <span>Fixed Expenses</span>
            </a>
            <a href="<?= BASE_URL ?>/bank-accounts" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/bank-accounts', $currentRoute) ?>">
                <i class="fa-solid fa-piggy-bank w-5 text-center text-teal-400"></i>
                <span>Bank Accounts</span>
            </a>
            <a href="<?= BASE_URL ?>/loans" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/loans', $currentRoute) ?>">
                <i class="fa-solid fa-hand-holding-dollar w-5 text-center text-amber-400"></i>
                <span>Inter-Brand Loans</span>
            </a>
            <a href="<?= BASE_URL ?>/liabilities" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/liabilities', $currentRoute) ?>">
                <i class="fa-solid fa-circle-arrow-down w-5 text-center text-rose-400"></i>
                <span>Liabilities</span>
            </a>
            <a href="<?= BASE_URL ?>/receivables" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/receivables', $currentRoute) ?>">
                <i class="fa-solid fa-circle-arrow-up w-5 text-center text-emerald-400"></i>
                <span>Receivables</span>
            </a>
            <a href="<?= BASE_URL ?>/settings" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/settings', $currentRoute) ?>">
                <i class="fa-solid fa-sliders w-5 text-center text-sky-400"></i>
                <span>System Settings</span>
            </a>

        <?php elseif (Auth::isBrandUser()): ?>

            <!-- BRAND USER MENU -->
            <a href="<?= BASE_URL ?>/money-in/create" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/money-in', $currentRoute) ?>">
                <i class="fa-solid fa-circle-plus w-5 text-center text-emerald-400"></i>
                <span>Money In</span>
            </a>
            <a href="<?= BASE_URL ?>/expenses/create" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/expenses', $currentRoute) ?>">
                <i class="fa-solid fa-circle-minus w-5 text-center text-rose-400"></i>
                <span>Expenses</span>
            </a>
            <a href="<?= BASE_URL ?>/fixed-expenses" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/fixed-expenses', $currentRoute) ?>">
                <i class="fa-solid fa-calendar-check w-5 text-center text-rose-400"></i>
                <span>Fixed Expenses</span>
            </a>
            <a href="<?= BASE_URL ?>/bank-accounts" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/bank-accounts', $currentRoute) ?>">
                <i class="fa-solid fa-building-columns w-5 text-center text-teal-400"></i>
                <span>Bank Accounts</span>
            </a>
            <a href="<?= BASE_URL ?>/loans" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/loans', $currentRoute) ?>">
                <i class="fa-solid fa-hand-holding-dollar w-5 text-center text-amber-400"></i>
                <span>Loans</span>
            </a>
            <a href="<?= BASE_URL ?>/liabilities" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/liabilities', $currentRoute) ?>">
                <i class="fa-solid fa-circle-arrow-down w-5 text-center text-rose-400"></i>
                <span>Liabilities</span>
            </a>
            <a href="<?= BASE_URL ?>/receivables" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/receivables', $currentRoute) ?>">
                <i class="fa-solid fa-circle-arrow-up w-5 text-center text-emerald-400"></i>
                <span>Receivables</span>
            </a>
            <a href="<?= BASE_URL ?>/transactions" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/transactions', $currentRoute) ?>">
                <i class="fa-solid fa-clock-rotate-left w-5 text-center text-indigo-400"></i>
                <span>Transaction History</span>
            </a>

        <?php elseif (Auth::isManager()): ?>
            <!-- MANAGER MENU -->
            <a href="<?= BASE_URL ?>/transactions" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/transactions', $currentRoute) ?>">
                <i class="fa-solid fa-list-check w-5 text-center text-indigo-400"></i>
                <span>All Transactions</span>
            </a>
            <a href="<?= BASE_URL ?>/fixed-expenses" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/fixed-expenses', $currentRoute) ?>">
                <i class="fa-solid fa-calendar-check w-5 text-center text-rose-400"></i>
                <span>Fixed Expenses</span>
            </a>
            <a href="<?= BASE_URL ?>/bank-accounts" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/bank-accounts', $currentRoute) ?>">
                <i class="fa-solid fa-building-columns w-5 text-center text-teal-400"></i>
                <span>Bank Accounts</span>
            </a>
            <a href="<?= BASE_URL ?>/loans" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/loans', $currentRoute) ?>">
                <i class="fa-solid fa-hand-holding-dollar w-5 text-center text-amber-400"></i>
                <span>Inter-Brand Loans</span>
            </a>
            <a href="<?= BASE_URL ?>/liabilities" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/liabilities', $currentRoute) ?>">
                <i class="fa-solid fa-circle-arrow-down w-5 text-center text-rose-400"></i>
                <span>Liabilities</span>
            </a>
            <a href="<?= BASE_URL ?>/receivables" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/receivables', $currentRoute) ?>">
                <i class="fa-solid fa-circle-arrow-up w-5 text-center text-emerald-400"></i>
                <span>Receivables</span>
            </a>
        <?php endif; ?>

        <!-- SHARED COMMON MENU -->
        <a href="<?= BASE_URL ?>/reports/daily" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/reports', $currentRoute) ?>">
            <i class="fa-solid fa-chart-pie w-5 text-center text-fuchsia-400"></i>
            <span>Reports</span>
        </a>
        <a href="<?= BASE_URL ?>/directory" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/directory', $currentRoute) ?>">
            <i class="fa-solid fa-address-book w-5 text-center text-yellow-400"></i>
            <span>Company Directory</span>
        </a>
        <a href="<?= BASE_URL ?>/profile" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors <?= isActive('/profile', $currentRoute) ?>">
            <i class="fa-solid fa-user-gear w-5 text-center text-purple-400"></i>
            <span>My Profile</span>
        </a>
    </nav>

    <!-- Sidebar Footer User Profile & Logout -->
    <div class="p-4 bg-slate-950 border-t border-slate-800 flex items-center justify-between flex-shrink-0">
        <a href="<?= BASE_URL ?>/profile" class="flex items-center gap-3 overflow-hidden group flex-1 mr-2" title="Edit Profile">
            <div class="w-8 h-8 rounded-full bg-slate-700 group-hover:bg-purple-600 text-slate-200 group-hover:text-white transition-colors flex items-center justify-center font-bold text-xs uppercase flex-shrink-0">
                <?= substr(Auth::user()['name'] ?? 'U', 0, 2) ?>
            </div>
            <div class="truncate">
                <span class="text-xs font-semibold text-white group-hover:text-purple-300 transition-colors block truncate"><?= e(Auth::user()['name'] ?? '') ?></span>
                <span class="text-[11px] text-slate-400 block truncate"><?= e(Auth::user()['email'] ?? '') ?></span>
            </div>
        </a>
        <a href="<?= BASE_URL ?>/logout" class="text-slate-400 hover:text-rose-400 p-1.5 transition-colors" title="Logout">
            <i class="fa-solid fa-right-from-bracket text-base"></i>
        </a>
    </div>
</aside>
