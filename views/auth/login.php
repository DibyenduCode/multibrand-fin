<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Biswas Company Multi-Brand System</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ['Inter', 'sans-serif'],
            }
          }
        }
      }
    </script>
    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="h-full font-sans antialiased text-slate-100 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 flex items-center justify-center p-4">

    <div class="w-full max-w-md space-y-6">
        <!-- GROUP LOGO & HEADER -->
        <div class="text-center space-y-3">
            <div class="bg-white p-4 rounded-2xl inline-block shadow-xl border border-slate-200/20 max-w-[220px] mx-auto">
                <img src="<?= BASE_URL ?>/uploads/group_logo.png" alt="BISWAS COMPANY" class="h-12 w-auto mx-auto object-contain">
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">BISWAS COMPANY</h1>
                <p class="text-xs text-sky-400 font-semibold tracking-wider uppercase mt-1">Multi-Brand Finance Hub</p>
            </div>
        </div>

        <!-- ALERTS -->
        <?php if ($msg = Flash::get('error')): ?>
            <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-xl text-sm font-semibold flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-lg text-rose-400"></i>
                <span><?= e($msg) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($msg = Flash::get('success')): ?>
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-xl text-sm font-semibold flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-lg text-emerald-400"></i>
                <span><?= e($msg) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($msg = Flash::get('info')): ?>
            <div class="p-4 bg-sky-500/10 border border-sky-500/30 text-sky-300 rounded-xl text-sm font-semibold flex items-center gap-3">
                <i class="fa-solid fa-circle-info text-lg text-sky-400"></i>
                <span><?= e($msg) ?></span>
            </div>
        <?php endif; ?>

        <!-- LOGIN FORM CARD -->
        <div class="bg-slate-800/80 backdrop-blur-md rounded-2xl border border-slate-700/80 shadow-2xl p-6 sm:p-8">
            <form action="<?= BASE_URL ?>/login" method="POST" class="space-y-5">
                <?= Security::csrfField() ?>

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                            <i class="fa-solid fa-envelope"></i>
                        </div>
                        <input type="email" id="email" name="email" required placeholder="name@group.com"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none placeholder:text-slate-500">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" id="password" name="password" required placeholder="••••••••"
                               class="w-full pl-10 pr-11 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none placeholder:text-slate-500">
                        
                        <!-- TOGGLE VIEW PASSWORD BUTTON -->
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-sky-400 transition-colors" title="Toggle View Password">
                            <i id="passwordEyeIcon" class="fa-solid fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- REMEMBER ME FOR 7 DAYS OPTION -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input type="checkbox" name="remember_me" value="1" class="w-4 h-4 rounded border-slate-700 bg-slate-900 text-sky-500 focus:ring-sky-500">
                        <span class="text-xs text-slate-300 font-medium">Remember me for 7 days</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm rounded-xl shadow-lg shadow-sky-600/30 transition-all">
                    LOG IN
                </button>
            </form>
        </div>

        <div class="text-center text-xs text-slate-500">
            &copy; <?= date('Y') ?> Biswas Company. All Rights Reserved.
        </div>
    </div>

    <script>
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
</body>
</html>
