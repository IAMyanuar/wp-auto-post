<!DOCTYPE html>
<html lang="en" class="font-outfit">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Lupa Password - Material WP Auto Post</title>
</head>
<body class="font-outfit bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-text min-h-screen flex items-center justify-center p-4 overflow-hidden">
    
    <canvas id="starfield" class="fixed inset-0 z-0 pointer-events-none"></canvas>

    <div class="relative z-10 w-full max-w-md bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl border border-white/20 p-8 sm:p-10 transform transition-all">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white mb-6 shadow-lg shadow-indigo-500/20">
                <span class="icon-[material-symbols-light--lock-reset] w-8 h-8"></span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Lupa Password</h1>
            <p class="text-sm text-gray-500 mt-2">Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang password Anda, silakan cek email / kotak log Anda.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <span class="icon-[material-symbols-light--mail-outline] w-5 h-5"></span>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full pl-10 pr-4 py-3 bg-gray-50 border @error('email') border-red-500 @else border-gray-200 @enderror rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm placeholder-gray-400 font-medium" placeholder="admin@example.com" required autofocus>
                </div>
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full flex justify-center py-3.5 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] hover:shadow-indigo-500/10 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 active:scale-[0.98] transition-all mt-8">
                Kirim Tautan Atur Ulang
            </button>
            
            <div class="mt-6 text-center text-sm text-gray-600">
                Lupa email? 
                <a href="{{ route('login') }}" class="font-semibold text-gray-900 underline hover:text-gray-600 transition-colors">Kembali ke Login</a>
            </div>
        </form>
    </div>

    <script src="{{ asset('assets/js/starfield.js') }}"></script>
</body>
</html>
