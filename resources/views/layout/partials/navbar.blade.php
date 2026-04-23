<!-- Top Navbar -->
<header class="fixed top-0 left-0 right-0 md:left-64 z-30 h-16 flex items-center px-5 md:px-8 gap-4"
    style="background: hsl(var(--surface-1)); border-bottom: 1px solid hsl(var(--border-light)); box-shadow: 0 1px 0 hsl(var(--border-light));">

    <!-- Mobile Logo -->
    <a href="{{ route('dashboard') }}" class="md:hidden flex-shrink-0">
        <img src="{{ asset('assets/properti/Logo.png') }}" alt="Logo" class="h-8 object-contain">
    </a>

    <!-- Desktop: Page Breadcrumb / Greeting -->
    <div class="hidden md:flex flex-col justify-center">
        <span class="text-xs font-semibold uppercase tracking-widest" style="color: hsl(var(--text-subtle));">WP Auto Post</span>
        <span class="text-[15px] font-bold leading-tight" style="color: hsl(var(--text-base));">@yield('page_title', 'Dashboard')</span>
    </div>

    <!-- Spacer -->
    <div class="flex-1"></div>

    <!-- Right Actions -->
    <div class="flex items-center gap-2.5">
        <!-- Profile Button -->
        <div class="relative group">
            <button class="flex items-center gap-2.5 pl-1.5 pr-3 py-1.5 rounded-xl transition-all duration-200 cursor-pointer">
                <!-- Avatar -->
                <div class="w-7 h-7 rounded-lg overflow-hidden flex-shrink-0">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=111827&color=fff&bold=true&size=100" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <span class="text-[13px] font-semibold hidden sm:block" style="color: hsl(var(--text-base));">Admin</span>
                <!-- Chevron -->
                <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current transition-transform duration-200 group-hover:rotate-180" style="color: hsl(var(--text-subtle));">
                    <path d="M7 10l5 5 5-5z"/>
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div class="absolute right-0 top-full mt-2 w-44 p-1.5 rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
                style="background: hsl(var(--surface-1)); border: 1px solid hsl(var(--border-light)); box-shadow: var(--shadow-card-lg);">
                <a href="{{ route('logout') }}"
                    class="flex items-center gap-2.5 w-full px-3 py-2.5 text-[13px] font-medium rounded-lg transition-colors duration-150"
                    style="color: hsl(var(--danger));"
                    onmouseenter="this.style.background='hsl(0 84% 97%)'"
                    onmouseleave="this.style.background='transparent'">
                    <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>

<script>
    (function () {
        function tick() {
            const el = document.getElementById('navbar-clock');
            if (!el) return;
            el.textContent = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
            });
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>
