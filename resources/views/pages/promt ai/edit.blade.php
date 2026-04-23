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
                    <span class="icon-[material-symbols-light--smart-toy-outline] w-5 h-5 text-white block"></span>
                </div>
                <div>
                    <h2 class="text-white font-bold text-base leading-tight">Edit Template Prompt</h2>
                    <p class="text-gray-400 text-xs mt-0.5">Perubahan berlaku langsung pada penjadwalan terkait</p>
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
            <input type="hidden" id="modal-prompt-id" name="prompt_id">

            {{-- Scrollable body --}}
            <div class="flex-1 overflow-y-auto px-7 py-5 space-y-5">

                {{-- Nama Template --}}
                <div>
                    <label for="modal-name" class="block text-sm font-semibold text-gray-800 mb-2">
                        Nama Template <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="modal-name" name="name"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400
                               focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all"
                        placeholder="Contoh: Template Blog SEO">
                    <p id="modal-name-error" class="hidden text-xs text-red-500 mt-1.5">
                        <span class="name-msg"></span>
                    </p>
                </div>

                {{-- Isi Prompt --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="modal-prompt" class="block text-sm font-semibold text-gray-800">
                            Isi Prompt <span class="text-red-500">*</span>
                        </label>
                        <span id="modal-char-count" class="text-xs text-gray-400 tabular-nums">0 karakter</span>
                    </div>
                    <textarea id="modal-prompt" name="prompt" rows="8" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400
                               focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all
                               leading-relaxed resize-none font-mono"
                        placeholder="Tulis instruksi prompt untuk AI di sini..."
                        oninput="updateCharCount(this)"></textarea>
                    <p id="modal-prompt-error" class="hidden text-xs text-red-500 mt-1.5">
                        <span class="prompt-msg"></span>
                    </p>
                </div>
            </div>

            {{-- Footer (fixed, tidak ikut scroll) --}}
            <div
                class="flex-shrink-0 px-7 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="submit" id="modal-submit-btn"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] rounded-xl
                           hover:bg-gray-700 active:scale-[0.98] transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                    <span id="modal-submit-icon"
                        class="icon-[material-symbols-light--save-outline] w-4 h-4 block"></span>
                    <span id="modal-submit-text">Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>


{{-- Toast Notification --}}
<div id="toast-notification" class="fixed top-6 right-6 z-[1000] flex items-center gap-3 px-5 py-3.5 bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-white text-sm font-medium
           rounded-2xl shadow-2xl opacity-0 -translate-y-4 pointer-events-none transition-all duration-300">
    <span id="toast-icon"
        class="w-5 h-5 text-emerald-400 flex-shrink-0 block icon-[material-symbols-light--check-circle-outline]"></span>
    <span id="toast-text"></span>
</div>

@push('scripts')
    <script>
        let currentUpdateUrl = '';
        function openEditModal(id, editUrl, updateUrl) {
            currentUpdateUrl = updateUrl;

            // Reset state
            const form = document.getElementById('edit-modal-form');
            form.classList.add('hidden');
            form.classList.remove('flex');
            document.getElementById('modal-loading').classList.remove('hidden');
            clearModalErrors();

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
                    document.getElementById('modal-prompt-id').value = data.id;
                    document.getElementById('modal-name').value = data.name;

                    const promptField = document.getElementById('modal-prompt');
                    promptField.value = data.prompt;
                    updateCharCount(promptField);

                    document.getElementById('modal-loading').classList.add('hidden');
                    form.classList.remove('hidden');
                    form.classList.add('flex');

                    setTimeout(() => document.getElementById('modal-name').focus(), 80);
                })
                .catch(() => {
                    closeEditModal();
                    showToast('Gagal memuat data prompt.', 'error');
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
            clearModalErrors();

            const name = document.getElementById('modal-name').value.trim();
            const prompt = document.getElementById('modal-prompt').value.trim();
            let valid = true;

            if (!name) {
                document.getElementById('modal-name-error').classList.remove('hidden');
                document.getElementById('modal-name-error').querySelector('.name-msg').textContent = 'Nama template wajib diisi.';
                document.getElementById('modal-name').classList.add('border-red-400');
                valid = false;
            }
            if (!prompt) {
                document.getElementById('modal-prompt-error').classList.remove('hidden');
                document.getElementById('modal-prompt-error').querySelector('.prompt-msg').textContent = 'Isi prompt wajib diisi.';
                document.getElementById('modal-prompt').classList.add('border-red-400');
                valid = false;
            }
            if (!valid) return;

            // Loading state on button
            const btn = document.getElementById('modal-submit-btn');
            const icon = document.getElementById('modal-submit-icon');
            const text = document.getElementById('modal-submit-text');
            btn.disabled = true;
            icon.className = 'w-4 h-4 block border-2 border-white/30 border-t-white rounded-full animate-spin';
            text.textContent = 'Menyimpan...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || '{{ csrf_token() }}';

            fetch(currentUpdateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ _method: 'PUT', name, prompt }),
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeEditModal();
                        showToast(data.message || 'Template berhasil diperbarui!', 'success');
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        if (data.errors) {
                            if (data.errors.name) {
                                document.getElementById('modal-name-error').classList.remove('hidden');
                                document.getElementById('modal-name-error').querySelector('.name-msg').textContent = data.errors.name[0];
                                document.getElementById('modal-name').classList.add('border-red-400');
                            }
                            if (data.errors.prompt) {
                                document.getElementById('modal-prompt-error').classList.remove('hidden');
                                document.getElementById('modal-prompt-error').querySelector('.prompt-msg').textContent = data.errors.prompt[0];
                                document.getElementById('modal-prompt').classList.add('border-red-400');
                            }
                        }
                        resetSubmitBtn();
                    }
                })
                .catch(() => {
                    showToast('Terjadi kesalahan. Coba lagi.', 'error');
                    resetSubmitBtn();
                });
        }

        function resetSubmitBtn() {
            const btn = document.getElementById('modal-submit-btn');
            btn.disabled = false;
            document.getElementById('modal-submit-icon').className = 'icon-[material-symbols-light--save-outline] w-4 h-4 block';
            document.getElementById('modal-submit-text').textContent = 'Simpan Perubahan';
        }

        function clearModalErrors() {
            document.getElementById('modal-name-error').classList.add('hidden');
            document.getElementById('modal-prompt-error').classList.add('hidden');
            document.getElementById('modal-name').classList.remove('border-red-400');
            document.getElementById('modal-prompt').classList.remove('border-red-400');
        }

        function updateCharCount(textarea) {
            document.getElementById('modal-char-count').textContent =
                textarea.value.length.toLocaleString('id-ID') + ' karakter';
        }

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

        // Close on Escape
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeEditModal();
                if (typeof closeCreateModal === 'function') closeCreateModal();
            }
        });
    </script>
@endpush