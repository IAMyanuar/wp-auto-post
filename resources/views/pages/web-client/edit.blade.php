<div id="edit-modal"
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300"
    aria-hidden="true">

    {{-- Backdrop --}}
    <div id="edit-modal-backdrop" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeEditModal()">
    </div>

    {{-- Panel --}}
    <div id="edit-modal-panel" class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col
               max-h-[88vh] translate-y-6 scale-[0.97] transition-all duration-300 ease-out">

        {{-- Gradient Header (fixed, tidak ikut scroll) --}}
        <div
            class="flex-shrink-0 bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] px-7 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center flex-shrink-0 ring-1 ring-white/20">
                    <span class="icon-[material-symbols-light--edit-square-outline] w-5 h-5 text-white block"></span>
                </div>
                <div>
                    <h2 class="text-white font-bold text-base leading-tight">Edit Web Client</h2>
                    <p class="text-gray-400 text-xs mt-0.5">Perbarui profil dan kunci API</p>
                </div>
            </div>
            <button onclick="closeEditModal()"
                class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center flex-shrink-0 transition-colors">
                <span class="icon-[material-symbols-light--close] w-5 h-5 text-white block"></span>
            </button>
        </div>

        {{-- Loading State --}}
        <div id="modal-loading" class="flex-1 flex flex-col items-center justify-center gap-3 py-10">
            <div class="w-9 h-9 border-4 border-gray-200 border-t-gray-800 rounded-full animate-spin"></div>
            <p class="text-sm text-gray-500">Memuat data...</p>
        </div>

        {{-- Form (scrollable) --}}
        <form id="edit-modal-form" class="hidden flex-1 flex flex-col overflow-hidden"
            onsubmit="submitEditModal(event)">
            @csrf
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" id="modal-client-id" name="client_id">

            {{-- Scrollable body --}}
            <div class="flex-1 overflow-y-auto px-7 py-5 space-y-5">

                <div class="space-y-4">
                    <div>
                        <label for="modal-nama-website" class="block text-sm font-semibold text-gray-800 mb-1">
                            Nama Website <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="modal-nama-website" name="nama_website" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                        <p id="modal-error-nama" class="hidden text-xs text-red-500 mt-1.5"></p>
                    </div>

                    <div>
                        <label for="modal-url-website" class="block text-sm font-semibold text-gray-800 mb-1">
                            URL Website <span class="text-red-500">*</span>
                        </label>
                        <input type="url" id="modal-url-website" name="url_website" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                        <p id="modal-error-url" class="hidden text-xs text-red-500 mt-1.5"></p>
                    </div>

                    <div>
                        <label for="modal-username" class="block text-sm font-semibold text-gray-800 mb-1">
                            Username WordPress <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="modal-username" name="username" required
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                        <p id="modal-error-username" class="hidden text-xs text-red-500 mt-1.5"></p>
                    </div>

                    <div>
                        <label for="modal-password" class="block text-sm font-semibold text-gray-800 mb-1">
                            Application Password <span class="text-xs font-normal text-gray-500 ml-1">(Kosongkan jika
                                tidak diubah)</span>
                        </label>
                        <input type="password" id="modal-password" name="password"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-5 space-y-4">
                    <h3 class="text-sm font-bold text-gray-800">Call to Action</h3>

                    <div>
                        <label for="modal-no-telpon" class="block text-sm font-semibold text-gray-800 mb-1">
                            No. WhatsApp / Telepon <span
                                class="text-xs font-normal text-gray-500 ml-1">(Opsional)</span>
                        </label>
                        <input type="text" id="modal-no-telpon" name="no_telpon" placeholder="Contoh: 081234567890"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all">
                    </div>

                    <div>
                        <label for="modal-alamat" class="block text-sm font-semibold text-gray-800 mb-1">
                            Alamat Bisnis / Info Promosi <span
                                class="text-xs font-normal text-gray-500 ml-1">(Opsional)</span>
                        </label>
                        <textarea id="modal-alamat" name="alamat" rows="2" placeholder="Contoh: Jl. Sudirman No 1..."
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all resize-none shadow-sm"></textarea>
                    </div>
                </div>

                <!-- Toggle Auto Publish -->
                <div class="flex items-center pt-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="modal-publikasi-otomatis" name="publikasi_otomatis" value="1"
                            class="sr-only peer">
                        <div
                            class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#86c84c]">
                        </div>
                        <span class="ms-3 text-sm font-medium text-gray-700">Auto Publish Artikel</span>
                    </label>
                </div>

            </div>

            {{-- Footer (fixed, tidak ikut scroll) --}}
            <div
                class="flex-shrink-0 px-7 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="submit" id="modal-submit-btn"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] rounded-xl hover:bg-gray-700 active:scale-[0.98] transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                    <span id="modal-submit-icon"
                        class="icon-[material-symbols-light--save-outline] w-4 h-4 block"></span>
                    <span id="modal-submit-text">Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        let currentClientUpdateUrl = '';

        function openEditModal(clientId, editUrl, updateUrl) {
            currentClientUpdateUrl = updateUrl;

            // Reset state
            const form = document.getElementById('edit-modal-form');
            form.classList.add('hidden');
            form.classList.remove('flex');
            document.getElementById('modal-loading').classList.remove('hidden');

            // Hide errors
            document.querySelectorAll('[id^="modal-error-"]').forEach(el => el.classList.add('hidden'));

            // Show modal
            const modal = document.getElementById('edit-modal');
            const panel = document.getElementById('edit-modal-panel');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {
                panel.classList.remove('translate-y-6', 'scale-[0.97]');
            });

            // Fetch data via AJAX
            fetch(editUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('modal-client-id').value = data.id;
                    document.getElementById('modal-nama-website').value = data.nama_website || '';
                    document.getElementById('modal-url-website').value = data.url_website || '';
                    document.getElementById('modal-username').value = data.username || '';
                    document.getElementById('modal-password').value = '';
                    document.getElementById('modal-no-telpon').value = data.no_telpon || '';
                    document.getElementById('modal-alamat').value = data.alamat || '';
                    document.getElementById('modal-publikasi-otomatis').checked = !!data.publikasi_otomatis;

                    document.getElementById('modal-loading').classList.add('hidden');
                    form.classList.remove('hidden');
                    form.classList.add('flex');

                    setTimeout(() => document.getElementById('modal-nama-website').focus(), 80);
                })
                .catch(err => {
                    closeEditModal();
                    showToast('Gagal memuat data.', 'error');
                });
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-modal');
            const panel = document.getElementById('edit-modal-panel');

            panel.classList.add('translate-y-6', 'scale-[0.97]');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function submitEditModal(e) {
            e.preventDefault();

            document.querySelectorAll('[id^="modal-error-"]').forEach(el => el.classList.add('hidden'));

            const btn = document.getElementById('modal-submit-btn');
            const icon = document.getElementById('modal-submit-icon');
            const text = document.getElementById('modal-submit-text');
            btn.disabled = true;
            icon.className = 'w-4 h-4 block border-2 border-white/30 border-t-white rounded-full animate-spin';
            text.textContent = 'Menyimpan...';

            const formData = new FormData(document.getElementById('edit-modal-form'));
            formData.set('publikasi_otomatis', document.getElementById('modal-publikasi-otomatis').checked ? 1 : 0);

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value;

            fetch(currentClientUpdateUrl, {
                method: 'POST', // using POST with _method=PUT from form
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
                        closeEditModal();
                        showToast(data.message || 'Berhasil disimpan', 'success');
                        setTimeout(() => window.location.reload(), 1200);
                    }
                })
                .catch(err => {
                    resetSubmitBtn();
                    if (err.status === 422 && err.data) {
                        if (err.data.errors) {
                            for (const field in err.data.errors) {
                                const errorEl = document.getElementById(`modal-error-${field.replace('_', '-')}`);
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

        function resetSubmitBtn() {
            const btn = document.getElementById('modal-submit-btn');
            btn.disabled = false;
            document.getElementById('modal-submit-icon').className = 'icon-[material-symbols-light--save-outline] w-4 h-4 block';
            document.getElementById('modal-submit-text').textContent = 'Simpan Perubahan';
        }

        // Close on Escape
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>
@endpush