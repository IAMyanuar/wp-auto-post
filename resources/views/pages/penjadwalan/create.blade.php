<div id="create-modal"
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300"
    aria-hidden="true">

    <!-- Backdrop -->
    <div id="create-modal-backdrop" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
        onclick="closeCreateModal()">
    </div>

    <!-- Panel -->
    <div id="create-modal-panel" style="max-width: 600px" class="relative w-full bg-gray-50 rounded-3xl shadow-2xl overflow-hidden flex flex-col
               max-h-[90vh] translate-y-6 scale-[0.97] transition-all duration-300 ease-out">

        <!-- Gradient Header  -->
        <div
            class="flex-shrink-0 bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] px-7 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center flex-shrink-0 ring-1 ring-white/20">
                    <span class="icon-[material-symbols-light--add-circle-outline] w-6 h-6 text-white block"></span>
                </div>
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">Buat List Artikel</h2>
                    <p class="text-gray-300 text-xs mt-0.5">Sistem akan membuat list artikel berdasarkan topik yang
                        diberikan.</p>
                </div>
            </div>
            <button onclick="closeCreateModal()"
                class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center flex-shrink-0 transition-colors">
                <span class="icon-[material-symbols-light--close] w-5 h-5 text-white block"></span>
            </button>
        </div>

        <!-- Form  -->
        <form id="create-modal-form" class="flex flex-col flex-1 overflow-hidden" onsubmit="submitCreateModal(event)">
            @csrf

            <div class="flex-1 overflow-y-auto px-7 py-7 space-y-6">
                <div class="space-y-2">
                    <label
                        class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest px-1">
                        Prompt / Topik Konten <span class="text-red-400">*</span>
                    </label>
                    <textarea name="topik_konten" id="create-topik-konten" rows="4"
                        class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-5 py-3.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-medium shadow-sm resize-none"
                        placeholder="Contoh: Tulis artikel tentang manfaat tanaman hias untuk kesehatan mental..."></textarea>
                    <p id="create-error-topik_konten" class="hidden text-xs text-red-500 mt-1 font-medium px-1"></p>
                </div>


                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest px-1">
                            Jumlah Konten <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <select name="jumlah_konten" id="create-jumlah-konten"
                                class="w-full appearance-none bg-gray-50 border border-gray-200 rounded-2xl px-5 py-3.5 pr-12 text-sm text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all cursor-pointer shadow-sm">
                                <option value="">— Pilih Jumlah —</option>
                                <option value="1">1 Artikel</option>
                                <option value="3">3 Artikel</option>
                                <option value="7">7 Artikel</option>
                            </select>
                            <span
                                class="icon-[material-symbols-light--keyboard-arrow-down] w-6 h-6 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none group-hover:text-purple-500 transition-colors"></span>
                        </div>
                        <p id="create-error-jumlah_konten" class="hidden text-xs text-red-500 font-medium px-1"></p>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest px-1">
                            Website Tujuan <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <select name="website_klien_id" id="create-website"
                                class="w-full appearance-none bg-gray-50 border border-gray-200 rounded-2xl px-5 py-3.5 pr-12 text-sm text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all cursor-pointer shadow-sm">
                                <option value="">— Pilih Website —</option>
                                @foreach(\App\Models\WebsiteKlien::all() as $website)
                                    <option value="{{ $website->id }}">{{ $website->nama_website }}</option>
                                @endforeach
                            </select>
                            <span
                                class="icon-[material-symbols-light--keyboard-arrow-down] w-6 h-6 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none group-hover:text-blue-500 transition-colors"></span>
                        </div>
                        <p id="create-error-website_klien_id" class="hidden text-xs text-red-500 font-medium px-1"></p>
                    </div>

                </div>

                <div
                    class="flex items-center justify-between p-5 bg-gradient-to-r from-white to-gray-50 rounded-2xl border border-gray-200 shadow-sm group hover:border-[#86c84c]/30 transition-all">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-10 h-10 rounded-xl bg-[#86c84c]/10 flex items-center justify-center text-[#86c84c]">
                            <span class="icon-[material-symbols-light--campaign-outline] w-6 h-6"></span>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Sertakan Call to Action</p>
                            <p class="text-[11px] text-gray-400 font-medium leading-tight mt-0.5">No. WA & Alamat akan
                                diambil otomatis dari data pelanggan.</p>
                        </div>
                    </div>
                    <label class="inline-flex items-center cursor-pointer scale-110">
                        <input type="checkbox" name="use_cta" id="create-use-cta" value="1" class="sr-only peer">
                        <div
                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#86c84c] shadow-inner">
                        </div>
                    </label>
                </div>

            </div>

            <div
                class="flex-shrink-0 px-7 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-3xl">
                <button type="submit" id="create-submit-btn"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] rounded-xl hover:from-[#1e1b4b] hover:via-[#0d0d0d] hover:to-[#1e1b4b] active:scale-[0.98] transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                    <span id="create-submit-icon"
                        class="icon-[material-symbols-light--add-circle-outline] w-4 h-4 block"></span>
                    <span id="create-submit-text">Buat Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        function openCreateModal() {
            document.getElementById('create-modal-form').reset();

            document.querySelectorAll('[id^="create-error-"]').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });

            const modal = document.getElementById('create-modal');
            const panel = document.getElementById('create-modal-panel');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {
                panel.classList.remove('translate-y-6', 'scale-[0.97]');
            });

            setTimeout(() => document.getElementById('create-topik-konten').focus(), 80);
        }

        function closeCreateModal() {
            const modal = document.getElementById('create-modal');
            const panel = document.getElementById('create-modal-panel');

            panel.classList.add('translate-y-6', 'scale-[0.97]');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        /* ─────────────────────────────────────────────
           State untuk WebSocket listener agar bisa
           di-teardown setelah menerima event pertama
        ───────────────────────────────────────────── */
        let _judulEchoListeners = null;   // { judulChannel, fallbackTimer }

        function _teardownJudulListeners() {
            if (!_judulEchoListeners) return;

            const { judulChannel, fallbackTimer } = _judulEchoListeners;

            if (fallbackTimer) clearTimeout(fallbackTimer);

            if (window.Echo && judulChannel) {
                // Lepas semua listener pada channel penjadwalan
                window.Echo.channel('penjadwalan')
                    .stopListening('.JudulArtikelTersimpan')
                    .stopListening('.N8nTimeoutDetected');
            }

            _judulEchoListeners = null;
        }

        /**
         * Dipanggil setelah backend sukses menerima generateJudul().
         * Fungsi ini:
         *  1. Menutup modal
         *  2. Menampilkan toast "sedang diproses"
         *  3. Subscribe channel Reverb untuk menunggu hasil
         *  4. Fallback 90s jika tidak ada event sama sekali
         */
        function _subscribeJudulChannel() {
            if (!window.Echo) {
                showToast('WebSocket tidak tersedia. Refresh halaman secara manual.', 'warning');
                setTimeout(() => window.location.reload(), 3000);
                return;
            }

            showToast('N8N sedang memproses... Menunggu hasil judul artikel.', 'info');

            const channel = window.Echo.channel('penjadwalan');

            // Fallback: jika 90 detik tidak ada event, tampil peringatan
            const fallbackTimer = setTimeout(() => {
                _teardownJudulListeners();
                showToast(
                    'Tidak ada respon dari N8N setelah 90 detik. Cek koneksi N8N atau reload halaman.',
                    'error'
                );
                resetCreateSubmitBtn();
            }, 90_000);

            _judulEchoListeners = { judulChannel: channel, fallbackTimer };

            // ① N8N berhasil → judul tersimpan
            channel.listen('.JudulArtikelTersimpan', (e) => {
                _teardownJudulListeners();
                showToast('Judul artikel berhasil dibuat oleh N8N!', 'success');
                setTimeout(() => window.location.reload(), 1200);
            });

            // ② Timeout terdeteksi → tampil detail status n8n
            channel.listen('.N8nTimeoutDetected', (e) => {
                _teardownJudulListeners();
                resetCreateSubmitBtn();

                const statusLabel = e.n8n_status
                    ? ` [Status N8N: ${e.n8n_status}]`
                    : '';

                showToast(
                    `⚠️ ${e.message}${statusLabel}`,
                    'error',
                    8000  // tampilkan lebih lama (8 detik)
                );
            });
        }

        function submitCreateModal(e) {
            e.preventDefault();

            document.querySelectorAll('[id^="create-error-"]').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });

            const btn = document.getElementById('create-submit-btn');
            const icon = document.getElementById('create-submit-icon');
            const text = document.getElementById('create-submit-text');

            btn.disabled = true;
            icon.className = 'w-4 h-4 block border-2 border-white/30 border-t-white rounded-full animate-spin';
            text.textContent = 'Mengirim ke N8N...';

            const form = document.getElementById('create-modal-form');
            const formData = new FormData(form);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value;

            fetch('{{ route("penjadwalan.generate-judul") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData,
            })
                .then(async r => {
                    if (!r.ok) {
                        const data = await r.json().catch(() => null);
                        throw { status: r.status, data };
                    }
                    return r.json();
                })
                .then(data => {
                    if (data.success) {
                        // Tutup modal, lalu tunggu WebSocket event
                        closeCreateModal();
                        _subscribeJudulChannel();
                        // Spinner tetap aktif selama menunggu WebSocket
                    } else {
                        showToast(data.message || 'Gagal menyimpan.', 'error');
                        resetCreateSubmitBtn();
                    }
                })
                .catch(err => {
                    resetCreateSubmitBtn();
                    if (err.status === 422 && err.data?.errors) {
                        for (const field in err.data.errors) {
                            const errorEl = document.getElementById(`create-error-${field}`);
                            if (errorEl) {
                                errorEl.textContent = err.data.errors[field][0];
                                errorEl.classList.remove('hidden');
                            }
                        }
                        showToast('Terdapat kesalahan pada form', 'error');
                    } else {
                        showToast(err.data?.message || 'Terjadi kesalahan sistem, coba lagi.', 'error');
                    }
                });
        }

        function resetCreateSubmitBtn() {
            const btn = document.getElementById('create-submit-btn');
            btn.disabled = false;
            document.getElementById('create-submit-icon').className = 'icon-[material-symbols-light--add-circle-outline] w-4 h-4 block';
            document.getElementById('create-submit-text').textContent = 'Simpan Jadwal';
        }

        // Close on Escape
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && typeof closeCreateModal === 'function') {
                const modalOpen = !document.getElementById('create-modal').classList.contains('opacity-0');
                if (modalOpen) closeCreateModal();
            }
        });
    </script>
@endpush