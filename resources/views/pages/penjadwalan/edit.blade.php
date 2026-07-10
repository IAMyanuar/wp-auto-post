@extends('layout.master')

@section('title', 'Edit Detail Artikel')
@section('page_title', 'Edit Detail Artikel')

@section('content')
    <form action="{{ route('penjadwalan.update', $artikel->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @if(request()->query('from') === 'riwayat')
            <input type="hidden" name="from" value="riwayat">
        @endif

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
                        <a href="{{ request()->query('from') === 'riwayat' ? route('riwayat.index') : route('penjadwalan.index') }}"
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Artikel</label>
                            <input type="text" name="judul"
                                class="w-full bg-gray-50/50 border border-gray-200 rounded-lg px-4 py-2.5 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm @error('judul') border-red-400 @enderror"
                                value="{{ old('judul', $artikel->judul) }}">
                            @error('judul')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Website --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Website Tujuan</label>
                            <input type="hidden" name="website_klien_id" value="{{ $artikel->website_klien_id }}">
                            <input type="text" readonly
                                class="w-full bg-gray-100 border border-gray-200 rounded-lg px-4 py-2.5 text-sm text-gray-600 cursor-not-allowed outline-none shadow-sm"
                                value="{{ optional($artikel->websiteKlien)->nama_website ?? '-' }}">
                        </div>

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
                @else
                    <input type="hidden" name="tanggal_jadwal"
                        value="{{ $artikel->tanggal_jadwal ? $artikel->tanggal_jadwal->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}">
                @endif

                @if(!empty($artikel->wp_id))
                    @php
                        $isWpDraft = str_contains($artikel->wp_url ?? '', '?p=') || $artikel->status === 'terjadwal';
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                            <div class="bg-purple-100 text-purple-600 p-2 rounded-lg flex items-center justify-center">
                                <span class="icon-[material-symbols-light--language] w-5 h-5 block"></span>
                            </div>
                            <h3 class="font-semibold text-gray-800 text-base">Status di WordPress</h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Status Publikasi WP</label>
                                <select name="wp_status"
                                    class="w-full bg-gray-50/50 border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 focus:bg-white transition-all shadow-sm">
                                    <option value="publish" {{ !$isWpDraft ? 'selected' : '' }}>Publish (Terbit di WP)</option>
                                    <option value="draft" {{ $isWpDraft ? 'selected' : '' }}>Draft (Ubah ke Draf di WP)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1.5">Pilih status publikasi artikel pada situs WordPress (Publish atau Draft).</p>
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

                {{-- Card: Status Plagiasi (Uniqtext) --}}
                @php
                    $cekTerakhir = $artikel->cekDuplikasiTerakhir;
                    $skor = $cekTerakhir ? $cekTerakhir->skor_keunikan : null;
                    $isUnique = $skor !== null && $skor >= 50;
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg flex items-center justify-center">
                                <span class="icon-[material-symbols-light--verified-user-outline] w-5 h-5 block"></span>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 text-base">Cek Plagiasi (Uniqtext)</h3>
                            </div>
                        </div>
                        <button type="button" id="btn-cek-plagiasi"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border border-indigo-200 text-xs font-bold rounded-xl cursor-pointer transition-all shadow-2xs">
                            Cek Keunikan
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        @if($cekTerakhir)
                            <div class="flex items-center justify-between p-3.5 rounded-xl border {{ $isUnique ? 'bg-emerald-50/60 border-emerald-200' : 'bg-red-50/60 border-red-200' }}">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Skor Keunikan</p>
                                    <p class="text-2xl font-black {{ $isUnique ? 'text-emerald-700' : 'text-red-700' }} mt-0.5">
                                        {{ $skor }}%
                                        <span class="text-xs font-medium {{ $isUnique ? 'text-emerald-600' : 'text-red-600' }}">
                                            (Duplikat {{ 100 - $skor }}%)
                                        </span>
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $isUnique ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-red-100 text-red-800 border border-red-300' }}">
                                    {{ $isUnique ? 'Aman (≤ 50%)' : 'Duplikat (> 50%)' }}
                                </span>
                            </div>

                            <div class="text-xs text-gray-500 flex items-center justify-between">
                                <span>Percobaan Ke: <strong class="text-gray-700">{{ $cekTerakhir->percobaan_ke }} dari Max 3</strong></span>
                                <span>Waktu: <strong class="text-gray-700">{{ $cekTerakhir->created_at->diffForHumans() }}</strong></span>
                            </div>

                            @if(is_array($cekTerakhir->hasil) && count($cekTerakhir->hasil) > 0)
                                <div class="border-t border-gray-100 pt-3">
                                    <p class="text-xs font-bold text-gray-700 mb-2">Sumber Duplikat Terdeteksi:</p>
                                    <ul class="space-y-2 max-h-48 overflow-y-auto text-xs pr-1">
                                        @foreach($cekTerakhir->hasil as $dup)
                                            <li class="p-2.5 bg-gray-50 rounded-lg border border-gray-200/60">
                                                <a href="{{ $dup['link'] ?? '#' }}" target="_blank" class="font-semibold text-blue-600 hover:underline block truncate">
                                                    {{ $dup['title'] ?? ($dup['link'] ?? 'Sumber Duplikat') }}
                                                </a>
                                                <span class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-700 font-bold rounded text-[10px]">
                                                    Kemiripan: {{ $dup['percent_dup'] ?? 0 }}%
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @else
                            <div class="p-4 text-center border border-dashed border-gray-200 rounded-xl bg-gray-50/50">
                                <p class="text-xs text-gray-500 italic">Belum ada riwayat pengecekan keunikan artikel ini.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="bg-orange-100 text-orange-600 p-2 rounded-lg flex items-center justify-center">
                                <span class="icon-[material-symbols-light--image-outline] w-5 h-5 block"></span>
                            </div>
                            <h3 class="font-semibold text-gray-800 text-base">Gambar</h3>
                        </div>
                        <label
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 hover:bg-orange-100 text-orange-600 border border-orange-200 text-xs font-bold rounded-xl cursor-pointer transition-all shadow-2xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            <span
                                id="btn-image-label">{{ $artikel->gambars->isNotEmpty() ? 'Ubah Gambar' : 'Tambah Gambar' }}</span>
                            <input type="file" name="gambar" accept="image/*" class="hidden"
                                onchange="previewSelectedImages(this)">
                        </label>
                    </div>
                    <div class="p-6 space-y-4">
                        <div id="new-images-preview" class="hidden">
                            <div id="preview-thumbnails" class="grid grid-cols-1 sm:grid-cols-2 gap-3"></div>
                        </div>

                        @forelse($artikel->gambars as $gambar)
                            <div
                                class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl bg-white shadow-sm existing-image-item">
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
                            <p class="text-sm text-gray-400 text-center py-4 italic existing-image-item">Belum ada gambar yang
                                diupload.
                            </p>
                        @endforelse
                    </div>
                </div>
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

        function previewSelectedImages(input) {
            const container = document.getElementById('new-images-preview');
            const thumbnails = document.getElementById('preview-thumbnails');
            const btnLabel = document.getElementById('btn-image-label');
            thumbnails.innerHTML = '';

            if (input.files && input.files.length > 0) {
                if (btnLabel) btnLabel.innerText = 'Ubah Gambar';
                container.classList.remove('hidden');
                document.querySelectorAll('.existing-image-item').forEach(el => el.classList.add('hidden'));
                Array.from(input.files).forEach((file) => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const div = document.createElement('div');
                        div.className = 'flex items-center gap-3 p-2.5 bg-white border border-orange-200 rounded-xl shadow-2xs overflow-hidden';
                        div.innerHTML = `
                                                                        <div class="w-12 h-12 rounded-lg bg-gray-100 flex-shrink-0 overflow-hidden border border-gray-200">
                                                                            <img src="${e.target.result}" class="w-full h-full object-cover">
                                                                        </div>
                                                                        <div class="flex-1 min-w-0">
                                                                            <p class="text-xs font-semibold text-gray-800 truncate" title="${file.name}">${file.name}</p>
                                                                        </div>
                                                                    `;
                        thumbnails.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                });
            } else {
                if (btnLabel) btnLabel.innerText = '{{ $artikel->gambars->isNotEmpty() ? "Ubah Gambar" : "Tambah Gambar" }}';
                container.classList.add('hidden');
                document.querySelectorAll('.existing-image-item').forEach(el => el.classList.remove('hidden'));
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const btnCekPlagiasi = document.getElementById('btn-cek-plagiasi');
            if (btnCekPlagiasi) {
                btnCekPlagiasi.addEventListener('click', function () {
                    btnCekPlagiasi.disabled = true;
                    btnCekPlagiasi.innerHTML = '<span class="icon-[svg-spinners--180-ring] w-4 h-4 animate-spin inline-block mr-1"></span> Mengecek...';

                    fetch("{{ route('penjadwalan.cek-plagiasi', $artikel->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        btnCekPlagiasi.disabled = false;
                        btnCekPlagiasi.innerText = 'Cek Keunikan';
                        if (data.success) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Selesai!',
                                    text: 'Pengecekan plagiasi berhasil dilakukan. Halaman akan dimuat ulang...',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => window.location.reload());
                            } else {
                                alert('Pengecekan plagiasi berhasil!');
                                window.location.reload();
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: data.message || 'Terjadi kesalahan saat memeriksa plagiasi.'
                                });
                            } else {
                                alert(data.message || 'Terjadi kesalahan saat memeriksa plagiasi.');
                            }
                        }
                    })
                    .catch(error => {
                        btnCekPlagiasi.disabled = false;
                        btnCekPlagiasi.innerText = 'Cek Keunikan';
                        alert('Terjadi kesalahan jaringan saat melakukan pengecekan.');
                    });
                });
            }
        });
    </script>
@endpush