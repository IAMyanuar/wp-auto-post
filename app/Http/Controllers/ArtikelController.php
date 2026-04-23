<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\WebsiteKlien;
use App\Models\AiAgentPrompt;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $search = $request->get('search');
        $status = $request->get('status');
        $websiteId = $request->get('website_id');

        $query = Artikel::with(['websiteKlien', 'aiAgentPrompt'])
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where('judul', 'like', "%{$search}%");
        }

        if ($websiteId) {
            $query->where('website_klien_id', $websiteId);
        }

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        } elseif (empty($status)) {
            $query->whereIn('status', ['terjadwal', 'diproses']);
        }

        $artikels = $query->paginate($limit)->withQueryString();
        $websites = WebsiteKlien::orderBy('nama_website', 'asc')->get();

        return view('pages.penjadwalan.index', compact('artikels', 'limit', 'search', 'status', 'websiteId', 'websites'));
    }

    public function riwayat(Request $request)
    {
        $limit = $request->get('limit', 10);
        $search = $request->get('search');
        $status = $request->get('status');
        $websiteId = $request->get('website_id');

        $query = Artikel::with(['websiteKlien', 'aiAgentPrompt'])
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where('judul', 'like', "%{$search}%");
        }

        if ($websiteId) {
            $query->where('website_klien_id', $websiteId);
        }

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        } elseif (empty($status)) {
            $query->whereIn('status', ['terpublish', 'gagal']);
        }

        $artikels = $query->paginate($limit)->withQueryString();
        $websites = WebsiteKlien::orderBy('nama_website', 'asc')->get();

        return view('pages.riwayat.index', compact('artikels', 'limit', 'search', 'status', 'websiteId', 'websites'));
    }

    public function create()
    {
        $websites = WebsiteKlien::all();
        $prompts = AiAgentPrompt::all();

        return view('pages.penjadwalan.create', compact('websites', 'prompts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'website_klien_id' => 'required|exists:website_klien,id',
            'ai_agent_prompt_id' => 'required|exists:ai_agent_prompt,id',
            'tipe_publikasi' => 'required|in:langsung,jadwal',
            'tanggal_jadwal' => 'required_if:tipe_publikasi,jadwal|nullable|date|after:now',
        ]);

        $useCta = $request->boolean('use_cta'); // true jika toggle ON

        $tanggal_jadwal = $request->tipe_publikasi === 'langsung' ? now() : $request->tanggal_jadwal;

        $artikel = Artikel::create([
            'judul' => $request->judul,
            'website_klien_id' => $request->website_klien_id,
            'ai_agent_prompt_id' => $request->ai_agent_prompt_id,
            'tanggal_jadwal' => $tanggal_jadwal,
            'status' => 'diproses', // Langsung set diproses saat dibuat
            'use_cta' => $useCta ? 1 : 0,
        ]);

        // Simpan Gambar (file upload)
        if ($request->hasFile('gambar_file')) {
            foreach ($request->file('gambar_file') as $index => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('artikel/gambar', 'public');
                    $artikel->gambars()->create([
                        'nama_gambar' => $file->getClientOriginalName(),
                        'alt_text' => $file->getClientOriginalName(),
                        'path' => $path,
                        'is_featured' => $index === 0,
                    ]);
                }
            }
        }

        // Simpan Hyperlink Internal
        if ($request->internal_url) {
            foreach ($request->internal_url as $index => $url) {
                if (!empty(trim($url))) {
                    $artikel->hyperlinks()->create([
                        'url' => $url,
                        'tipe' => 'internal',
                    ]);
                }
            }
        }

        // Simpan Hyperlink Eksternal
        if ($request->external_url) {
            foreach ($request->external_url as $index => $url) {
                if (!empty(trim($url))) {
                    $artikel->hyperlinks()->create([
                        'url' => $url,
                        'tipe' => 'external',
                    ]);
                }
            }
        }

        // Load relasi untuk
        $artikel->load(['gambars', 'hyperlinks', 'websiteKlien', 'aiAgentPrompt']);

        \Illuminate\Support\Facades\Log::info("Mulai membuat store artikel (ID: {$artikel->id})", [
            'website' => $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : 'Tidak ada',
            'judul' => $artikel->judul
        ]);
        $n8nData = [
            'artikel_id' => $artikel->id,
            'judul' => $artikel->judul,
            'tanggal_jadwal' => $artikel->tanggal_jadwal,
            'website' => $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : null,
            'url_website' => $artikel->websiteKlien ? $artikel->websiteKlien->url_website : null,
            'prompt' => $artikel->aiAgentPrompt ? $artikel->aiAgentPrompt->prompt : null,
            'gambars' => $artikel->gambars->pluck('path')->map(function ($path) {
                return asset('storage/' . $path);
            })->toArray(),
            'internal_links' => $artikel->hyperlinks->where('tipe', 'internal')->map(function ($link) {
                return ['url' => $link->url];
            })->values()->toArray(),
            'external_links' => $artikel->hyperlinks->where('tipe', 'external')->map(function ($link) {
                return ['url' => $link->url];
            })->values()->toArray(),
            'use_cta' => $artikel->use_cta ? 1 : 0,
            'no_telpon' => $artikel->use_cta ? optional($artikel->websiteKlien)->no_telpon : null,
            'alamat' => $artikel->use_cta ? optional($artikel->websiteKlien)->alamat : null,
        ];

        // Kirim POST Request ke Webhook n8n
        \Illuminate\Support\Facades\Log::info("Mengirim artikel (ID: {$artikel->id}) ke n8n", [
            'website' => $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : 'Tidak ada'
        ]);

        $artikel->update([
            'keterangan_proses' => 'Proses Pembuatan Konten',
            'persentase_proses' => 15,
        ]);

        try {
            $responseN8n = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->post('https://andy-biform-flukily.ngrok-free.dev/webhook-test/auto-post-n8n', $n8nData);

            if (!$responseN8n->successful()) {
                \Illuminate\Support\Facades\Log::error('Error respons dari n8n saat post artikel: ' . $responseN8n->body(), [
                    'website' => $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : 'Tidak ada'
                ]);
                $artikel->update([
                    'status' => 'gagal',
                    'keterangan_proses' => 'Gagal menerima respon dari n8n',
                    'persentase_proses' => 0,
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error request ke n8n (store artikel): ' . $e->getMessage(), [
                'website' => $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : 'Tidak ada'
            ]);
            $artikel->update([
                'status' => 'gagal',
                'keterangan_proses' => 'Timeout: N8n tidak aktif atau tidak merespon.',
                'persentase_proses' => 0,
            ]);
        }

        if ($artikel->status === 'gagal') {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memicu proses AI: ' . ($artikel->keterangan_proses ?? 'Hubungi Admin'),
                ], 422);
            }
            return redirect()->route('penjadwalan.index')
                ->with('error', 'Gagal memicu proses AI: ' . ($artikel->keterangan_proses ?? 'Hubungi Admin'));
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Jadwal artikel berhasil dibuat dan sedang diproses AI!',
            ]);
        }

        return redirect()->route('penjadwalan.index')
            ->with('success', 'Jadwal artikel berhasil dibuat dan sedang diproses AI!');
    }



    public function edit(Artikel $artikel)
    {
        $websites = WebsiteKlien::all();
        $prompts = AiAgentPrompt::all();

        return view('pages.penjadwalan.edit', compact('artikel', 'websites', 'prompts'));
    }

    public function update(Request $request, Artikel $artikel)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'website_klien_id' => 'required|exists:website_klien,id',
            'ai_agent_prompt_id' => 'required|exists:ai_agent_prompt,id',
            'tanggal_jadwal' => 'required|date',
            'status' => 'in:diproses,terjadwal,gagal,terpublish',
            'konten' => 'nullable|string',
            'kata_kunci' => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'meta_deskripsi' => 'nullable|string',
        ]);

        $artikel->update([
            'judul' => $request->judul,
            'website_klien_id' => $request->website_klien_id,
            'ai_agent_prompt_id' => $request->ai_agent_prompt_id,
            'tanggal_jadwal' => $request->tanggal_jadwal,
            'status' => $request->status ?? $artikel->status,
            'konten' => $request->konten,
            'kata_kunci' => $request->kata_kunci,
            'kategori' => $request->kategori,
            'tags' => $request->tags,
            'meta_deskripsi' => $request->meta_deskripsi,
        ]);

        return redirect()->route('penjadwalan.index')
            ->with('success', 'Detail artikel berhasil diperbarui!');
    }

    /**
     * Endpoint ringan untuk smart polling status artikel.
     * Hanya mengembalikan id+status artikel yang masih dalam status aktif (processing).
     * Jika tidak ada, frontend tahu harus berhenti polling.
     */
    public function pollStatus(Request $request)
    {
        $ids = $request->input('ids', []);

        // Jika tidak ada ID yang dikirim, kembalikan semua yang masih diproses
        if (empty($ids)) {
            $data = Artikel::where('status', 'diproses')
                ->select('id', 'status', 'wp_id', 'wp_url', 'skor_seo', 'skor_readability', 'keterangan_proses', 'persentase_proses')
                ->get();
        } else {
            // Cek status hanya untuk ID yang diminta
            $data = Artikel::whereIn('id', $ids)
                ->select('id', 'status', 'wp_id', 'wp_url', 'skor_seo', 'skor_readability', 'keterangan_proses', 'persentase_proses')
                ->get();
        }

        return response()->json([
            'statuses' => $data,
            'has_processing' => $data->where('status', 'diproses')->isNotEmpty(),
        ]);
    }

    public function retry(Artikel $artikel)
    {
        if ($artikel->status !== 'gagal') {
            return redirect()->route('penjadwalan.index')
                ->with('error', 'Hanya artikel yang gagal yang dapat dicoba ulang.');
        }

        $artikel->load(['gambars', 'hyperlinks', 'websiteKlien', 'aiAgentPrompt']);
        $website = $artikel->websiteKlien;

        // Bersihkan data di WordPress jika sebelumnya sudah sempat terbuat 
        // (agar tidak terjadi duplikat saat n8n generate ulang)
        if ($website && $artikel->wp_id) {
            $baseUrl = rtrim($website->base_url, '/');
            $auth = [$website->username, $website->password];

            // 1. Hapus gambar WP
            foreach ($artikel->gambars as $gambar) {
                if ($gambar->wp_media_id) {
                    try {
                        \Illuminate\Support\Facades\Http::withoutVerifying()
                            ->withBasicAuth(...$auth)
                            ->timeout(10)
                            ->delete("{$baseUrl}/wp-json/wp/v2/media/{$gambar->wp_media_id}?force=true");
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning("Gagal hapus media WP ID {$gambar->wp_media_id} saat retry: " . $e->getMessage());
                    }
                    // Reset field media
                    $gambar->update(['wp_media_id' => null, 'wp_media_url' => null]);
                }
            }

            // 2. Hapus post WP
            try {
                \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withBasicAuth(...$auth)
                    ->timeout(15)
                    ->delete("{$baseUrl}/wp-json/wp/v2/posts/{$artikel->wp_id}?force=true");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal hapus post WP saat retry: ' . $e->getMessage());
            }

            // Reset wp_id agar seolah-olah post baru bagi frontend & n8n
            $artikel->update(['wp_id' => null, 'wp_url' => null]);
        }

        // Ubah status ke diproses
        $artikel->update(['status' => 'diproses']);

        $n8nData = [
            'artikel_id' => $artikel->id,
            'judul' => $artikel->judul,
            'tanggal_jadwal' => $artikel->tanggal_jadwal,
            'website' => $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : null,
            'url_website' => $artikel->websiteKlien ? $artikel->websiteKlien->url_website : null,
            'prompt' => $artikel->aiAgentPrompt ? $artikel->aiAgentPrompt->prompt : null,
            'gambars' => $artikel->gambars->pluck('path')->map(function ($path) {
                return asset('storage/' . $path);
            })->toArray(),
            'internal_links' => $artikel->hyperlinks->where('tipe', 'internal')->map(function ($link) {
                return ['url' => $link->url];
            })->values()->toArray(),
            'external_links' => $artikel->hyperlinks->where('tipe', 'external')->map(function ($link) {
                return ['url' => $link->url];
            })->values()->toArray(),
            'use_cta' => $artikel->use_cta ? 1 : 0,
            'no_telpon' => $artikel->use_cta ? optional($artikel->websiteKlien)->no_telpon : null,
            'alamat' => $artikel->use_cta ? optional($artikel->websiteKlien)->alamat : null,
        ];

        // Kirim POST Request ke Webhook n8n
        \Illuminate\Support\Facades\Log::info("Mencoba ulang artikel (ID: {$artikel->id}) ke n8n", [
            'website' => $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : 'Tidak ada'
        ]);

        $artikel->update([
            'keterangan_proses' => 'Mencoba ulang tugas pembuatan artikel...',
            'persentase_proses' => 15,
        ]);

        try {
            $responseN8n = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                    'Accept' => 'application/json',
                ])
                ->timeout(30)
                ->post('https://andy-biform-flukily.ngrok-free.dev/webhook/auto-post-n8n', $n8nData);

            if (!$responseN8n->successful()) {
                \Illuminate\Support\Facades\Log::error('Error respons dari n8n saat retry post artikel: ' . $responseN8n->body(), [
                    'website' => $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : 'Tidak ada'
                ]);
                $artikel->update([
                    'status' => 'gagal',
                    'keterangan_proses' => 'Gagal uji ulang, respon n8n error',
                    'persentase_proses' => 0,
                ]);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error request ke n8n (retry artikel): ' . $e->getMessage(), [
                'website' => $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : 'Tidak ada'
            ]);
            $artikel->update([
                'status' => 'gagal',
                'keterangan_proses' => 'Server n8n timeout / tidak berfungsi',
                'persentase_proses' => 0,
            ]);
        }

        if ($artikel->status === 'gagal') {
            return redirect()->route('riwayat.index')
                ->with('error', 'Gagal Membuat Ulang Konten!, Tidak Terhubung Dengan N8N');
        }

        return redirect()->route('penjadwalan.index')
            ->with('success', 'Percobaan ulang dijalankan. Artikel sedang diproses!');
    }

    public function retryYoast(Artikel $artikel)
    {
        $website = $artikel->websiteKlien;

        if (!$website || !$artikel->wp_id) {
            return back()->with('error', 'Artikel ini belum berhasil dipublish ke WordPress (Tidak ada WP ID).');
        }

        $artikel->update([
            'status' => 'diproses',
            'keterangan_proses' => 'Mengambil ulang Skor SEO...',
            'persentase_proses' => 85,
        ]);

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                    'Accept' => 'application/json',
                ])
                ->timeout(15)
                ->post('https://andy-biform-flukily.ngrok-free.dev/webhook/cek-seo-yoast', [
                    'artikel_id' => $artikel->id,
                    'url_website' => $website->url_website,
                    'username' => $website->username,
                    'password' => $website->password,
                    'wp_id' => $artikel->wp_id,
                ]);

            if (!$response->successful()) {
                \Illuminate\Support\Facades\Log::error('Error respons n8n saat retry Yoast: ' . $response->body());
                $isLangsungPublish = $artikel->tanggal_jadwal && $artikel->tanggal_jadwal <= now();
                $artikel->update([
                    'status' => $isLangsungPublish ? 'terpublish' : 'terjadwal',
                    'keterangan_proses' => 'Gagal ambil Yoast lagi',
                    'persentase_proses' => 100,
                ]);
                return back()->with('error', 'Gagal request ke n8n untuk cek Yoast.');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Exception saat retry Yoast: ' . $e->getMessage());
            $isLangsungPublish = $artikel->tanggal_jadwal && $artikel->tanggal_jadwal <= now();
            $artikel->update([
                'status' => $isLangsungPublish ? 'terpublish' : 'terjadwal',
                'keterangan_proses' => 'Gagal ambil Yoast lagi (Time Out)',
                'persentase_proses' => 100,
            ]);
            return back()->with('error', 'Server n8n timeout / offline.');
        }

        return back()->with('success', 'Sedang mencoba mengambil ulang skor Yoast SEO!');
    }

    public function destroy(Artikel $artikel)
    {
        $website = $artikel->websiteKlien;

        // Ensure we load the relations needed for cleanup
        $artikel->load('gambars');

        // Hapus post & media di WordPress jika artikel sudah pernah dikirim ke WP
        if ($website && $artikel->wp_id) {
            $baseUrl = $website->base_url;
            $auth = [$website->username, $website->password];

            // 1. Hapus semua media/gambar di WP berdasarkan wp_media_id yang tersimpan di DB
            foreach ($artikel->gambars as $gambar) {
                if ($gambar->wp_media_id) {
                    try {
                        \Illuminate\Support\Facades\Http::withoutVerifying()
                            ->withBasicAuth(...$auth)
                            ->timeout(10)
                            ->delete("{$baseUrl}/wp-json/wp/v2/media/{$gambar->wp_media_id}?force=true");
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning("Gagal hapus media WP ID {$gambar->wp_media_id}: " . $e->getMessage());
                    }
                }
            }

            // 2. Hapus post WordPress secara permanen (force=true melewati Trash)
            try {
                \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withBasicAuth(...$auth)
                    ->timeout(15)
                    ->delete("{$baseUrl}/wp-json/wp/v2/posts/{$artikel->wp_id}?force=true");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal hapus post WP: ' . $e->getMessage());
            }
        }

        // Hapus file fisik gambar yang ada di folder storage lokal
        foreach ($artikel->gambars as $gambar) {
            if (!empty($gambar->path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($gambar->path);
            }
        }

        $artikel->delete();

        // Jika sedang di halaman riwayat, tetap di riwayat (back() agar filter tetap terjaga)
        if (request()->header('referer') && str_contains(request()->header('referer'), 'riwayat')) {
            return back()->with('success', 'Artikel berhasil dihapus dari riwayat!');
        }

        return redirect()->route('penjadwalan.index')
            ->with('success', 'Artikel beserta file gambarnya berhasil dihapus!');
    }
}
