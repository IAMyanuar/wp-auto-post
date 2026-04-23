<!DOCTYPE html>
<html lang="en" class="font-outfit">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>@yield('title', 'WP Auto Post')</title>
</head>

<body class="font-outfit antialiased selection:bg-indigo-100 selection:text-indigo-900"
    style="background: radial-gradient(at 0% 0%, #ffffff, transparent), radial-gradient(at 100% 100%, #f0f4ff, transparent), hsl(var(--surface-2)); color: hsl(var(--text-base)); min-height: 100vh;">

    <div class="flex min-h-screen">

        @include('layout.partials.sidebar')

        <!-- Right Column: Navbar + Main -->
        <div class="flex flex-col flex-1 md:ml-64 min-h-screen">

            @include('layout.partials.navbar')

            <!-- Main Content -->
            <main class="flex-1 px-5 md:px-8 pt-24 md:pt-[5.5rem] pb-28 md:pb-10">
                <!-- Page Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-extrabold tracking-tight" style="color: hsl(var(--text-base));">
                        @yield('page_title', 'Dashboard')
                    </h1>
                    <p class="text-sm mt-0.5" style="color: hsl(var(--text-muted));">
                        @yield('page_subtitle', '')
                    </p>
                </div>

                <!-- Content Card -->
                <div class="w-full p-6 md:p-8 rounded-[var(--radius-card)] min-h-[60vh]"
                    style="background: hsl(var(--surface-1)); box-shadow: var(--shadow-card); border: 1px solid hsl(var(--border-light));">
                    @yield('content')
                </div>
            </main>

        </div>
    </div>

    <!-- Toast/Alert Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1000,
                timerProgressBar: true,
                customClass: {
                    popup: '!rounded-xl shadow-md border border-gray-100',
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            @if(session('success'))
                Toast.fire({
                    icon: 'success',
                    title: '{{ session("success") }}'
                });
            @endif

            @if(session('error'))
                Toast.fire({
                    icon: 'error',
                    title: '{{ session("error") }}'
                });
            @endif

            // Event delegation untuk form delete (termasuk yang di-generate JS secara dinamis)
            document.body.addEventListener('submit', function (e) {
                const form = e.target.closest('.form-delete');
                if (!form) return;
                e.preventDefault();
                Swal.fire({
                    title: 'Hapus Data?',
                    text: "Tindakan ini tidak dapat dibatalkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'hsl(0 0% 10%)',
                    cancelButtonColor: 'hsl(220 13% 70%)',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    customClass: {
                        popup: '!rounded-[1.25rem] shadow-xl p-2',
                        confirmButton: '!rounded-[0.625rem] px-5 py-2.5 font-bold',
                        cancelButton: '!rounded-[0.625rem] px-5 py-2.5 font-bold mt-2 sm:mt-0',
                    }
                }).then((result) => {
                    if (result.isConfirmed) { form.submit(); }
                });
            });
        });
    </script>
    @stack('scripts')
</body>

</html>