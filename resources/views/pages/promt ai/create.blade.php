{{-- ===================== CREATE MODAL ===================== --}}
<div id="create-modal"
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300"
    aria-hidden="true">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeCreateModal()"></div>

    {{-- Panel --}}
    <div id="create-modal-panel" class="relative w-full max-w-3xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col
               max-h-[88vh] translate-y-6 scale-[0.97] transition-all duration-300 ease-out">

        {{-- Header --}}
        <div
            class="flex-shrink-0 bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] px-7 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center flex-shrink-0 ring-1 ring-white/20">
                    <span class="icon-[material-symbols-light--add-circle-outline] w-5 h-5 text-white block"></span>
                </div>
                <div>
                    <h2 class="text-white font-bold text-base leading-tight">Tambah Template Prompt</h2>
                    <p class="text-gray-400 text-xs mt-0.5">Buat template format konten baru untuk AI</p>
                </div>
            </div>
            <button onclick="closeCreateModal()"
                class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center flex-shrink-0 transition-colors">
                <span class="icon-[material-symbols-light--close] w-5 h-5 text-white block"></span>
            </button>
        </div>

        {{-- Form --}}
        <form id="create-modal-form" class="flex flex-col flex-1 overflow-hidden" onsubmit="submitCreateModal(event)">
            @csrf

            {{-- Scrollable body --}}
            <div class="flex-1 overflow-y-auto px-7 py-5 space-y-5">

                {{-- Nama Template --}}
                <div>
                    <label for="create-name" class="block text-sm font-semibold text-gray-800 mb-2">
                        Nama Template <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="create-name" name="name"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400
                               focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all"
                        placeholder="Contoh: Template Blog SEO">
                    <p id="create-name-error" class="hidden text-xs text-red-500 mt-1.5">
                        <span class="create-name-msg"></span>
                    </p>
                </div>

                {{-- Isi Prompt --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="create-prompt" class="block text-sm font-semibold text-gray-800">
                            Isi Prompt <span class="text-red-500">*</span>
                        </label>
                        <span id="create-char-count" class="text-xs text-gray-400 tabular-nums">0 karakter</span>
                    </div>
                    <textarea id="create-prompt" name="prompt" rows="8" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400
                               focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all
                               leading-relaxed resize-none font-mono"
                        placeholder="Contoh:&#10;Tulis artikel SEO dengan struktur:&#10;- Intro 2 paragraf&#10;- 3 subjudul H2 (min. 150 kata)&#10;- Penutup dengan CTA&#10;Gaya: santai namun informatif. Total: 1000–1200 kata."
                        oninput="updateCreateCharCount(this)"></textarea>
                    <p id="create-prompt-error" class="hidden text-xs text-red-500 mt-1.5">
                        <span class="create-prompt-msg"></span>
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div
                class="flex-shrink-0 px-7 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <button type="submit" id="create-submit-btn"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] rounded-xl
                           hover:bg-gray-700 active:scale-[0.98] transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                    <span id="create-submit-icon"
                        class="icon-[material-symbols-light--add-circle-outline] w-4 h-4 block"></span>
                    <span id="create-submit-text">Simpan Template</span>
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
            document.getElementById('create-char-count').textContent = '0 karakter';
            clearCreateErrors();

            const modal = document.getElementById('create-modal');
            const panel = document.getElementById('create-modal-panel');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {
                panel.classList.remove('translate-y-6', 'scale-[0.97]');
            });

            setTimeout(() => document.getElementById('create-name').focus(), 80);
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
            clearCreateErrors();

            const name = document.getElementById('create-name').value.trim();
            const prompt = document.getElementById('create-prompt').value.trim();
            let valid = true;

            if (!name) {
                document.getElementById('create-name-error').classList.remove('hidden');
                document.getElementById('create-name-error').querySelector('.create-name-msg').textContent = 'Nama template wajib diisi.';
                document.getElementById('create-name').classList.add('border-red-400');
                valid = false;
            }
            if (!prompt) {
                document.getElementById('create-prompt-error').classList.remove('hidden');
                document.getElementById('create-prompt-error').querySelector('.create-prompt-msg').textContent = 'Isi prompt wajib diisi.';
                document.getElementById('create-prompt').classList.add('border-red-400');
                valid = false;
            }
            if (!valid) return;

            const btn = document.getElementById('create-submit-btn');
            const icon = document.getElementById('create-submit-icon');
            const text = document.getElementById('create-submit-text');
            btn.disabled = true;
            icon.className = 'w-4 h-4 block border-2 border-white/30 border-t-white rounded-full animate-spin';
            text.textContent = 'Menyimpan...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || '{{ csrf_token() }}';

            const storeUrl = '{{ route("ai-prompt.store") }}';

            fetch(storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ name, prompt }),
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeCreateModal();
                        showToast(data.message || 'Template berhasil ditambahkan!', 'success');
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        if (data.errors) {
                            if (data.errors.name) {
                                document.getElementById('create-name-error').classList.remove('hidden');
                                document.getElementById('create-name-error').querySelector('.create-name-msg').textContent = data.errors.name[0];
                                document.getElementById('create-name').classList.add('border-red-400');
                            }
                            if (data.errors.prompt) {
                                document.getElementById('create-prompt-error').classList.remove('hidden');
                                document.getElementById('create-prompt-error').querySelector('.create-prompt-msg').textContent = data.errors.prompt[0];
                                document.getElementById('create-prompt').classList.add('border-red-400');
                            }
                        }
                        resetCreateBtn();
                    }
                })
                .catch(() => {
                    showToast('Terjadi kesalahan. Coba lagi.', 'error');
                    resetCreateBtn();
                });
        }

        function resetCreateBtn() {
            document.getElementById('create-submit-btn').disabled = false;
            document.getElementById('create-submit-icon').className = 'icon-[material-symbols-light--add-circle-outline] w-4 h-4 block';
            document.getElementById('create-submit-text').textContent = 'Simpan Template';
        }

        function clearCreateErrors() {
            document.getElementById('create-name-error').classList.add('hidden');
            document.getElementById('create-prompt-error').classList.add('hidden');
            document.getElementById('create-name').classList.remove('border-red-400');
            document.getElementById('create-prompt').classList.remove('border-red-400');
        }

        function updateCreateCharCount(textarea) {
            document.getElementById('create-char-count').textContent =
                textarea.value.length.toLocaleString('id-ID') + ' karakter';
        }
    </script>
@endpush