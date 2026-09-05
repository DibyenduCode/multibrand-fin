<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> - <?= APP_NAME ?></title>
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
            },
            colors: {
              brand: {
                50: '#f0f7ff',
                100: '#e0effe',
                500: '#0284c7',
                600: '#0369a1',
                700: '#075985',
                900: '#0c4a6e',
              }
            }
          }
        }
      }
    </script>
    <!-- FontAwesome 6 CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      [x-cloak] { display: none !important; }
      .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
      }
      .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
      }
      .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
      }
      .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
      }

      /* Custom SweetAlert2 Theme Styling */
      div.swal2-popup {
        font-family: 'Inter', sans-serif !important;
        border-radius: 1rem !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1) !important;
      }
      div.swal2-title {
        font-size: 1.125rem !important;
        font-weight: 700 !important;
        color: #0f172a !important;
      }
      div.swal2-html-container {
        font-size: 0.875rem !important;
        color: #475569 !important;
      }
    </style>
    <script>
      // Global SweetAlert2 Theme Mixin
      const SwalTheme = Swal.mixin({
        customClass: {
          popup: 'rounded-2xl border border-slate-200 shadow-2xl p-6',
          title: 'text-slate-900 font-bold text-lg',
          confirmButton: 'px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-bold text-sm rounded-xl shadow-md transition-all focus:outline-none mx-1',
          cancelButton: 'px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition-all focus:outline-none mx-1',
          denyButton: 'px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm rounded-xl shadow-md transition-all focus:outline-none mx-1'
        },
        buttonsStyling: false
      });

      // Global SweetAlert Toast Mixin
      const SwalToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        customClass: {
          popup: 'rounded-xl border border-slate-200 shadow-lg font-sans text-sm font-semibold'
        },
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer);
          toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
      });

      // Helper function to handle delete form confirmations with SweetAlert
      function confirmDeleteForm(event, form, title = 'Are you sure?', text = 'This action cannot be undone.') {
        event.preventDefault();
        SwalTheme.fire({
          title: title,
          text: text,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, Delete It!',
          cancelButtonText: 'Cancel',
          customClass: {
            popup: 'rounded-2xl border border-slate-200 shadow-2xl p-6',
            title: 'text-slate-900 font-bold text-lg',
            confirmButton: 'px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-sm rounded-xl shadow-md transition-all focus:outline-none mx-1',
            cancelButton: 'px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition-all focus:outline-none mx-1'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
        return false;
      }
    </script>
</head>

<body class="min-h-screen font-sans antialiased text-slate-900 bg-slate-50 selection:bg-sky-500 selection:text-white">
<div class="min-h-screen flex flex-col md:flex-row">
