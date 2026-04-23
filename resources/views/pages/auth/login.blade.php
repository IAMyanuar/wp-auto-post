<!DOCTYPE html>
<html lang="en" class="font-outfit">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Login - WP Auto Post</title>
</head>

<body
    class="font-outfit bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-text min-h-screen flex items-center justify-center p-4">

    <!-- Login Container -->
    <div
        class="w-full max-w-md bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl border border-white/20 p-8 sm:p-10 transform transition-all">

        <!-- Header -->
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white mb-6 shadow-lg shadow-indigo-500/20">
                <span class="icon-[material-symbols-light--passkey-rounded] w-8 h-8"></span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Welcome</h1>
            <p class="text-sm text-gray-500 mt-2">Masuk ke Dashboard Manajemen Konten</p>
        </div>

        <!-- Form -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <span class="icon-[material-symbols-light--mail-outline] w-5 h-5"></span>
                    </div>
                    <input type="email" id="email" name="email"
                        class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm placeholder-gray-400 font-medium"
                        placeholder="admin@example.com" required>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <a href="{{ route('password.request') }}"
                        class="text-xs font-semibold text-gray-900 underline hover:text-gray-600 transition-colors">Lupa
                        Password?</a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <span class="icon-[material-symbols-light--lock-outline] w-5 h-5"></span>
                    </div>
                    <input type="password" id="password" name="password"
                        class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm placeholder-gray-400 font-medium"
                        placeholder="••••••••" required>
                </div>
            </div>

            <div class="flex items-center">
                <input id="remember-me" name="remember-me" type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded cursor-pointer transition-all">
                <label for="remember-me" class="ml-2 block text-sm text-gray-700 cursor-pointer">
                    Ingat saya
                </label>
            </div>

            <button type="submit"
                class="w-full flex justify-center py-3.5 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] hover:shadow-indigo-500/10 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 active:scale-[0.98] transition-all mt-8">
                Masuk ke Dashboard
            </button>
        </form>
    </div>

</body>

</html>