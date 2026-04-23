<!DOCTYPE html>
<html lang="en" class="font-outfit">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Atur Ulang Password - Material WP Auto Post</title>
</head>
<body class="font-outfit bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-text min-h-screen flex items-center justify-center p-4 overflow-hidden">
    
    <canvas id="starfield" class="fixed inset-0 z-0 pointer-events-none"></canvas>

    <div class="relative z-10 w-full max-w-md bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl border border-white/20 p-8 sm:p-10 transform transition-all">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white mb-6 shadow-lg shadow-indigo-500/20">
                <span class="icon-[material-symbols-light--admin-panel-settings-outline] w-8 h-8"></span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Buat Password Baru</h1>
            <p class="text-sm text-gray-500 mt-2">Silakan masukkan password baru Anda.</p>
        </div>

        <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ implode('', $request->route()->parameters()) }}">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Saat Ini</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <span class="icon-[material-symbols-light--mail-outline] w-5 h-5"></span>
                    </div>
                    <input type="email" id="email" name="email" value="{{ request()->email ?? old('email') }}" class="w-full pl-10 pr-4 py-3 bg-gray-100 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm font-medium" readonly required>
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <span class="icon-[material-symbols-light--lock-outline] w-5 h-5"></span>
                    </div>
                    <input type="password" id="password" name="password" class="w-full pl-10 pr-4 py-3 bg-gray-50 border @error('password') border-red-500 @else border-gray-200 @enderror rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm placeholder-gray-400 font-medium" required autofocus>
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <span class="icon-[material-symbols-light--lock-outline] w-5 h-5"></span>
                    </div>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm placeholder-gray-400 font-medium" required>
                </div>
            </div>

            <button type="submit" class="w-full flex justify-center py-3.5 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] hover:shadow-indigo-500/10 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 active:scale-[0.98] transition-all mt-8">
                Simpan Password
            </button>
        </form>
    </div>

    <script src="{{ asset('assets/js/starfield.js') }}"></script>
</body>
</html>
