@extends('layout.master')

@section('title', 'Edit Detail Artikel')
@section('page_title', 'Edit Detail Artikel')

@section('content')
    <form action="{{ route('penjadwalan.update', $artikel->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            {{-- Main Form Column --}}
            <div class="xl:col-span-2 space-y-6">
                @php
                    $statusConfig = [
                        'diproses' => ['label' => 'Diproses API', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-400'],
                        'terjadwal' => ['label' => 'Terjadwal', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'dot' => 'bg-blue-500'],
                        'terpublish' => ['label' => 'Sudah Terbit', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-500'],
                        'gagal' => ['label' => 'Gagal', 'bg' => 'bg-red-50', 'text' => 'text-red-700', 'border' => 'border-red-200', 'dot' => 'bg-red-500'],
                    ];
                    $currStatus = $statusConfig[$artikel->status] ?? $statusConfig['gagal'];
                @endphp

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Edit Detail Artikel</h2>
                            <p class="text-gray-500 text-sm mt-0.5">Sesuaikan konten dan pengaturan publikasi.</p>
                        </div>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-bold rounded-full border {{ $currStatus['bg'] }} {{ $currStatus['text'] }} {{ $currStatus['border'] }} uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full {{ $currStatus['dot'] }}"></span>
                            {{ $currStatus['label'] }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('penjadwalan.index') }}"
                            class="text-sm font-bold border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl transition-all shadow-sm">
                            Batal
                        </a>
                    </div>
                </div>



                {{-- Card: Detail & Konten Artikel --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="bg-blue-100 text-blue-600 p-2 rounded-lg flex items-center justify-center">
                            <span class="icon-[material-symbols-light--edit-document-outline] w-5 h-5 block"></span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 text-base">Konten Artikel</h3>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Judul --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="judul"
                                class="w-full bg-gray-50/50 border border-gray-200 rounded-lg px-4 py-2.5 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm @error('judul') border-red-400 @enderror"
                                value="{{ old('judul', $artikel->judul) }}">
                            @error('judul')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        @if($artikel->status !== 'terpublish')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                {{-- Website --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Website Tujuan <span
                                            class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select name="website_klien_id"
                                            class="w-full appearance-none bg-gray-50/50 border border-gray-200 rounded-lg px-4 py-2.5 pr-10 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm">
                                            @foreach($websites as $website)
                                                <option value="{{ $website->id }}" {{ old('website_klien_id', $artikel->website_klien_id) == $website->id ? 'selected' : '' }}>
                                                    {{ $website->nama_website }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span
                                                class="icon-[material-symbols-light--keyboard-arrow-down] w-5 h-5 text-gray-400"></span>
                                        </div>
                                    </div>
                                </div>
                                {{-- Template Prompt --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Template Prompt <span
                                            class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <select name="ai_agent_prompt_id"
                                            class="w-full appearance-none bg-gray-50/50 border border-gray-200 rounded-lg px-4 py-2.5 pr-10 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm">
                                            @foreach($prompts as $prompt)
                                                <option value="{{ $prompt->id }}" {{ old('ai_agent_prompt_id', $artikel->ai_agent_prompt_id) == $prompt->id ? 'selected' : '' }}>
                                                    {{ $prompt->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span
                                                class="icon-[material-symbols-light--keyboard-arrow-down] w-5 h-5 text-gray-400"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="website_klien_id" value="{{ $artikel->website_klien_id }}">
                            <input type="hidden" name="ai_agent_prompt_id" value="{{ $artikel->ai_agent_prompt_id }}">
                        @endif

                        <hr class="border-gray-100">

                        {{-- Kategori & Tags --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                <input type="text" name="kategori" value="{{ old('kategori', $artikel->kategori) }}"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white shadow-sm transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                                <input type="text" name="tags" value="{{ old('tags', $artikel->tags) }}"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white shadow-sm transition-all">
                            </div>
                        </div>

                        {{-- Kata Kunci --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kata Kunci (Focus Keyword)</label>
                            <input type="text" name="kata_kunci" value="{{ old('kata_kunci', $artikel->kata_kunci) }}"
                                class="w-full bg-gray-50/50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white shadow-sm transition-all">
                        </div>

                        {{-- Meta Deskripsi --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Meta Deskripsi</label>
                            <textarea name="meta_deskripsi" rows="3"
                                class="w-full bg-gray-50/50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white shadow-sm transition-all">{{ old('meta_deskripsi', $artikel->meta_deskripsi) }}</textarea>
                        </div>

                        {{-- Konten (TinyMCE) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Isi Konten</label>
                            <p class="text-xs text-gray-500 mb-3 block">Gunakan editor di bawah untuk menyesuaikan artikel
                                yang ter-generate.</p>
                            <textarea id="konten" name="konten"
                                class="w-full bg-white">{{ old('konten', $artikel->konten) }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Sidebar Column --}}
            <div class="space-y-6">
                {{-- Penjadwalan & Status --}}
                {{-- Card: Penjadwalan (Hanya muncul jika belum terpublish) --}}
                @if($artikel->status !== 'terpublish')
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <div class="bg-green-100 text-green-600 p-2 rounded-lg flex items-center justify-center">
                                <span class="icon-[material-symbols-light--calendar-clock-outline] w-5 h-5 block"></span>
                            </div>
                            <h3 class="font-semibold text-gray-800 text-base">Penjadwalan</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal & Waktu Publish <span
                                        class="text-red-500">*</span></label>
                                <input type="datetime-local" name="tanggal_jadwal"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all shadow-sm"
                                    value="{{ old('tanggal_jadwal', $artikel->tanggal_jadwal ? $artikel->tanggal_jadwal->format('Y-m-d\TH:i') : '') }}">
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Action Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6">
                        <button type="submit"
                            class="w-full px-5 py-3 text-sm font-bold text-white bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] rounded-xl hover:bg-gray-800 transition-all shadow-sm active:scale-[0.98] flex items-center justify-center gap-2">
                            <span class="icon-[material-symbols-light--save-outline] w-5 h-5"></span>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>

                {{-- Gambar Tersimpan --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                        <div class="bg-orange-100 text-orange-600 p-2 rounded-lg flex items-center justify-center">
                            <span class="icon-[material-symbols-light--image-outline] w-5 h-5 block"></span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800 text-base">Gambar</h3>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        @forelse($artikel->gambars as $gambar)
                            <div class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl bg-white shadow-sm">
                                <div
                                    class="w-16 h-16 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden border border-gray-200">
                                    <img src="{{ $gambar->wp_media_url ?? Storage::url($gambar->path) }}" alt="Preview"
                                        class="w-full h-full object-cover"
                                        onerror="this.onerror=null; this.src='https://placehold.co/200x200?text=Error';">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate" title="{{ $gambar->nama_gambar }}">
                                        {{ $gambar->nama_gambar }}
                                    </p>
                                    @if($gambar->is_featured)
                                        <span
                                            class="inline-block mt-1 px-2 py-0.5 bg-yellow-100 text-yellow-700 text-[10px] font-bold rounded-full uppercase tracking-wider">Featured
                                            Image</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4 italic">Belum ada gambar yang diupload ke database.
                            </p>
                        @endforelse
                    </div>
                </div>

                {{-- Card: Analisis Yoast SEO (Moved to Sidebar) --}}
                @php
                    $rawYoast = trim((string) $artikel->deskripsi_yoast);
                    $yoastData = [];

                    if (!empty($rawYoast)) {
                        if ($rawYoast[0] !== '{' && $rawYoast[0] !== '[') {
                            $rawYoast = '{' . $rawYoast;
                        }

                        $decoded = json_decode($rawYoast, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $suffixes = ['}', ']}', ']} }', ']} ]} }', '"]}', '"]}}', '"]}]}'];
                            foreach ($suffixes as $suffix) {
                                $attempt = json_decode($rawYoast . $suffix, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $decoded = $attempt;
                                    break;
                                }
                            }
                        }

                        if (is_string($decoded)) {
                            $decoded = json_decode($decoded, true);
                        }

                        $yoastData = is_array($decoded) ? $decoded : [];
                    }

                    $seoData = $yoastData['seo'] ?? [];
                    $readabilityData = $yoastData['readability'] ?? [];

                    // Helper function to render items
                    $renderYoastItems = function ($items, $bgClass) {
                        if (empty($items))
                            return '';
                        $html = '<ul class="space-y-3 mt-3">';
                        foreach ($items as $item) {
                            $parts = explode(':', $item, 2);
                            $formattedText = count($parts) == 2
                                ? "<span class='font-semibold text-gray-800'>{$parts[0]}:</span>" . $parts[1]
                                : $item;

                            $html .= '<li class="flex items-start gap-2.5 text-sm text-gray-600">';
                            $html .= '<span class="mt-1 flex-shrink-0 w-2.5 h-2.5 rounded-full ' . $bgClass . '"></span>';
                            $html .= '<p class="leading-relaxed">' . $formattedText . '</p>';
                            $html .= '</li>';
                        }
                        $html .= '</ul>';
                        return $html;
                    };
                @endphp

                @if(!empty($seoData) || !empty($readabilityData))
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="bg-purple-100 text-purple-600 p-2 rounded-lg">
                                    <span class="icon-[material-symbols-light--analytics-outline] w-5 h-5 block"></span>
                                </div>
                                <h3 class="font-bold text-gray-800 text-base">Analisis Yoast</h3>
                            </div>
                        </div>

                        <div class="p-6 space-y-8 divide-y divide-gray-100">
                            {{-- SEO Section --}}
                            <div class="pt-0">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                        <span
                                            class="icon-[material-symbols-light--travel-explore] w-5 h-5 text-gray-500"></span>
                                        SEO
                                    </h4>
                                    <span
                                        class="px-2.5 py-1 rounded-lg text-sm font-black border {{ $artikel->skor_seo >= 80 ? 'bg-green-50 text-green-700 border-green-200' : ($artikel->skor_seo >= 50 ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-red-50 text-red-700 border-red-200') }}">
                                        {{ $artikel->skor_seo ?? 0 }}<span
                                            class="text-[10px] opacity-60 font-normal">/100</span>
                                    </span>
                                </div>

                                @if(!empty($seoData['problems']))
                                    <div class="mb-4">
                                        <h5 class="text-[11px] font-bold text-red-600 uppercase tracking-wider mb-2">Problems</h5>
                                        {!! $renderYoastItems($seoData['problems'], 'bg-red-500') !!}
                                    </div>
                                @endif

                                @if(!empty($seoData['improvements']))
                                    <div class="mb-4">
                                        <h5 class="text-[11px] font-bold text-orange-600 uppercase tracking-wider mb-2">Improvements
                                        </h5>
                                        {!! $renderYoastItems($seoData['improvements'], 'bg-orange-500') !!}
                                    </div>
                                @endif

                                @if(!empty($seoData['good']))
                                    <div>
                                        <h5 class="text-[11px] font-bold text-green-600 uppercase tracking-wider mb-2">Good Results
                                        </h5>
                                        {!! $renderYoastItems($seoData['good'], 'bg-green-500') !!}
                                    </div>
                                @endif
                            </div>

                            {{-- Readability Section --}}
                            <div class="pt-8">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                        <span
                                            class="icon-[material-symbols-light--menu-book-outline] w-5 h-5 text-gray-500"></span>
                                        Keterbacaan
                                    </h4>
                                    <span
                                        class="px-2.5 py-1 rounded-lg text-sm font-black border {{ $artikel->skor_readability >= 80 ? 'bg-green-50 text-green-700 border-green-200' : ($artikel->skor_readability >= 50 ? 'bg-orange-50 text-orange-700 border-orange-200' : 'bg-red-50 text-red-700 border-red-200') }}">
                                        {{ $artikel->skor_readability ?? 0 }}<span
                                            class="text-[10px] opacity-60 font-normal">/100</span>
                                    </span>
                                </div>

                                @if(!empty($readabilityData['problems']))
                                    <div class="mb-4">
                                        <h5 class="text-[11px] font-bold text-red-600 uppercase tracking-wider mb-2">Problems</h5>
                                        {!! $renderYoastItems($readabilityData['problems'], 'bg-red-500') !!}
                                    </div>
                                @endif

                                @if(!empty($readabilityData['improvements']))
                                    <div class="mb-4">
                                        <h5 class="text-[11px] font-bold text-orange-600 uppercase tracking-wider mb-2">Improvements
                                        </h5>
                                        {!! $renderYoastItems($readabilityData['improvements'], 'bg-orange-500') !!}
                                    </div>
                                @endif

                                @if(!empty($readabilityData['good']))
                                    <div>
                                        <h5 class="text-[11px] font-bold text-green-600 uppercase tracking-wider mb-2">Good Results
                                        </h5>
                                        {!! $renderYoastItems($readabilityData['good'], 'bg-green-500') !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    {{-- Load file TinyMCE secara LOKAL dari folder public/vendor/tinymce/ --}}
    <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#konten',
                    height: 1000,
                    min_height: 500,
                    menubar: true,
                    promotion: false,
                    branding: false,
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                        'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                    ],
                    toolbar: 'undo redo | blocks | ' +
                        'bold italic forecolor backcolor | alignleft aligncenter ' +
                        'alignright alignjustify | bullist numlist outdent indent | ' +
                        'removeformat | link image | code fullscreen help',
                    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 15px; }',
                    setup: function (editor) {
                        editor.on('change', function () {
                            tinymce.triggerSave();
                        });
                    }
                });
            } else {
                console.warn('TinyMCE script gagal dimuat. Pastikan Anda telah meletakkan file TinyMCE di folder aplikasi Anda pada path yang benar (public/vendor/tinymce/tinymce.min.js).');
            }
        });
    </script>
@endpush