<div id="create-modal"
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300"
    aria-hidden="true">

    {{-- Backdrop --}}
    <div id="create-modal-backdrop" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
        onclick="closeCreateModal()">
    </div>

    {{-- Panel --}}
    <div id="create-modal-panel" class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col
               max-h-[88vh] translate-y-6 scale-[0.97] transition-all duration-300 ease-out">

        {{-- Gradient Header (fixed, tidak ikut scroll) --}}
        <div
            class="flex-shrink-0 bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] px-7 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center flex-shrink-0 ring-1 ring-white/20">
                    <span class="icon-[material-symbols-light--add-circle-outline] w-5 h-5 text-white block"></span>
                </div>
                <div>
                    <h2 class="text-white font-bold text-base leading-tight">Tambah Web Client</h2>
                    <p class="text-gray-400 text-xs mt-0.5">Tambahkan Web Client (WordPress) baru</p>
                </div>
            </div>
            <button onclick="closeCreateModal()"
                class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center flex-shrink-0 transition-colors">
                <span class="icon-[material-symbols-light--close] w-5 h-5 text-white block"></span>
            </button>
        </div>

        {{-- Form (scrollable) --}}
        <form id="create-modal-form" class="flex flex-col flex-1 overflow-hidden" onsubmit="submitCreateModal(event)">
            @csrf

            {{-- Scrollable body --}}
            <div class="flex-1 overflow-y-auto px-7 py-5 space-y-5">

                <div class="space-y-4">
                    <div>
                        <label for="create-nama-website" class="block text-sm font-semibold text-gray-800 mb-1">
                            Nama Website <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="create-nama-website" name="nama_website" required
                            placeholder="Contoh: Blog Pribadi"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                        <p id="create-error-nama" class="hidden text-xs text-red-500 mt-1.5"></p>
                    </div>

                    <div>
                        <label for="create-url-website" class="block text-sm font-semibold text-gray-800 mb-1">
                            URL Website <span class="text-red-500">*</span>
                        </label>
                        <input type="url" id="create-url-website" name="url_website" required
                            placeholder="https://contoh.com"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                        <p id="create-error-url" class="hidden text-xs text-red-500 mt-1.5"></p>
                    </div>

                    <div>
                        <label for="create-username" class="block text-sm font-semibold text-gray-800 mb-1">
                            Username WordPress <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="create-username" name="username" required placeholder="admin"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                        <p id="create-error-username" class="hidden text-xs text-red-500 mt-1.5"></p>
                    </div>

                    <div>
                        <label for="create-password" class="block text-sm font-semibold text-gray-800 mb-1">
                            Application Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="create-password" name="password" required
                            placeholder="xxxx xxxx xxxx xxxx"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                        <p id="create-error-password" class="hidden text-xs text-red-500 mt-1.5"></p>
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1.5">
                            <span
                                class="icon-[material-symbols-light--info-outline] w-3.5 h-3.5 block flex-shrink-0"></span>
                            Gunakan Application Passwords dari profil WP
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5 space-y-4">
                    <h3 class="text-sm font-bold text-gray-800">Call to Action</h3>

                    <div>
                        <label for="create-no-telpon" class="block text-sm font-semibold text-gray-800 mb-1">
                            No. WhatsApp / Telepon <span
                                class="text-xs font-normal text-gray-500 ml-1">(Opsional)</span>
                        </label>
                        <input type="text" id="create-no-telpon" name="no_telpon" placeholder="Contoh: 081234567890"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                    </div>

                    <div>
                        <label for="create-alamat" class="block text-sm font-semibold text-gray-800 mb-1">
                            Alamat Bisnis / Info Promosi <span
                                class="text-xs font-normal text-gray-500 ml-1">(Opsional)</span>
                        </label>
                        <textarea id="create-alamat" name="alamat" rows="2" placeholder="Contoh: Jl. Sudirman No 1..."
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all resize-none shadow-sm"></textarea>
                    </div>
                </div>

                <!-- Toggle Auto Publish -->
                <div class="flex items-center pt-2 gap-3 bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    <label class="inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox" id="create-publikasi-otomatis" name="publikasi_otomatis" value="1"
                            class="sr-only peer" checked>
                        <div
                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#86c84c]">
                        </div>
                    </label>
                    <div>
                        <p class="text-sm font-medium text-gray-800">Auto Publish Artikel</p>
                        <p class="text-[11px] text-gray-500 leading-tight mt-0.5">Jika aktif, artikel dari AI langsung
                            terpublikasi tanpa reviu manual.</p>
                    </div>
                </div>

            </div>

            {{-- Footer (fixed, tidak ikut scroll) --}}
            <div
                class="flex-shrink-0 px-7 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="submit" id="create-submit-btn"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] rounded-xl hover:bg-gray-700 active:scale-[0.98] transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                    <span id="create-submit-icon"
                        class="icon-[material-symbols-light--add-circle-outline] w-4 h-4 block"></span>
                    <span id="create-submit-text">Simpan Web Client</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        function openCreateModal() {
            // Reset form
            document.getElementById('create-modal-form').reset();

            // Hide errors
            document.querySelectorAll('[id^="create-error-"]').forEach(el => el.classList.add('hidden'));

            // Show modal
            const modal = document.getElementById('create-modal');
            const panel = document.getElementById('create-modal-panel');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {
                panel.classList.remove('translate-y-6', 'scale-[0.97]');
            });

            setTimeout(() => document.getElementById('create-nama-website').focus(), 80);
        }

        function closeCreateModal() {
            const modal = document.getElementById('create-modal');
            const panel = document.getElementById('create-modal-panel');

            panel.classList.add('translate-y-6', 'scale-[0.97]');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function submitCreateModal(e) {
            e.preventDefault();

            document.querySelectorAll('[id^="create-error-"]').forEach(el => el.classList.add('hidden'));

            const btn = document.getElementById('create-submit-btn');
            const icon = document.getElementById('create-submit-icon');
            const text = document.getElementById('create-submit-text');
            btn.disabled = true;
            icon.className = 'w-4 h-4 block border-2 border-white/30 border-t-white rounded-full animate-spin';
            text.textContent = 'Menyimpan...';

            const formData = new FormData(document.getElementById('create-modal-form'));

            // Ensure checked state is handled as boolean/1/0 depending on backend validation
            if (!document.getElementById('create-publikasi-otomatis').checked) {
                formData.delete('publikasi_otomatis');
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value;

            fetch('{{ route("web-client.store") }}', {
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
                        closeCreateModal();
                        showToast(data.message || 'Berhasil disimpan', 'success');
                        setTimeout(() => window.location.reload(), 1200);
                    }
                })
                .catch(err => {
                    resetCreateSubmitBtn();
                    if (err.status === 422 && err.data) {
                        if (err.data.errors) {
                            for (const field in err.data.errors) {
                                const errorEl = document.getElementById(`create-error-${field.replace('_', '-')}`);
                                if (errorEl) {
                                    errorEl.textContent = err.data.errors[field][0];
                                    errorEl.classList.remove('hidden');
                                }
                            }
                        } else if (err.data.message) {
                            showToast(err.data.message, 'error');
                        } else {
                            showToast('Cek kembali form isian Anda.', 'error');
                        }
                    } else {
                        showToast('Terjadi kesalahan. Coba lagi.', 'error');
                    }
                });
        }

        function resetCreateSubmitBtn() {
            const btn = document.getElementById('create-submit-btn');
            btn.disabled = false;
            document.getElementById('create-submit-icon').className = 'icon-[material-symbols-light--add-circle-outline] w-4 h-4 block';
            document.getElementById('create-submit-text').textContent = 'Simpan Web Client';
        }

        // Close on Escape - handled by edit.blade.php already
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && typeof closeCreateModal === 'function') {
                closeCreateModal();
            }
        });
    </script>
@endpush