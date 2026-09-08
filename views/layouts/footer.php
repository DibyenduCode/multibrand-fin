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

// Progressive Web App (PWA) Mobile Integration Engine
let deferredPwaPrompt = null;

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('<?= BASE_URL ?>/sw.js')
            .then((reg) => {
                console.log('PWA Service Worker registered successfully:', reg.scope);
            })
            .catch((err) => {
                console.warn('PWA Service Worker registration failed:', err);
            });
    });
}

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPwaPrompt = e;

    // Show topbar install button strictly on mobile (< 768px)
    if (window.innerWidth < 768) {
        const topbarBtn = document.getElementById('pwa-topbar-install-btn');
        if (topbarBtn) {
            topbarBtn.classList.remove('hidden');
            topbarBtn.classList.add('flex');
        }
    }
});

function triggerPwaInstall() {
    if (deferredPwaPrompt) {
        deferredPwaPrompt.prompt();
        deferredPwaPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                SwalToast.fire({
                    icon: 'success',
                    title: 'App Added to Home Screen!'
                });
            }
            deferredPwaPrompt = null;
        });
    } else if (window.matchMedia('(display-mode: standalone)').matches || navigator.standalone) {
        SwalToast.fire({
            icon: 'info',
            title: 'PWA Mobile App is already installed!'
        });
    } else {
        // iOS or fallback instructions
        SwalTheme.fire({
            title: '<i class="fa-solid fa-mobile-screen text-sky-500 text-3xl mb-2 block"></i> Install Mobile App',
            html: `
                <div class="text-left space-y-3 text-slate-600 text-xs">
                    <p class="font-semibold text-slate-800">To install this app on your phone:</p>
                    <ol class="list-decimal pl-4 space-y-1.5">
                        <li>Tap the <strong class="text-slate-900"><i class="fa-solid fa-arrow-up-from-bracket text-sky-600"></i> Share</strong> button in your mobile browser.</li>
                        <li>Scroll down and tap <strong class="text-slate-900"><i class="fa-regular fa-square-plus text-sky-600"></i> Add to Home Screen</strong>.</li>
                        <li>Open the app directly from your home screen icon for full standalone app experience!</li>
                    </ol>
                </div>
            `,
            confirmButtonText: 'Got it!'
        });
    }
}
</script>
</body>
</html>
