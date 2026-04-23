@extends('layout.master')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Selamat Datang</h1>
            <p class="text-sm text-gray-500 mt-1">Berikut ringkasan aktivitas artikel Anda hari ini.</p>
        </div>
    </div>


    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        <div
            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                <span class="icon-[material-symbols-light--article-outline] w-6 h-6 text-gray-700"></span>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Artikel</p>
                <p class="text-2xl font-black text-gray-900 mt-0.5">{{ $totalArtikel }}</p>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <span class="icon-[material-symbols-light--calendar-clock-outline] w-6 h-6 text-blue-600"></span>
            </div>
            <div>
                <p class="text-xs text-blue-600 font-medium uppercase tracking-wide">Terjadwal</p>
                <p class="text-2xl font-black text-blue-700 mt-0.5">{{ $terjadwal }}</p>
            </div>
        </div>


        <div
            class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <span class="icon-[material-symbols-light--check-circle-outline] w-6 h-6 text-emerald-600"></span>
            </div>
            <div>
                <p class="text-xs text-emerald-600 font-medium uppercase tracking-wide">Terpublish</p>
                <p class="text-2xl font-black text-emerald-700 mt-0.5">{{ $terpublish }}</p>
            </div>
        </div>

        <div
            class="bg-white rounded-2xl border border-red-100 shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                <span class="icon-[material-symbols-light--error-outline-rounded] w-6 h-6 text-red-500"></span>
            </div>
            <div>
                <p class="text-xs text-red-500 font-medium uppercase tracking-wide">Gagal</p>
                <p class="text-2xl font-black text-red-600 mt-0.5">{{ $gagal }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
                        <span class="icon-[material-symbols-light--calendar-month-outline] w-5 h-5 block"></span>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Jadwal Artikel</h3>
                        <p class="text-xs text-gray-500">Klik event untuk melihat detail</p>
                    </div>
                </div>

                <div class="hidden sm:flex items-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>Terjadwal</span>
                    <span class="flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>Terpublish</span>
                    <span class="flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>Diproses</span>
                    <span class="flex items-center gap-1.5"><span
                            class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>Gagal</span>
                </div>
            </div>

            <div class="p-5">
                <div id="dashboard-calendar"></div>
            </div>
        </div>

        <div class="space-y-5">

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="icon-[material-symbols-light--analytics-outline] w-5 h-5 text-purple-600"></span>
                        <h4 class="font-bold text-gray-800 text-sm">Rata-rata Skor SEO</h4>
                    </div>
                </div>
                @php $avg = round($avgSeo ?? 0); @endphp
                <div class="flex items-end gap-3">
                    <span
                        class="text-5xl font-black {{ $avg >= 70 ? 'text-emerald-600' : ($avg >= 50 ? 'text-orange-500' : 'text-red-500') }}">
                        {{ $avg }}
                    </span>
                    <span class="text-gray-400 text-sm mb-2">/100</span>
                </div>
                <div class="mt-3 h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-700 {{ $avg >= 70 ? 'bg-emerald-500' : ($avg >= 50 ? 'bg-orange-400' : 'bg-red-400') }}"
                        style="width: {{ $avg }}%">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Berdasarkan artikel dengan data SEO tersedia</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center gap-2 mb-3">
                    <span class="icon-[material-symbols-light--language] w-5 h-5 text-cyan-600"></span>
                    <h4 class="font-bold text-gray-800 text-sm">Website Terhubung</h4>
                </div>
                <p class="text-4xl font-black text-gray-900">{{ $totalWebsite }}</p>
                <a href="{{ route('web-client.index') }}"
                    class="text-xs text-cyan-600 hover:underline mt-1 inline-block">Kelola Website →</a>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
                    <span class="icon-[material-symbols-light--history] w-5 h-5 text-gray-600"></span>
                    <h4 class="font-bold text-gray-800 text-sm">Aktivitas Terbaru</h4>
                </div>
                <ul class="divide-y divide-gray-50">
                    @forelse($artikelTerbaru as $a)
                        @php
                            $statusConf = [
                                'terjadwal' => ['bg-blue-100', 'text-blue-700', 'Terjadwal'],
                                'terpublish' => ['bg-emerald-100', 'text-emerald-700', 'Terpublish'],
                                'gagal' => ['bg-red-100', 'text-red-700', 'Gagal'],
                                'diproses' => ['bg-amber-100', 'text-amber-700', 'Diproses'],
                            ];
                            [$sbg, $stxt, $slabel] = $statusConf[$a->status] ?? ['bg-gray-100', 'text-gray-600', $a->status];
                        @endphp
                        <li class="px-5 py-3 flex items-start gap-3 hover:bg-gray-50/50 transition-colors">
                            <div class="mt-0.5 flex-shrink-0">
                                @if($a->status === 'terpublish')
                                    <span
                                        class="icon-[material-symbols-light--check-circle-outline] w-4 h-4 text-emerald-500"></span>
                                @elseif($a->status === 'gagal')
                                    <span class="icon-[material-symbols-light--error-outline] w-4 h-4 text-red-500"></span>
                                @elseif($a->status === 'diproses')
                                    <span class="icon-[material-symbols-light--avg-pace-outline] w-4 h-4 text-amber-500"></span>
                                @else
                                    <span class="icon-[material-symbols-light--schedule-outline] w-4 h-4 text-blue-500"></span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('penjadwalan.edit', $a->id) }}"
                                    class="text-sm font-semibold text-gray-800 truncate block hover:text-black"
                                    title="{{ $a->judul }}">
                                    {{ Str::limit($a->judul, 40) }}
                                </a>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $a->websiteKlien->nama_website ?? '-' }} &bull; {{ $a->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $sbg }} {{ $stxt }}">
                                {{ $slabel }}
                            </span>
                        </li>
                    @empty
                        <li class="px-5 py-8 text-center text-sm text-gray-400">Belum ada aktivitas.</li>
                    @endforelse
                </ul>
                @if($artikelTerbaru->count())
                    <div class="px-5 py-3 border-t border-gray-100 text-center">
                        <a href="{{ route('penjadwalan.index') }}"
                            class="text-xs font-bold text-gray-500 hover:text-black transition-colors">
                            Lihat Semua →
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <div id="event-popup"
        class="hidden fixed z-50 bg-white rounded-2xl shadow-xl border border-gray-200 p-4 w-72 transition-all duration-200"
        style="display:none">
        <div class="flex items-start justify-between mb-2">
            <h5 id="popup-title" class="text-sm font-bold text-gray-900 leading-snug pr-2"></h5>
            <button onclick="document.getElementById('event-popup').style.display='none'"
                class="text-gray-400 hover:text-gray-700 flex-shrink-0">
                <span class="icon-[material-symbols-light--close] w-4 h-4 block"></span>
            </button>
        </div>
        <p id="popup-status" class="text-xs font-bold mb-3"></p>
        <div class="flex gap-2">
            <a id="popup-edit" href="#"
                class="flex-1 text-center text-xs font-bold bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-white py-2 rounded-lg hover:bg-gray-800 transition-colors">
                Edit Artikel
            </a>
            <a id="popup-wp" href="#" target="_blank"
                class="flex-1 text-center text-xs font-bold border border-gray-200 text-gray-700 py-2 rounded-lg hover:bg-gray-50 transition-colors"
                style="display:none">
                Buka di WP
            </a>
        </div>
    </div>

    <div id="fc-tooltip" style="display:none;position:fixed;z-index:9999;pointer-events:none;"
        class="bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-white text-xs rounded-xl shadow-2xl px-4 py-3 w-64">
        <p id="fc-tooltip-website" class="font-bold text-blue-300 mb-1 truncate"></p>
        <p id="fc-tooltip-title" class="font-semibold text-white leading-snug mb-2"></p>
        <div class="flex items-center gap-1.5 text-gray-300">
            <span class="icon-[material-symbols-light--nest-clock-farsight-analog-rounded] w-3.5 h-3.5"></span>
            <span id="fc-tooltip-time"></span>
        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('vendor/fullcalendar/core.global.min.js') }}"></script>
    <script src="{{ asset('vendor/fullcalendar/daygrid.global.min.js') }}"></script>
    <script src="{{ asset('vendor/fullcalendar/timegrid.global.min.js') }}"></script>
    <script src="{{ asset('vendor/fullcalendar/list.global.min.js') }}"></script>
    <script src="{{ asset('vendor/fullcalendar/interaction.global.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('dashboard-calendar');
            if (!calendarEl) { console.error('Calendar element not found'); return; }
            if (typeof FullCalendar === 'undefined') { console.error('FullCalendar not loaded'); return; }

            const events = @json($calendarEvents);
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridMonth,listWeek',
                },
                buttonText: {
                    month: 'Bulan',
                    list: 'Daftar',
                },
                height: 600,
                events: events,
                displayEventTime: false,
                eventDisplay: 'block',
                dayMaxEvents: 3,
                eventMouseEnter: function (info) {
                    const props = info.event.extendedProps;
                    const tooltip = document.getElementById('fc-tooltip');
                    document.getElementById('fc-tooltip-website').textContent = props.website || '-';
                    document.getElementById('fc-tooltip-title').textContent = info.event.title;
                    document.getElementById('fc-tooltip-time').textContent = props.tanggalLengkap || props.jamPublish || '-';

                    const rect = info.el.getBoundingClientRect();
                    let left = rect.left;
                    let top = rect.bottom + 8;
                    // Stay within viewport
                    if (left + 260 > window.innerWidth - 12) left = window.innerWidth - 272;
                    if (top + 110 > window.innerHeight) top = rect.top - 118;

                    tooltip.style.left = left + 'px';
                    tooltip.style.top = top + 'px';
                    tooltip.style.display = 'block';
                },
                eventMouseLeave: function () {
                    document.getElementById('fc-tooltip').style.display = 'none';
                },
                eventClick: function (info) {
                    info.jsEvent.preventDefault();
                    const props = info.event.extendedProps;
                    const statusMap = {
                        terjadwal: ['Terjadwal', 'color:#2563eb'],
                        terpublish: ['Terpublish ✓', 'color:#059669'],
                        gagal: ['Gagal ✗', 'color:#dc2626'],
                        diproses: ['Sedang Diproses…', 'color:#d97706'],
                    };
                    const [label, style] = statusMap[props.status] ?? ['Unknown', 'color:#6b7280'];

                    const popup = document.getElementById('event-popup');
                    const titleEl = document.getElementById('popup-title');
                    const statusEl = document.getElementById('popup-status');
                    const editBtn = document.getElementById('popup-edit');
                    const wpBtn = document.getElementById('popup-wp');

                    titleEl.textContent = info.event.title;
                    statusEl.textContent = label;
                    statusEl.style.cssText = `font-size:0.75rem;font-weight:700;margin-bottom:0.75rem;${style}`;
                    editBtn.href = props.editUrl;

                    if (props.url) {
                        wpBtn.href = props.url;
                        wpBtn.style.display = '';
                    } else {
                        wpBtn.style.display = 'none';
                    }

                    const rect = info.el.getBoundingClientRect();
                    const popupW = 288;
                    let left = rect.left + window.scrollX;
                    let top = rect.bottom + window.scrollY + 8;
                    if (left + popupW > window.innerWidth - 16) left = window.innerWidth - popupW - 16;

                    popup.style.left = left + 'px';
                    popup.style.top = top + 'px';
                    popup.style.display = 'block';
                },
            });

            try {
                calendar.render();
                console.log('[FullCalendar] rendered, events:', events.length);
            } catch (e) {
                console.error('[FullCalendar] render error:', e);
            }

            document.addEventListener('click', function (e) {
                const popup = document.getElementById('event-popup');
                if (popup && !popup.contains(e.target) && !e.target.closest('.fc-event')) {
                    popup.style.display = 'none';
                }
            });
        });
    </script>

    <style>
        .fc {
            font-family: inherit;
        }

        .fc .fc-toolbar-title {
            font-size: 1rem;
            font-weight: 800;
            color: #111827;
        }

        .fc .fc-button {
            background: #fff !important;
            border: 1px solid #e5e7eb !important;
            color: #374151 !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            border-radius: 0.6rem !important;
            padding: 0.35rem 0.75rem !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.15s;
        }

        .fc .fc-button:hover {
            background: #f9fafb !important;
            border-color: #d1d5db !important;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background: #111827 !important;
            border-color: #111827 !important;
            color: #fff !important;
        }

        .fc .fc-today-button {
            background: #f3f4f6 !important;
        }

        /* Day headers */
        .fc .fc-col-header-cell-cushion {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #6b7280;
            padding: 0.6rem 0;
            text-decoration: none !important;
        }

        /* Day numbers */
        .fc .fc-daygrid-day-number {
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            text-decoration: none !important;
            padding: 6px 8px;
        }

        .fc .fc-day-today .fc-daygrid-day-number {
            background: #111827;
            color: #fff;
            border-radius: 99px;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fc .fc-day-today {
            background: #f8fafc !important;
        }

        /* Events */
        .fc .fc-event {
            border-radius: 6px !important;
            font-size: 0.72rem !important;
            font-weight: 600 !important;
            padding: 2px 6px !important;
            cursor: pointer;
            border: none !important;
        }

        .fc .fc-event:hover {
            opacity: 0.85;
        }

        /* Grid lines */
        .fc .fc-scrollgrid {
            border-color: #f3f4f6 !important;
            border-radius: 12px;
            overflow: hidden;
        }

        .fc td,
        .fc th {
            border-color: #f3f4f6 !important;
        }

        /* List view */
        .fc .fc-list-event:hover td {
            background: #f9fafb !important;
        }

        .fc .fc-list-day-cushion {
            background: #f3f4f6 !important;
            font-size: 0.75rem;
            font-weight: 700;
        }
    </style>
@endpush