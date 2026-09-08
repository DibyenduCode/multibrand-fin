</div> <!-- End Main Content Container -->
</div> <!-- End Flex Wrapper -->

<?php
$currentRoute = $_SERVER['REQUEST_URI'] ?? '';
function isMobileActive($path, $currentRoute) {
    if ($path === '/dashboard' && (strpos($currentRoute, '/dashboard') !== false || $currentRoute === '/' || $currentRoute === '')) {
        return 'text-sky-400 font-bold';
    }
    return (strpos($currentRoute, $path) !== false) ? 'text-sky-400 font-bold' : 'text-slate-400 hover:text-slate-200';
}
?>

<!-- FIXED MOBILE BOTTOM ACTION BAR (Strictly Hidden on Desktop PC: md:hidden) -->
<div class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-slate-900/95 backdrop-blur-md border-t border-slate-800 shadow-2xl px-2 py-1.5 flex items-center justify-around">
    <!-- 1. Dashboard -->
    <a href="<?= BASE_URL ?>/dashboard" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all active:scale-90 <?= isMobileActive('/dashboard', $currentRoute) ?>">
        <i class="fa-solid fa-table-columns text-lg mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Home</span>
    </a>

    <!-- 2. Transactions -->
    <a href="<?= BASE_URL ?>/transactions" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all active:scale-90 <?= isMobileActive('/transactions', $currentRoute) ?>">
        <i class="fa-solid fa-list-check text-lg mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Transactions</span>
    </a>

    <!-- 3. Fixed Expenses -->
    <a href="<?= BASE_URL ?>/fixed-expenses" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all active:scale-90 <?= isMobileActive('/fixed-expenses', $currentRoute) ?>">
        <i class="fa-solid fa-calendar-check text-lg mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Fixed Exp.</span>
    </a>

    <!-- 4. Loans -->
    <a href="<?= BASE_URL ?>/loans" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition-all active:scale-90 <?= isMobileActive('/loans', $currentRoute) ?>">
        <i class="fa-solid fa-hand-holding-dollar text-lg mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Loans</span>
    </a>

    <!-- 5. App Menu Drawer -->
    <button type="button" onclick="toggleSidebar()" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl text-slate-400 hover:text-slate-200 transition-all active:scale-90 focus:outline-none">
        <i class="fa-solid fa-bars-staggered text-lg mb-0.5"></i>
        <span class="text-[10px] tracking-tight">Menu</span>
    </button>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('main-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    if (!sidebar || !backdrop) return;
    
    if (sidebar.classList.contains('-translate-x-full')) {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    } else {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
        document.body.style.overflow = '';
    }
}
</script>
</body>
</html>
