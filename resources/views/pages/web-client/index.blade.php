@extends('layout.master')

@section('title', 'Web Client')
@section('page_title', 'Web Client')

@section('content')
    <div class="mb-8 sm:mb-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4 sm:gap-6">
        <p class="text-gray-600 text-[15px] leading-relaxed w-full sm:w-auto">Kelola daftar Web Client untuk pengiriman
            artikel otomatis.</p>
        <button type="button" onclick="openCreateModal()"
            class="w-full sm:w-auto bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] hover:from-[#1e1b4b] hover:via-[#0d0d0d] hover:to-[#1e1b4b] text-white px-5 py-3 sm:py-2.5 rounded-xl sm:rounded-lg text-[14px] font-bold transition-all flex items-center justify-center gap-2 whitespace-nowrap shadow-sm hover:shadow-md active:scale-[0.98]">
            <span class="icon-[material-symbols-light--add] w-[20px] h-[20px]"></span>
            Tambah Web Client
        </button>
    </div>

    <!-- Filter & Table -->
    <div
        class="w-full md:bg-white md:rounded-2xl md:shadow-[0_4px_24px_rgba(0,0,0,0.02)] md:border md:border-gray-100 md:p-6 p-0">
        <!-- Top Controls -->
        <form method="GET" action="{{ route('web-client.index') }}"
            class="flex flex-col sm:flex-row justify-between gap-3 sm:gap-4 mb-6">
            <!-- Search -->
            <div class="relative w-full sm:max-w-xs">
                <div class="absolute inset-y-0 left-0 pl-5 sm:pl-4 flex items-center pointer-events-none">
                    <span class="icon-[material-symbols-light--search] w-5 h-5 sm:w-5 sm:h-5 text-gray-400"></span>
                </div>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    class="w-full pl-12 sm:pl-11 pr-4 py-3 sm:py-2.5 bg-white sm:bg-gray-50 border border-gray-100 sm:border-transparent rounded-2xl sm:rounded-full shadow-sm sm:shadow-none focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm sm:text-[13px] text-gray-700 placeholder-gray-400 font-medium"
                    placeholder="Cari client...">
            </div>

            <!-- Limit -->
            <div class="w-full sm:w-auto flex-shrink-0">
                <div class="relative w-full">
                    <select name="limit" onchange="this.form.submit()"
                        class="w-full appearance-none pl-5 pr-10 py-3 sm:py-2.5 bg-white sm:bg-gray-50 border border-gray-100 sm:border-transparent rounded-2xl sm:rounded-full shadow-sm sm:shadow-none focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm sm:text-[13px] text-gray-700 font-medium cursor-pointer hover:bg-gray-50">
                        <option value="5" {{ ($limit ?? 10) == 5 ? 'selected' : '' }}>5 Entri</option>
                        <option value="10" {{ ($limit ?? 10) == 10 ? 'selected' : '' }}>10 Entri </option>
                        <option value="25" {{ ($limit ?? 10) == 25 ? 'selected' : '' }}>25 Entri</option>
                        <option value="50" {{ ($limit ?? 10) == 50 ? 'selected' : '' }}>50 Entri</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <span class="icon-[material-symbols-light--keyboard-arrow-down] w-5 h-5 text-gray-400"></span>
                    </div>
                </div>
                <!-- Retain search term when limit changes -->
                @if(($search ?? '') != '')
                    <input type="hidden" name="search_retain" value="{{ $search }}" disabled>
                @endif
            </div>
        </form>

        <!-- Table (Desktop View) -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-white">
                        <th class="py-4 px-5 text-sm font-semibold rounded-tl-xl">No.</th>
                        <th class="py-4 px-5 text-sm font-semibold">Nama Website</th>
                        <th class="py-4 px-5 text-sm font-semibold">URL Website</th>
                        <th class="py-4 px-5 text-sm font-semibold">Username</th>
                        <th class="py-4 px-5 text-sm font-semibold">Auto Publish</th>
                        <th class="py-4 px-5 text-sm font-semibold text-right rounded-tr-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $key => $client)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-4 px-5 text-sm text-gray-600 font-medium">{{ $clients->firstItem() + $key }}.</td>
                            <td class="py-4 px-5 text-sm text-gray-800 font-medium">{{ $client->nama_website }}</td>
                            <td class="py-4 px-5 text-sm text-gray-600"><a href="{{ $client->url_website }}" target="_blank"
                                    class="text-blue-500 hover:underline">{{ $client->url_website }}</a></td>
                            <td class="py-4 px-5 text-sm text-gray-600">{{ $client->username }}</td>
                            <td class="py-4 px-5 text-sm">
                                @if($client->publikasi_otomatis)
                                    <span
                                        class="px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full font-semibold border border-green-200">Auto
                                        (ON)</span>
                                @else
                                    <span
                                        class="px-3 py-1 bg-gray-100 text-gray-800 text-xs rounded-full font-semibold border border-gray-200">Manual
                                        (OFF)</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-sm text-right flex justify-end gap-2">
                                <!-- Edit Button -->
                                <button type="button"
                                    onclick="openEditModal({{ $client->id }}, '{{ route('web-client.edit', $client->id) }}', '{{ route('web-client.update', $client->id) }}')"
                                    class="group relative flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 text-orange-600 hover:bg-orange-500 hover:text-white transition-colors">
                                    <span class="icon-[material-symbols-light--edit-square-outline] w-5 h-5"></span>
                                </button>

                                <!-- Delete Button -->
                                <form action="{{ route('web-client.destroy', $client->id) }}" method="POST"
                                    class="inline-block form-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="group relative flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-600 hover:text-white transition-colors">
                                        <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-gray-500">Tidak ada data ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards Layout (Hidden on LG and up) -->
        <div class="block lg:hidden space-y-4">
            @forelse($clients as $key => $client)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Top Section -->
                    <div class="p-4 flex items-start justify-between">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-8 h-8 rounded-full bg-gray-100 text-gray-900 flex items-center justify-center text-sm font-bold flex-shrink-0">
                                {{ $clients->firstItem() + $key }}
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-[15px] leading-tight mb-1">{{ $client->nama_website }}
                                </h3>
                                <a href="{{ $client->url_website }}" target="_blank"
                                    class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                    <span class="icon-[material-symbols-light--link] w-3.5 h-3.5"></span>
                                    {{ parse_url($client->url_website, PHP_URL_HOST) ?? $client->url_website }}
                                </a>
                            </div>
                        </div>
                        <!-- Actions -->
                        <div class="flex items-center gap-1">
                            <button type="button"
                                onclick="openEditModal({{ $client->id }}, '{{ route('web-client.edit', $client->id) }}', '{{ route('web-client.update', $client->id) }}')"
                                class="w-8 h-8 flex items-center justify-center text-orange-600 hover:text-orange-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <span class="icon-[material-symbols-light--edit-square-outline] w-5 h-5"></span>
                            </button>
                            <form action="{{ route('web-client.destroy', $client->id) }}" method="POST"
                                class="inline-block form-delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-8 h-8 flex items-center justify-center text-red-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Bottom Section -->
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-gray-600 text-[13px] font-medium">
                            <span class="icon-[material-symbols-light--person] w-4 h-4 text-gray-400"></span>
                            {{ $client->username }}
                        </div>
                        <div>
                            @if($client->publikasi_otomatis)
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="relative flex h-2 w-2">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                    </span>
                                    <span class="text-[12px] font-semibold text-green-700">Auto Publish</span>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                    <span class="text-[12px] font-medium text-gray-500">Manual</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center text-sm text-gray-500 bg-gray-50 rounded-xl border border-gray-100">
                    Tidak ada data ditemukan.
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($clients->hasPages() || $clients->total() > 0)
            <div class="mt-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-gray-600">
                <div>
                    Menampilkan {{ $clients->firstItem() ?? 0 }} sampai {{ $clients->lastItem() ?? 0 }} dari
                    {{ $clients->total() }} entri
                </div>
                <div>
                    {{ $clients->links('pagination::tailwind') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Toast Notification --}}
    <div id="toast-notification"
        class="fixed top-6 right-6 z-[1000] flex items-center gap-3 px-5 py-3.5 bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-white text-sm font-medium
                                       rounded-2xl shadow-2xl opacity-0 -translate-y-4 pointer-events-none transition-all duration-300">
        <span id="toast-icon"
            class="w-5 h-5 text-emerald-400 flex-shrink-0 block icon-[material-symbols-light--check-circle-outline]"></span>
        <span id="toast-text"></span>
    </div>

    @include('pages.web-client.create')
    @include('pages.web-client.edit')

    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast-notification');
            const icon = document.getElementById('toast-icon');
            document.getElementById('toast-text').textContent = message;
            icon.className = type === 'success'
                ? 'w-5 h-5 text-emerald-400 flex-shrink-0 block icon-[material-symbols-light--check-circle-outline]'
                : 'w-5 h-5 text-red-400 flex-shrink-0 block icon-[material-symbols-light--error-outline]';

            toast.classList.remove('opacity-0', '-translate-y-4', 'pointer-events-none');
            setTimeout(() => toast.classList.add('opacity-0', '-translate-y-4', 'pointer-events-none'), 3000);
        }
    </script>
@endsection