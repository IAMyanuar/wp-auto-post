<aside id="app-sidebar"
    class="fixed bottom-0 left-0 w-full h-20 md:h-full md:w-64 md:flex-col flex items-center md:items-stretch justify-around md:justify-start z-40 transition-all duration-300 shadow-[20px_0_40px_rgba(0,0,0,0.01)]"
    style="background: linear-gradient(to bottom, #ffffff, hsl(var(--surface-2))); border-right: 1px solid hsl(var(--border-light));">

    <div class="hidden md:flex items-center px-6 h-16 flex-shrink-0"
        style="border-bottom: 1px solid hsl(var(--border-light));">
        <a href="{{ route('dashboard') }}" class="transition-transform hover:scale-105 duration-300">
            <img src="{{ asset('assets/properti/Logo.png') }}" alt="Semesta Multitekno"
                class="h-8 w-auto object-contain">
        </a>
    </div>

    <div class="hidden md:block px-5 pt-5 pb-2"></div>


    <nav
        class="flex md:flex-col flex-1 md:flex-none md:px-3 md:gap-y-1 justify-around md:justify-start w-full pb-1 md:pb-4">
        @php
            $menuItems = [
                [
                    'route' => 'dashboard',
                    'label' => 'Dashboard',
                    'link' => route('dashboard'),
                    'anim' => 'group-hover:rotate-6',
                    'icon' => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>'
                ],

                [
                    'route' => 'ai-prompt.*',
                    'label' => 'Prompt AI',
                    'link' => route('ai-prompt.index'),
                    'anim' => 'group-hover:scale-110',
                    'icon' => '<path d="M19 9l1.25-2.75L23 5l-2.75-1.25L19 1l-1.25 2.75L15 5l2.75 1.25L19 9zm-7.5.5L9 4 6.5 9.5 1 12l5.5 2.5L9 20l2.5-5.5L17 12l-5.5-2.5zM19 15l-1.25 2.75L15 19l2.75 1.25L19 23l1.25-2.75L23 21l-2.75-1.25L19 15z"/>'
                ],

                [
                    'route' => 'web-client.*',
                    'label' => 'Web Client',
                    'link' => route('web-client.index'),
                    'anim' => 'group-hover:rotate-12',
                    'icon' => '<path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95a15.65 15.65 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.92 8zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56A7.987 7.987 0 0 1 5.08 16zm2.95-8H5.08a7.987 7.987 0 0 1 3.56-3.56A15.65 15.65 0 0 0 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2z"/>'
                ],

                [
                    'route' => 'penjadwalan.*',
                    'label' => 'Penjadwalan',
                    'link' => route('penjadwalan.index'),
                    'anim' => 'group-hover:translate-y-[-2px]',
                    'icon' => '<path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zm-8 4H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z"/>'
                ],

                [
                    'route' => 'riwayat.*',
                    'label' => 'Riwayat',
                    'link' => route('riwayat.index'),
                    'anim' => 'group-hover:rotate-[-20deg]',
                    'icon' => '<path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>'
                ],
            ];
        @endphp

        @foreach($menuItems as $item)
            @php $isActive = request()->routeIs($item['route']); @endphp

            <a href="{{ $item['link'] }}"
                class="group relative flex flex-col md:flex-row items-center justify-center md:justify-start gap-0 md:gap-3.5 px-2 py-1.5 md:px-3 md:py-2.5 rounded-2xl md:rounded-xl transition-all duration-300
                                                        {{ $isActive ? 'bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] to-gray-900 text-white shadow-lg' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900 hover:scale-[1.01]' }}">

                <div
                    class="flex-shrink-0 w-10 h-10 md:w-9 md:h-9 rounded-xl flex items-center justify-center transition-all duration-300
                                                        {{ $isActive
            ? 'bg-white/15 ring-1 ring-white/20'
            : 'bg-white shadow-[0_2px_8px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.04] group-hover:shadow-none group-hover:bg-transparent group-hover:ring-0' }}">
                    <svg viewBox="0 0 24 24"
                        class="w-5 h-5 fill-current transition-all duration-300 {{ $item['anim'] }}
                                                            {{ $isActive ? 'text-white scale-110' : 'text-gray-500 group-hover:text-gray-900' }}">
                        {!! $item['icon'] !!}
                    </svg>
                </div>

                <span
                    class="hidden md:inline text-[13.5px] leading-none truncate {{ $isActive ? 'font-bold' : 'font-semibold' }}">
                    {{ $item['label'] }}
                </span>
                <span
                    class="md:hidden text-[9px] mt-1 font-bold leading-none {{ $isActive ? 'text-white' : 'text-gray-500' }}">
                    {{ Str::words($item['label'], 1, '') }}
                </span>

                @if($isActive)
                    <div class="hidden md:block ml-auto w-1.5 h-1.5 rounded-full bg-white/50 animate-pulse flex-shrink-0"></div>
                @endif

            </a>
        @endforeach
    </nav>

</aside>