@extends('layout.master')

@section('title', 'Riwayat')
@section('page_title', 'Riwayat Aktivitas')

@section('content')
    <div class="mb-6">
        <p class="text-gray-600 text-sm">Catatan histori pembuatan dan publikasi artikel oleh AI Agen.</p>
    </div>

    <div class="space-y-4">
        <!-- History Item Placeholder -->
        <div class="flex gap-4 p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors">
            <div class="flex-shrink-0 mt-1">
                <span class="icon-[material-symbols-light--check-circle-outline] w-6 h-6 text-gray-900"></span>
            </div>
            <div>
                <h4 class="text-sm font-bold text-gray-800">Artikel berhasil dipublikasikan</h4>
                <p class="text-xs text-gray-500 mt-1">"Tips Membuat Konten Menarik di Tahun 2026" - Target: Contoh Blog</p>
                <span class="text-[11px] text-gray-400 mt-2 block">Hari ini, 10:30 WIB</span>
            </div>
        </div>

        <!-- History Item Placeholder -->
        <div class="flex gap-4 p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition-colors">
            <div class="flex-shrink-0 mt-1">
                <span class="icon-[material-symbols-light--info-outline-rounded] w-6 h-6 text-gray-900"></span>
            </div>
            <div>
                <h4 class="text-sm font-bold text-gray-800">AI Agen: Pembuatan Teks Selesai</h4>
                <p class="text-xs text-gray-500 mt-1">Prompt: "Panduan belajar Laravel untuk pemula dengan blade komponen"</p>
                <span class="text-[11px] text-gray-400 mt-2 block">Kemarin, 14:15 WIB</span>
            </div>
        </div>
    </div>
@endsection
