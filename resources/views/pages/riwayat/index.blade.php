@extends('layout.master')

@section('title', 'Riwayat Artikel')
@section('page_title', 'Riwayat Artikel')

@section('content')
    <div class="overflow-hidden max-w-full">
        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-gray-500 text-sm leading-relaxed">Riwayat dari semua artikel yang telah selesai diproses
                (terpublish atau gagal).</p>
        </div>

        {{-- Filter & Table Container --}}
        <div
            class="w-full md:bg-white md:rounded-2xl md:shadow-[0_4px_24px_rgba(0,0,0,0.02)] md:border md:border-gray-100 md:p-6 p-0">

            {{-- Top Controls --}}
            <form method="GET" action="{{ route('riwayat.index') }}" class="flex flex-col sm:flex-row gap-3 mb-5">
                {{-- Search --}}
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="icon-[material-symbols-light--search] w-4 h-4 text-gray-400"></span>
                    </div>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm text-gray-700 placeholder-gray-400"
                        placeholder="Cari judul artikel...">
                </div>

                {{-- Website Filter --}}
                <div class="relative w-full sm:w-48 flex-shrink-0">
                    <select name="website_id" onchange="this.form.submit()"
                        class="w-full appearance-none pl-4 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm text-gray-700 cursor-pointer">
                        <option value="">Semua Website</option>
                        @foreach($websites as $web)
                            <option value="{{ $web->id }}" {{ ($websiteId ?? '') == $web->id ? 'selected' : '' }}>
                                {{ Str::limit($web->nama_website, 20) }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="icon-[material-symbols-light--keyboard-arrow-down] w-4 h-4 text-gray-400"></span>
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="relative w-full sm:w-40 flex-shrink-0">
                    <select name="status" onchange="this.form.submit()"
                        class="w-full appearance-none pl-4 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm text-gray-700 cursor-pointer">
                        <option value="semua" {{ ($status ?? '') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="terpublish" {{ ($status ?? '') == 'terpublish' ? 'selected' : '' }}>Terpublish</option>
                        <option value="gagal" {{ ($status ?? '') == 'gagal' ? 'selected' : '' }}>Gagal</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="icon-[material-symbols-light--keyboard-arrow-down] w-4 h-4 text-gray-400"></span>
                    </div>
                </div>

                {{-- Limit --}}
                <div class="relative w-full sm:w-32 flex-shrink-0">
                    <select name="limit" onchange="this.form.submit()"
                        class="w-full appearance-none pl-4 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm text-gray-700 cursor-pointer">
                        <option value="5" {{ ($limit ?? 10) == 5 ? 'selected' : '' }}>5 Entri</option>
                        <option value="10" {{ ($limit ?? 10) == 10 ? 'selected' : '' }}>10 Entri</option>
                        <option value="25" {{ ($limit ?? 10) == 25 ? 'selected' : '' }}>25 Entri</option>
                        <option value="50" {{ ($limit ?? 10) == 50 ? 'selected' : '' }}>50 Entri</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="icon-[material-symbols-light--keyboard-arrow-down] w-4 h-4 text-gray-400"></span>
                    </div>
                </div>
            </form>

            @php
                $statusConfig = [
                    'terpublish' => ['label' => 'Terbit', 'bg' => 'bg-green-50', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
                    'gagal' => ['label' => 'Gagal', 'bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'dot' => 'bg-red-500'],
                ];
            @endphp

            {{-- Table (Desktop lg+) --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-white">
                            <th class="py-4 px-5 text-sm font-semibold rounded-tl-xl w-12">No.</th>
                            <th class="py-4 px-5 text-sm font-semibold">Judul Artikel</th>
                            <th class="py-4 px-5 text-sm font-semibold">Website</th>
                            <th class="py-4 px-5 text-sm font-semibold">Nilai SEO</th>
                            <th class="py-4 px-5 text-sm font-semibold">Jadwal</th>
                            <th class="py-4 px-5 text-sm font-semibold">Status</th>
                            <th class="py-4 px-5 text-sm font-semibold text-right rounded-tr-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($artikels as $key => $artikel)
                            @php $sc = $statusConfig[$artikel->status] ?? $statusConfig['diproses']; @endphp
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors"
                                data-artikel-id="{{ $artikel->id }}" data-status="{{ $artikel->status }}">
                                <td class="py-4 px-5 text-sm text-gray-500 font-medium">{{ $artikels->firstItem() + $key }}.
                                </td>
                                <td class="py-4 px-5 text-sm text-gray-800 font-semibold max-w-[220px]">
                                    <p class="truncate">{{ $artikel->judul }}</p>
                                </td>
                                <td class="py-4 px-5 text-sm text-gray-600">{{ $artikel->websiteKlien->nama_website ?? '-' }}
                                </td>
                                <td class="py-4 px-5 text-sm">
                                    @if(!is_null($artikel->skor_seo))
                                        <div class="flex items-center gap-1.5">
                                            <span
                                                class="font-bold {{ $artikel->skor_seo >= 70 ? 'text-green-600' : ($artikel->skor_seo >= 50 ? 'text-orange-500' : 'text-red-500') }}">
                                                {{ $artikel->skor_seo }}
                                            </span>
                                            <span class="text-xs text-gray-400">/ 100</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 text-sm text-gray-600">
                                    @if($artikel->tanggal_jadwal)
                                        <div class="flex items-center gap-1.5">
                                            <span class="icon-[material-symbols-light--schedule] w-4 h-4 text-gray-400"></span>
                                            {{ $artikel->tanggal_jadwal->format('d M Y, H:i') }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-4 px-5 text-sm">
                                    <span data-badge-id="{{ $artikel->id }}"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full border {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                        {{ $sc['label'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-5 text-sm text-right">
                                    <div data-action-id="{{ $artikel->id }}" class="flex items-center justify-end gap-2">
                                        @if($artikel->status !== 'diproses')
                                            @if($artikel->status === 'gagal')
                                                <form action="{{ route('penjadwalan.retry', $artikel->id) }}" method="POST"
                                                    class="inline-block form-retry">
                                                    @csrf
                                                    <button type="submit" title="Coba Ulang Semua (Retry Full)"
                                                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors">
                                                        <span class="icon-[material-symbols-light--refresh] w-5 h-5"></span>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('penjadwalan.edit', $artikel->id) }}"
                                                class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 text-orange-600 hover:bg-orange-500 hover:text-white transition-colors">
                                                <span class="icon-[material-symbols-light--edit-square-outline] w-5 h-5"></span>
                                            </a>
                                            <form action="{{ route('penjadwalan.destroy', $artikel->id) }}" method="POST"
                                                class="inline-block form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-600 hover:text-white transition-colors">
                                                    <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-orange-400 italic">Sedang diproses...</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-sm text-gray-400">
                                    <span
                                        class="icon-[material-symbols-light--calendar-month] w-10 h-10 mx-auto block mb-2 text-gray-300"></span>
                                    Belum ada jadwal artikel.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards (< lg) --}} <div class="block lg:hidden space-y-3">
                @forelse($artikels as $key => $artikel)
                    @php $sc = $statusConfig[$artikel->status] ?? $statusConfig['diproses']; @endphp
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden w-full min-w-0"
                        data-artikel-id="{{ $artikel->id }}" data-status="{{ $artikel->status }}">
                        <div class="p-4 pb-2 flex items-start gap-3">
                            <div
                                class="w-8 h-8 rounded-full bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-white flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                                {{ $artikels->firstItem() + $key }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-gray-900 text-[15px] leading-snug line-clamp-2">
                                    {{ $artikel->judul }}
                                </h3>
                            </div>
                        </div>
                        <div class="px-4 pb-3">
                            <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-xs text-gray-500 mt-1">
                                <span class="flex items-center gap-1">
                                    <span class="icon-[material-symbols-light--language] w-3.5 h-3.5 text-gray-400"></span>
                                    {{ $artikel->websiteKlien->nama_website ?? '-' }}
                                </span>
                                @if($artikel->tanggal_jadwal)
                                    <span class="flex items-center gap-1">
                                        <span class="icon-[material-symbols-light--schedule] w-3.5 h-3.5 text-gray-400"></span>
                                        {{ $artikel->tanggal_jadwal->format('d M Y, H:i') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs text-gray-500 font-medium">
                                    Yoast:
                                    @if(!is_null($artikel->skor_seo))
                                        <span
                                            class="{{ $artikel->skor_seo >= 70 ? 'text-green-600' : ($artikel->skor_seo >= 50 ? 'text-orange-500' : 'text-red-500') }} font-bold">{{ $artikel->skor_seo }}</span>/100
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </span>
                                <span data-badge-id="{{ $artikel->id }}"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-[11px] font-semibold rounded-full border flex-shrink-0 {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                                    {{ $sc['label'] }}
                                </span>
                            </div>

                            <div data-action-id="{{ $artikel->id }}" class="flex items-center gap-2 justify-end pt-2 border-t border-gray-100/50">
                                @if($artikel->status !== 'diproses')
                                    @if($artikel->status === 'gagal')
                                        <form action="{{ route('penjadwalan.retry', $artikel->id) }}" method="POST" class="form-retry">
                                            @csrf
                                            <button type="submit" title="Coba Ulang Full"
                                                class="h-9 px-3 flex items-center justify-center gap-2 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all text-xs font-bold">
                                                <span class="icon-[material-symbols-light--refresh] w-4 h-4"></span>
                                                Retry
                                            </button>
                                        </form>
                                    @endif
                                    @if(is_null($artikel->skor_seo) && !is_null($artikel->wp_id))
                                        <form action="{{ route('penjadwalan.retryYoast', $artikel->id) }}" method="POST" class="form-retry-yoast">
                                            @csrf
                                            <button type="submit" title="Cek Yoast"
                                                class="h-9 px-3 flex items-center justify-center gap-2 rounded-xl bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all text-xs font-bold">
                                                <span class="icon-[material-symbols-light--search-check] w-4 h-4"></span>
                                                SEO
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('penjadwalan.edit', $artikel->id) }}"
                                        class="h-9 px-3 flex items-center justify-center gap-2 rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-500 hover:text-white transition-all text-xs font-bold">
                                        <span class="icon-[material-symbols-light--edit-square-outline] w-4 h-4"></span>
                                        Edit
                                    </a>
                                    <form action="{{ route('penjadwalan.destroy', $artikel->id) }}" method="POST" class="form-delete">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="h-9 px-3 flex items-center justify-center gap-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-all text-xs font-bold">
                                            <span class="icon-[material-symbols-light--delete-outline] w-4 h-4"></span>
                                            Hapus
                                        </button>
                                    </form>
                                @else
                                    <div class="flex items-center gap-2 text-orange-500 text-[11px] font-bold italic py-1">
                                        <span class="icon-[material-symbols-light--progress-activity] w-4 h-4 animate-spin"></span>
                                        Artikel sedang diproses...
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-sm text-gray-400 bg-gray-50 rounded-xl border border-gray-100">
                        <span
                            class="icon-[material-symbols-light--calendar-month] w-8 h-8 mx-auto block mb-2 text-gray-300"></span>
                        Belum ada jadwal artikel.
                    </div>
                @endforelse
        </div>

        {{-- Pagination --}}
        @if($artikels->hasPages() || $artikels->total() > 0)
            <div class="mt-5 flex flex-col sm:flex-row justify-between items-center gap-3 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500 order-2 sm:order-1">
                    Menampilkan <span class="font-semibold text-gray-700">{{ $artikels->firstItem() ?? 0 }}</span>
                    &ndash;
                    <span class="font-semibold text-gray-700">{{ $artikels->lastItem() ?? 0 }}</span>
                    dari <span class="font-semibold text-gray-700">{{ $artikels->total() }}</span> entri
                </p>
                <div class="order-1 sm:order-2">
                    {{ $artikels->links('pagination::tailwind') }}
                </div>
            </div>
        @endif
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const statusConfig = {
                terpublish: { label: 'Terpublish', bg: 'bg-green-50', text: 'text-green-700', border: 'border-green-200', dot: 'bg-green-500' },
                gagal: { label: 'Gagal', bg: 'bg-red-50', text: 'text-red-700', border: 'border-red-200', dot: 'bg-red-500' },
            };

            // Kumpulkan semua ID artikel yang saat ini berstatus 'diproses' di halaman ini
            const processingIds = Array.from(document.querySelectorAll('[data-artikel-id][data-status="diproses"]'))
                .map(el => el.dataset.artikelId);

            // Intercept form-retry untuk memberikan feedback realtime sebelum page reload
            document.body.addEventListener('submit', function (e) {
                const retryForm = e.target.closest('.form-retry');
                if (!retryForm) return;

                const container = retryForm.closest('[data-action-id]');
                if (container) {
                    const artikelId = container.dataset.actionId;

                    // Beri jeda sedikit agar permintaan form POST dikirimkan ke backend lebih dahulu 
                    // sebelum kerangka HTML (DOM-nya) dihancurkan oleh Javascript.
                    setTimeout(() => {
                        // Secara langsung ubah badge menjadi 'diproses' kuning realtime
                        updateBadge(artikelId, 'diproses');

                        // Ganti action buttons dengan teks 'Diproses...'
                        container.innerHTML = `<span class="text-xs text-orange-400 italic px-1">Diproses...</span>`;
                    }, 50);
                }
            });

            // Tidak ada artikel diproses di halaman ini → tidak perlu polling sama sekali
            if (processingIds.length === 0) return;

            let pollInterval = null;
            const POLL_DELAY = 1500; // 1.5 detik (lebih responsif untuk menangkap progress realtime)

            function updateBadge(artikelId, status) {
                const cfg = statusConfig[status] ?? statusConfig['diproses'];
                // Update semua elemen badge milik artikel ini (desktop + mobile)
                document.querySelectorAll(`[data-badge-id="${artikelId}"]`).forEach(badge => {
                    badge.className = `inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full border ${cfg.bg} ${cfg.text} ${cfg.border}`;
                    badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full ${cfg.dot}"></span>${cfg.label}`;
                });
                // Update data-status pada SEMUA elemen (desktop + mobile)
                document.querySelectorAll(`[data-artikel-id="${artikelId}"]`).forEach(el => {
                    el.dataset.status = status;
                });

                // Jika status bukan diproses lagi, tampilkan tombol edit & delete
                if (status !== 'diproses') {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                    const editUrl = `{{ url('/penjadwalan') }}/${artikelId}/edit`;
                    const deleteUrl = `{{ url('/penjadwalan') }}/${artikelId}`;

                        let buttonsHtml = '';
                        if (isDesktop) {
                            buttonsHtml = `<a href="${editUrl}" class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 text-orange-600 hover:bg-orange-500 hover:text-white transition-colors"><span class="icon-[material-symbols-light--edit-square-outline] w-5 h-5"></span></a><form action="${deleteUrl}" method="POST" class="inline-block form-delete"><input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-600 hover:text-white transition-colors"><span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span></button></form>`;
                        } else {
                            buttonsHtml = `<a href="${editUrl}" class="h-9 px-3 flex items-center justify-center gap-2 rounded-xl bg-orange-50 text-orange-600 transition-all text-xs font-bold"><span class="icon-[material-symbols-light--edit-square-outline] w-4 h-4"></span> Edit</a><form action="${deleteUrl}" method="POST" class="form-delete"><input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="h-9 px-3 flex items-center justify-center gap-2 rounded-xl bg-red-50 text-red-600 transition-all text-xs font-bold"><span class="icon-[material-symbols-light--delete-outline] w-4 h-4"></span> Hapus</button></form>`;
                        }
                        container.innerHTML = buttonsHtml;
                    });
                }
            }

            function doPoll() {
                // Ambil ID yang masih diproses dari DOM
                const stillProcessing = Array.from(document.querySelectorAll('[data-artikel-id][data-status="diproses"]'))
                    .map(el => el.dataset.artikelId);

                // Sudah tidak ada yang diproses → hentikan polling
                if (stillProcessing.length === 0) {
                    clearInterval(pollInterval);
                    return;
                }

                // Request hanya bawa ID yang masih perlu dicek
                fetch(`{{ route('penjadwalan.poll-status') }}?ids[]=${stillProcessing.join('&ids[]=')}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(({ statuses }) => {
                        statuses.forEach(({ id, status }) => {
                            const el = document.querySelector(`[data-artikel-id="${id}"]`);
                            if (el && el.dataset.status !== status) {
                                updateBadge(id, status);
                            }
                        });

                        // Custom Progress UI (Pojok Kanan Atas)
                        const processing = statuses.find(s => s.status === 'diproses');
                        const finished = statuses.find(s => s.status !== 'diproses');
                        const target = processing || finished;

                        const toastEl = document.getElementById('progress-toast-container');
                        let pctEl = document.getElementById('progress-pct');
                        let textEl = document.getElementById('progress-text');
                        let barEl = document.getElementById('progress-bar');
                        let iconEl = document.getElementById('progress-icon');

                        if (!target) {
                            toastEl.classList.add('translate-x-[120%]', 'opacity-0');
                        } else {
                            toastEl.classList.remove('translate-x-[120%]', 'opacity-0');

                            const pct = target.persentase_proses || 0;
                            const isError = target.status === 'gagal';
                            const isSuccess = target.status === 'terjadwal' || target.status === 'terpublish';

                            pctEl.textContent = pct + '%';
                            textEl.textContent = target.keterangan_proses || 'Memproses artikel...';
                            barEl.style.width = Math.max(pct, 5) + '%';

                            if (isError) {
                                pctEl.className = 'text-[13px] font-bold text-red-500 ml-2';
                                barEl.className = 'bg-red-500 h-1.5 rounded-full transition-all duration-500 ease-out';
                                iconEl.className = 'w-9 h-9 rounded-full bg-red-50 flex items-center justify-center flex-shrink-0';
                                iconEl.innerHTML = '<span class="icon-[material-symbols-light--error] w-5 h-5 text-red-600"></span>';
                            } else if (isSuccess) {
                                pctEl.className = 'text-[13px] font-bold text-green-500 ml-2';
                                barEl.className = 'bg-green-500 h-1.5 rounded-full transition-all duration-500 ease-out';
                                iconEl.className = 'w-9 h-9 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0';
                                iconEl.innerHTML = '<span class="icon-[material-symbols-light--check-circle] w-5 h-5 text-green-600"></span>';
                            } else {
                                pctEl.className = 'text-[13px] font-bold text-blue-600 ml-2';
                                barEl.className = 'bg-blue-600 h-1.5 rounded-full transition-all duration-500 ease-out';
                                iconEl.className = 'w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0';
                                iconEl.innerHTML = '<span class="icon-[material-symbols-light--progress-activity] w-5 h-5 text-blue-600 animate-spin"></span>';
                            }

                            if (!processing) {
                                setTimeout(() => {
                                    toastEl.classList.add('translate-x-[120%]', 'opacity-0');
                                }, 3500);
                            }
                        }
                    })
                    .catch(() => { });
            }

            pollInterval = setInterval(doPoll, POLL_DELAY);
        })();
    </script>
@endpush