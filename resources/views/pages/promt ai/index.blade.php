@extends('layout.master')

@section('title', 'AI Agent Prompt')
@section('page_title', 'AI Agent Prompt')

@section('content')
    <div class="overflow-hidden max-w-full">
        {{-- Header --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-gray-500 text-sm leading-relaxed">Kelola template format konten yang digunakan oleh AI saat
                membuat artikel.</p>
            <button type="button" onclick="openCreateModal()"
                class="inline-flex items-center justify-center gap-2 w-full sm:w-auto bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] hover:from-[#1e1b4b] hover:via-[#0d0d0d] hover:to-[#1e1b4b] text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all shadow-sm active:scale-[0.98]">
                <span class="icon-[material-symbols-light--add] w-5 h-5 flex-shrink-0"></span>
                Tambah Prompt
            </button>
        </div>

        {{-- Filter & Table Container --}}
        <div
            class="w-full md:bg-white md:rounded-2xl md:shadow-[0_4px_24px_rgba(0,0,0,0.02)] md:border md:border-gray-100 md:p-6 p-0">

            {{-- Top Controls --}}
            <form method="GET" action="{{ route('ai-prompt.index') }}" class="flex flex-col sm:flex-row gap-3 mb-5">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <span class="icon-[material-symbols-light--search] w-4 h-4 text-gray-400"></span>
                    </div>
                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-sm text-gray-700 placeholder-gray-400"
                        placeholder="Cari nama atau isi prompt...">
                </div>
                <div class="relative w-full sm:w-36 flex-shrink-0">
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

            {{-- Table Desktop --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-white">
                            <th class="py-4 px-5 text-sm font-semibold rounded-tl-xl w-12">No.</th>
                            <th class="py-4 px-5 text-sm font-semibold w-52">Nama Template</th>
                            <th class="py-4 px-5 text-sm font-semibold">Isi Prompt</th>
                            <th class="py-4 px-5 text-sm font-semibold text-right rounded-tr-xl">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prompts as $key => $prompt)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-5 text-sm text-gray-500 font-medium">{{ $prompts->firstItem() + $key }}.</td>
                                <td class="py-4 px-5 text-sm text-gray-800 font-semibold">{{ $prompt->name }}</td>
                                <td class="py-4 px-5 text-sm text-gray-500 max-w-sm">
                                    <p class="line-clamp-2 leading-relaxed">{{ $prompt->prompt }}</p>
                                </td>
                                <td class="py-4 px-5 text-sm text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                            onclick="openEditModal({{ $prompt->id }}, '{{ route('ai-prompt.edit', $prompt->id) }}', '{{ route('ai-prompt.update', $prompt->id) }}')"
                                            class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 text-orange-600 hover:bg-orange-500 hover:text-white transition-colors">
                                            <span class="icon-[material-symbols-light--edit-square-outline] w-5 h-5"></span>
                                        </button>
                                        <form action="{{ route('ai-prompt.destroy', $prompt->id) }}" method="POST"
                                            class="inline-block form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-100 text-red-600 hover:bg-red-600 hover:text-white transition-colors">
                                                <span class="icon-[material-symbols-light--delete-outline] w-5 h-5"></span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-sm text-gray-400">
                                    <span
                                        class="icon-[material-symbols-light--smart-toy-outline] w-10 h-10 mx-auto block mb-2 text-gray-300"></span>
                                    Belum ada template prompt. Silakan tambah yang baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="block lg:hidden space-y-3">
                @forelse($prompts as $key => $prompt)
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden w-full min-w-0">
                        <div class="flex items-start justify-between gap-2 p-4 pb-3">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <div
                                    class="w-7 h-7 rounded-full bg-gradient-to-br from-[#0d0d0d] via-[#1e1b4b] to-[#0d0d0d] text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                                    {{ $prompts->firstItem() + $key }}
                                </div>
                                <h3 class="font-bold text-gray-900 text-sm leading-tight truncate min-w-0">{{ $prompt->name }}
                                </h3>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button"
                                    onclick="openEditModal({{ $prompt->id }}, '{{ route('ai-prompt.edit', $prompt->id) }}', '{{ route('ai-prompt.update', $prompt->id) }}')"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-orange-100 text-orange-600 hover:bg-orange-500 hover:text-white transition-colors">
                                    <span class="icon-[material-symbols-light--edit-square-outline] w-4 h-4"></span>
                                </button>
                                <form action="{{ route('ai-prompt.destroy', $prompt->id) }}" method="POST" class="form-delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-100 text-red-600 hover:bg-red-600 hover:text-white transition-colors">
                                        <span class="icon-[material-symbols-light--delete-outline] w-4 h-4"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="px-4 pb-4 w-full min-w-0 overflow-hidden">
                            <div class="bg-gray-50 rounded-lg border border-gray-100 px-3 py-2 overflow-hidden">
                                <p class="text-xs text-gray-500 leading-relaxed line-clamp-3 break-words overflow-hidden"
                                    style="overflow-wrap: anywhere; word-break: break-all;">
                                    {{ $prompt->prompt }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-sm text-gray-400 bg-gray-50 rounded-xl border border-gray-100">
                        <span
                            class="icon-[material-symbols-light--smart-toy-outline] w-8 h-8 mx-auto block mb-2 text-gray-300"></span>
                        Belum ada template prompt.
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($prompts->hasPages() || $prompts->total() > 0)
                <div class="mt-5 flex flex-col sm:flex-row justify-between items-center gap-3 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 order-2 sm:order-1">
                        Menampilkan <span class="font-semibold text-gray-700">{{ $prompts->firstItem() ?? 0 }}</span>
                        &ndash;
                        <span class="font-semibold text-gray-700">{{ $prompts->lastItem() ?? 0 }}</span>
                        dari <span class="font-semibold text-gray-700">{{ $prompts->total() }}</span> entri
                    </p>
                    <div class="order-1 sm:order-2">
                        {{ $prompts->links('pagination::tailwind') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Edit — seluruh kode modal ada di edit.blade.php --}}
    @include('pages.promt ai.edit')
    @include('pages.promt ai.create')

@endsection