<div id="create-modal"
    class="fixed inset-0 z-[999] flex items-center justify-center p-4 opacity-0 pointer-events-none transition-opacity duration-300"
    aria-hidden="true">

    {{-- Backdrop --}}
    <div id="create-modal-backdrop" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
        onclick="closeCreateModal()">
    </div>

    {{-- Panel --}}
    <div id="create-modal-panel" class="relative w-full max-w-5xl bg-gray-50 rounded-3xl shadow-2xl overflow-hidden flex flex-col
               max-h-[90vh] translate-y-6 scale-[0.97] transition-all duration-300 ease-out">

        {{-- Gradient Header (fixed, tidak ikut scroll) --}}
        <div
            class="flex-shrink-0 bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] px-7 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div
                    class="w-10 h-10 rounded-2xl bg-white/10 backdrop-blur flex items-center justify-center flex-shrink-0 ring-1 ring-white/20">
                    <span class="icon-[material-symbols-light--add-circle-outline] w-6 h-6 text-white block"></span>
                </div>
                <div>
                    <h2 class="text-white font-bold text-lg leading-tight">Buat Jadwal Artikel</h2>
                    <p class="text-gray-300 text-xs mt-0.5">Buat penjadwalan artikel AI baru.</p>
                </div>
            </div>
            <button onclick="closeCreateModal()"
                class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center flex-shrink-0 transition-colors">
                <span class="icon-[material-symbols-light--close] w-5 h-5 text-white block"></span>
            </button>
        </div>

        {{-- Form (scrollable) --}}
        <form id="create-modal-form" class="flex flex-col flex-1 overflow-hidden" onsubmit="submitCreateModal(event)"
            enctype="multipart/form-data">
            @csrf

            {{-- Scrollable body --}}
            <div class="flex-1 overflow-y-auto px-7 py-7 space-y-6">

                {{-- Row 1: Judul --}}
                <div class="space-y-2">
                    <label
                        class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest px-1">
                        <span class="icon-[material-symbols-light--topic-outline] w-4 h-4 text-indigo-500"></span>
                        Judul Artikel <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="judul" id="create-judul"
                        class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-5 py-3.5 text-sm text-gray-800 placeholder-gray-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-medium shadow-sm"
                        placeholder="Contoh: 10 Tips Belajar Coding untuk Pemula">
                    <p id="create-error-judul" class="hidden text-xs text-red-500 mt-1 font-medium px-1"></p>
                </div>

                {{-- Row 2: Website + Prompt --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest px-1">
                            <span class="icon-[material-symbols-light--language] w-4 h-4 text-blue-500"></span>
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

                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest px-1">
                            <span
                                class="icon-[material-symbols-light--smart-toy-outline-rounded] w-4 h-4 text-purple-500"></span>
                            Template Prompt <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <select name="ai_agent_prompt_id" id="create-prompt"
                                class="w-full appearance-none bg-gray-50 border border-gray-200 rounded-2xl px-5 py-3.5 pr-12 text-sm text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all cursor-pointer shadow-sm">
                                <option value="">— Pilih Template —</option>
                                @foreach(\App\Models\AiAgentPrompt::all() as $prompt)
                                    <option value="{{ $prompt->id }}">{{ $prompt->name ?? 'Prompt #' . $prompt->id }}
                                    </option>
                                @endforeach
                            </select>
                            <span
                                class="icon-[material-symbols-light--keyboard-arrow-down] w-6 h-6 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none group-hover:text-purple-500 transition-colors"></span>
                        </div>
                        <p id="create-error-ai_agent_prompt_id" class="hidden text-xs text-red-500 font-medium px-1">
                        </p>
                    </div>
                </div>

                {{-- Row 3: Tipe Publikasi + Tanggal --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label
                            class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest px-1">
                            <span
                                class="icon-[material-symbols-light--rocket-launch-outline] w-4 h-4 text-orange-500"></span>
                            Tipe Publikasi
                        </label>
                        <div class="relative group">
                            <select name="tipe_publikasi" id="create-tipe-publikasi" onchange="toggleCreateTanggal()"
                                class="w-full appearance-none bg-gray-50 border border-gray-200 rounded-2xl px-5 py-3.5 pr-12 text-sm text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all cursor-pointer shadow-sm">
                                <option value="langsung">Langsung Publish</option>
                                <option value="jadwal">Di Jadwalkan</option>
                            </select>
                            <span
                                class="icon-[material-symbols-light--keyboard-arrow-down] w-6 h-6 text-gray-400 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none group-hover:text-orange-500 transition-colors"></span>
                        </div>
                    </div>

                    <div id="create-tanggal-container"
                        class="space-y-2 hidden animate-in fade-in slide-in-from-top-2 duration-300">
                        <label
                            class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-widest px-1">
                            <span
                                class="icon-[material-symbols-light--calendar-month-outline] w-4 h-4 text-emerald-500"></span>
                            Tanggal & Waktu Publish <span class="text-red-400">*</span>
                        </label>
                        <input type="datetime-local" name="tanggal_jadwal" id="create-tanggal-jadwal"
                            class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-5 py-3.5 text-sm text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all shadow-sm">
                        <p id="create-error-tanggal_jadwal" class="hidden text-xs text-red-500 font-medium px-1"></p>
                    </div>
                </div>

                {{-- Divider --}}
                <div class="border-t border-dashed border-gray-200"></div>

                {{-- Row 4: Gambar Upload --}}
                <div class="space-y-4 pt-2">
                    <div class="flex items-center justify-between px-1">
                        <div class="flex items-center gap-2">
                            <span class="icon-[material-symbols-light--image-outline] w-5 h-5 text-indigo-500"></span>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest">Gambar
                                    Artikel</label>
                                <p class="text-[10px] text-gray-400 font-medium">Sisipkan referensi visual untuk AI.</p>
                            </div>
                        </div>
                        <button type="button" onclick="addCreateGambarRow()"
                            class="inline-flex items-center gap-1.5 text-[11px] font-bold text-indigo-600 bg-white hover:bg-indigo-50 border border-indigo-100 px-3 py-2 rounded-xl transition-all shadow-sm active:scale-95">
                            <span class="icon-[material-symbols-light--add-circle-outline] w-4 h-4"></span> Tambah Media
                        </button>
                    </div>
                    <div id="create-gambar-container" class="grid grid-cols-1 gap-3">
                        <div class="gambar-row flex items-center gap-3 animate-in fade-in duration-300">
                            <div class="flex-1 relative">
                                <input type="file" name="gambar_file[]" accept="image/*"
                                    class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-gray-100 file:text-gray-600 hover:file:bg-gray-200 file:cursor-pointer bg-white border border-gray-200 rounded-2xl py-1.5 px-2 shadow-sm focus-within:border-indigo-500 transition-all">
                            </div>
                            <button type="button" onclick="this.closest('.gambar-row').remove()"
                                class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-red-400 hover:bg-red-50 hover:text-red-500 hover:border-red-100 transition-all shadow-sm active:scale-95">
                                <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                            </button>
                        </div>
                    </div>
                    <p id="create-error-gambar_file" class="hidden text-xs text-red-500 font-medium px-1"></p>
                </div>

                {{-- Row 5: Hyperlinks side-by-side --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 pt-2">
                    {{-- Internal --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="icon-[material-symbols-light--link-rounded] w-5 h-5 text-emerald-500"></span>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest">Link
                                        Internal</label>
                                    <p class="text-[10px] text-gray-400 font-medium">Halaman pada domain yang sama.</p>
                                </div>
                            </div>
                            <button type="button" onclick="addCreateInternalRow()"
                                class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 bg-white hover:bg-emerald-50 border border-emerald-100 px-3 py-2 rounded-xl transition-all shadow-sm active:scale-95">
                                <span class="icon-[material-symbols-light--add-link] w-4 h-4"></span> Tambah
                            </button>
                        </div>
                        <div id="create-internal-container" class="space-y-3">
                            <div class="internal-row flex items-center gap-3 animate-in fade-in duration-300">
                                <input type="text" name="internal_url[]"
                                    class="flex-1 bg-white border border-gray-200 rounded-2xl px-5 py-3 text-sm text-gray-800 placeholder-gray-300 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all shadow-sm"
                                    placeholder="/halaman-tujuan">
                                <button type="button" onclick="this.closest('.internal-row').remove()"
                                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-red-400 hover:bg-red-50 hover:text-red-500 hover:border-red-100 transition-all shadow-sm active:scale-95">
                                    <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- External --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between px-1">
                            <div class="flex items-center gap-2">
                                <span class="icon-[material-symbols-light--open-in-new] w-5 h-5 text-orange-500"></span>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest">Link
                                        Eksternal</label>
                                    <p class="text-[10px] text-gray-400 font-medium">Menuju website pihak ketiga.</p>
                                </div>
                            </div>
                            <button type="button" onclick="addCreateExternalRow()"
                                class="inline-flex items-center gap-1.5 text-[11px] font-bold text-orange-600 bg-white hover:bg-orange-50 border border-orange-100 px-3 py-2 rounded-xl transition-all shadow-sm active:scale-95">
                                <span class="icon-[material-symbols-light--add-link] w-4 h-4"></span> Tambah
                            </button>
                        </div>
                        <div id="create-external-container" class="space-y-3">
                            <div class="external-row flex items-center gap-3 animate-in fade-in duration-300">
                                <input type="text" name="external_url[]"
                                    class="flex-1 bg-white border border-gray-200 rounded-2xl px-5 py-3 text-sm text-gray-800 placeholder-gray-300 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all shadow-sm"
                                    placeholder="https://example.com/blog-post">
                                <button type="button" onclick="this.closest('.external-row').remove()"
                                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-red-400 hover:bg-red-50 hover:text-red-500 hover:border-red-100 transition-all shadow-sm active:scale-95">
                                    <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 6: Call to Action Toggle --}}
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

            {{-- Footer (fixed, tidak ikut scroll) --}}
            <div
                class="flex-shrink-0 px-7 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3 rounded-b-3xl">
                <button type="button" onclick="closeCreateModal()"
                    class="px-5 py-2.5 text-sm font-semibold text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-100 transition-all active:scale-[0.98]">
                    Batal
                </button>
                <button type="submit" id="create-submit-btn"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold text-white bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] rounded-xl hover:from-[#1e1b4b] hover:via-[#0d0d0d] hover:to-[#1e1b4b] active:scale-[0.98] transition-all shadow-sm disabled:opacity-60 disabled:cursor-not-allowed">
                    <span id="create-submit-icon"
                        class="icon-[material-symbols-light--add-circle-outline] w-4 h-4 block"></span>
                    <span id="create-submit-text">Simpan Jadwal</span>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        const inputClass = 'w-full bg-white border border-gray-200 rounded-2xl px-5 py-3 text-sm text-gray-800 placeholder-gray-300 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all shadow-sm';
        const fileInputClass = 'w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[11px] file:font-bold file:bg-gray-100 file:text-gray-600 hover:file:bg-gray-200 file:cursor-pointer bg-white border border-gray-200 rounded-2xl py-1.5 px-2 shadow-sm focus-within:border-indigo-500 transition-all';
        const btnRemoveClass = 'w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 text-red-400 hover:bg-red-50 hover:text-red-500 hover:border-red-100 transition-all shadow-sm active:scale-95';

        function openCreateModal() {
            document.getElementById('create-modal-form').reset();

            document.querySelectorAll('[id^="create-error-"]').forEach(el => {
                el.classList.add('hidden');
                el.textContent = '';
            });

            // Clear dynamic rows except the first one
            const imgContainer = document.getElementById('create-gambar-container');
            imgContainer.innerHTML = `
                    <div class="gambar-row flex items-center gap-3 animate-in fade-in duration-300">
                        <div class="flex-1 relative">
                            <input type="file" name="gambar_file[]" accept="image/*" class="${fileInputClass}">
                        </div>
                        <button type="button" onclick="this.closest('.gambar-row').remove()" class="${btnRemoveClass}">
                            <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                        </button>
                    </div>
                `;
            document.getElementById('create-internal-container').innerHTML = `
                    <div class="internal-row flex items-center gap-3 animate-in fade-in duration-300">
                        <input type="text" name="internal_url[]" class="${inputClass}" placeholder="/halaman-tujuan">
                        <button type="button" onclick="this.closest('.internal-row').remove()" class="${btnRemoveClass}">
                            <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                        </button>
                    </div>
                `;
            document.getElementById('create-external-container').innerHTML = `
                    <div class="external-row flex items-center gap-3 animate-in fade-in duration-300">
                        <input type="text" name="external_url[]" class="${inputClass}" placeholder="https://example.com/blog-post">
                        <button type="button" onclick="this.closest('.external-row').remove()" class="${btnRemoveClass}">
                            <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                        </button>
                    </div>
                `;

            toggleCreateTanggal();

            const modal = document.getElementById('create-modal');
            const panel = document.getElementById('create-modal-panel');
            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            requestAnimationFrame(() => {
                panel.classList.remove('translate-y-6', 'scale-[0.97]');
            });

            setTimeout(() => document.getElementById('create-judul').focus(), 80);
        }

        function closeCreateModal() {
            const modal = document.getElementById('create-modal');
            const panel = document.getElementById('create-modal-panel');

            panel.classList.add('translate-y-6', 'scale-[0.97]');
            modal.classList.add('opacity-0', 'pointer-events-none');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        function toggleCreateTanggal() {
            const tipe = document.getElementById('create-tipe-publikasi').value;
            const container = document.getElementById('create-tanggal-container');
            const input = document.getElementById('create-tanggal-jadwal');

            if (tipe === 'jadwal') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                input.value = '';
            }
        }

        function addCreateGambarRow() {
            const container = document.getElementById('create-gambar-container');
            const row = document.createElement('div');
            row.className = 'gambar-row flex items-center gap-3 animate-in fade-in duration-300';
            row.innerHTML = `
                    <div class="flex-1 relative">
                        <input type="file" name="gambar_file[]" accept="image/*" class="${fileInputClass}">
                    </div>
                    <button type="button" onclick="this.closest('.gambar-row').remove()" class="${btnRemoveClass}">
                        <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                    </button>
                `;
            container.appendChild(row);
        }

        function addCreateInternalRow() {
            const container = document.getElementById('create-internal-container');
            const row = document.createElement('div');
            row.className = 'internal-row flex items-center gap-3 animate-in fade-in duration-300';
            row.innerHTML = `
                    <input type="text" name="internal_url[]" class="${inputClass}" placeholder="/halaman-tujuan">
                    <button type="button" onclick="this.closest('.internal-row').remove()" class="${btnRemoveClass}">
                        <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                    </button>
                `;
            container.appendChild(row);
        }

        function addCreateExternalRow() {
            const container = document.getElementById('create-external-container');
            const row = document.createElement('div');
            row.className = 'external-row flex items-center gap-3 animate-in fade-in duration-300';
            row.innerHTML = `
                    <input type="text" name="external_url[]" class="${inputClass}" placeholder="https://example.com/blog-post">
                    <button type="button" onclick="this.closest('.external-row').remove()" class="${btnRemoveClass}">
                        <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                    </button>
                `;
            container.appendChild(row);
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
            text.textContent = 'Menyimpan...';

            const form = document.getElementById('create-modal-form');
            const formData = new FormData(form);

            // Menghapus data empty agar tidak bingung backend
            const fileInputs = form.querySelectorAll('input[type="file"]');
            fileInputs.forEach((input) => {
                if (input.files.length === 0) {
                    // If you want to drop empty inputs to not send them
                    // We'll leave it to Laravel to filter them. 
                    // But appending file data directly using FormData usually works.
                }
            });

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value;

            fetch('{{ route("penjadwalan.store") }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData, // fetch akan meresolusi content-type secara otomatis (multipart/form-data)
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