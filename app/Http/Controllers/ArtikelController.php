<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\PerintahArtikel;
use App\Models\WebsiteKlien;
use App\Services\ArtikelService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ArtikelController extends Controller
{
    public function __construct(protected ArtikelService $artikelService)
    {
    }

    public function index(Request $request)
    {
        $limit = $request->get('limit', 10);
        $search = $request->get('search');
        $status = $request->get('status');
        $websiteId = $request->get('website_id');

        $query = Artikel::with(['websiteKlien', 'gambars'])
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



    public function create()
    {
        $websites = WebsiteKlien::all();
        return view('pages.penjadwalan.create', compact('websites'));
    }


    public function generateJudul(Request $request)
    {
        $validated = $request->validate([
            'topik_konten' => 'required|string|max:2000',
            'jumlah_konten' => 'required|integer|min:1|max:100',
            'website_klien_id' => 'required|exists:website_klien,id',
            'call_action' => 'nullable|string',
        ], [
            'topik_konten.required' => 'Prompt / topik konten wajib diisi.',
            'jumlah_konten.required' => 'Jumlah konten wajib dipilih.',
            'website_klien_id.required' => 'Website tujuan wajib dipilih.',
            'website_klien_id.exists' => 'Website tujuan tidak ditemukan.',
        ]);

        $result = $this->artikelService->generateJudul(
            userId: auth()->id(),
            websiteKlienId: (int) $validated['website_klien_id'],
            topik: $validated['topik_konten'],
            jumlahArtikel: (int) $validated['jumlah_konten'],
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Gagal mengirim permintaan ke n8n.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permintaan generate judul berhasil dikirim! N8n sedang memproses.',
            'perintah_id' => $result['perintah']->id ?? null,
            'data' => $result['data'],
        ]);
    }



    public function generateKonten(Request $request)
    {
        $websiteKlienId = $request->input('website_klien_id')
            ? (int) $request->input('website_klien_id')
            : null;

        $result = $this->artikelService->generateKonten($websiteKlienId);

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }

    public function retry(Request $request, $id)
    {
        if ($request->input('type') === 'perintah' || $request->has('perintah_id')) {
            $perintahId = $request->input('perintah_id', $id);
            $perintah = \App\Models\PerintahArtikel::find($perintahId);

            if ($perintah && (strcasecmp((string) $perintah->n8n_status, 'error') === 0 || $perintah->status === 'gagal' || $perintah->status === 'timeout')) {
                $perintah->update([
                    'status' => 'pending',
                    'n8n_status' => null,
                ]);

                $result = $this->artikelService->generateJudul(
                    $perintah->user_id ?? auth()->id(),
                    $perintah->website_klien_id,
                    $perintah->topik,
                    $perintah->jumlah_artikel,
                    $perintah->id
                );

                if (empty($result['success'])) {
                    return back()->with('error', $result['message'] ?? 'Gagal menghubungi n8n saat retry generate judul.');
                }

                return back()->with('success', 'Berhasil mengulangi proses generate judul!');
            }
        }

        // Kondisi 2: Jika pada tabel artikel kolom n8n_status = error, ulangi generateKonten()
        $artikel = \App\Models\Artikel::find($id);
        if ($artikel && (strcasecmp((string) $artikel->n8n_status, 'error') === 0 || $artikel->status === 'gagal')) {
            $artikel->update([
                'n8n_status' => null,
            ]);

            $result = $this->artikelService->generateKonten(null, $artikel->id);

            if (empty($result['success'])) {
                return back()->with('error', $result['message'] ?? 'Gagal menghubungi n8n saat retry generate konten.');
            }

            return back()->with('success', 'Berhasil mengulangi proses generate konten!');
        }

        // Fallback: Jika ID tidak ditemukan di tabel artikel, cek pada tabel perintah_artikel
        $perintah = \App\Models\PerintahArtikel::find($id);
        if ($perintah && (strcasecmp((string) $perintah->n8n_status, 'error') === 0 || $perintah->status === 'gagal' || $perintah->status === 'timeout')) {
            $perintah->update([
                'status' => 'diproses',
                'n8n_status' => null,
            ]);

            $result = $this->artikelService->generateJudul(
                $perintah->user_id ?? auth()->id(),
                $perintah->website_klien_id,
                $perintah->topik,
                $perintah->jumlah_artikel
            );

            if (empty($result['success'])) {
                return back()->with('error', $result['message'] ?? 'Gagal menghubungi n8n saat retry generate judul.');
            }

            return back()->with('success', 'Berhasil mengulangi proses generate judul!');
        }

        return back()->with('error', 'Proses retry gagal: Data tidak ditemukan atau status tidak memenuhi syarat untuk diulang.');
    }


    public function edit(Artikel $artikel)
    {
        $websites = WebsiteKlien::all();
        return view('pages.penjadwalan.edit', compact('artikel', 'websites'));
    }

    public function update(Request $request, Artikel $artikel)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'website_klien_id' => 'required|exists:website_klien,id',
            'tanggal_jadwal' => 'nullable|date',
            'status' => 'in:diproses,terjadwal,gagal,terpublish',
            'konten' => 'nullable|string',
            'kata_kunci' => 'nullable|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'meta_deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|max:10240',
            'gambars.*' => 'nullable|image|max:10240',
            'wp_status' => 'nullable|in:publish,draft',
        ]);

        $redirectRoute = $request->input('from') === 'riwayat' ? 'riwayat.index' : 'penjadwalan.index';

        $artikel->update([
            'judul' => $request->judul,
            'website_klien_id' => $request->website_klien_id,
            'tanggal_jadwal' => $request->tanggal_jadwal ?? $artikel->tanggal_jadwal,
            'status' => $request->status ?? $artikel->status,
            'konten' => $request->konten,
            'kata_kunci' => $request->kata_kunci,
            'kategori' => $request->kategori,
            'tags' => $request->tags,
            'meta_deskripsi' => $request->meta_deskripsi,
        ]);

        $file = $request->hasFile('gambar') ? $request->file('gambar') : ($request->hasFile('gambars') ? $request->file('gambars')[0] : null);
        if ($file) {
            $website = $artikel->websiteKlien;
            $artikel->load('gambars');
            if ($website && !empty($artikel->wp_id) && $artikel->status !== 'terjadwal') {
                $baseUrl = $website->base_url;
                $auth = [$website->username, $website->password];
                foreach ($artikel->gambars as $gambar) {
                    if ($gambar->wp_media_id) {
                        try {
                            \Illuminate\Support\Facades\Http::withoutVerifying()
                                ->withBasicAuth(...$auth)
                                ->timeout(10)
                                ->delete("{$baseUrl}/wp-json/wp/v2/media/{$gambar->wp_media_id}?force=true");
                        } catch (\Exception $e) {
                        }
                    }
                }
            }
            foreach ($artikel->gambars as $gambar) {
                if (!empty($gambar->path)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($gambar->path);
                }
            }
            $artikel->gambars()->delete();

            $path = $file->store('artikel_gambar', 'public');
            $namaGambar = $file->getClientOriginalName();

            $artikel->gambars()->create([
                'nama_gambar' => $namaGambar,
                'path' => $path,
                'alt_text' => pathinfo($namaGambar, PATHINFO_FILENAME),
            ]);
        }

        if (empty($artikel->wp_id) || $artikel->status === 'terjadwal') {
            return redirect()->route($redirectRoute)
                ->with('success', 'Detail artikel berhasil diperbarui!');
        }

        $website = $artikel->websiteKlien;
        if ($website) {
            $wpBaseUrl = $website->base_url;
            $auth = [$website->username, $website->password];
            $this->uploadGambarsToWordPress($artikel);

            $featuredMediaId = null;
            $artikel->load('gambars');
            $gambar = $artikel->gambars->whereNotNull('wp_media_id')->first();
            if ($gambar) {
                $featuredMediaId = $gambar->wp_media_id;
            }

            $wpApiUrl = "{$wpBaseUrl}/wp-json/wp/v2/posts/{$artikel->wp_id}";

            $body = [
                'title' => $artikel->judul,
                'content' => $artikel->konten,
                'category' => is_array($artikel->kategori) ? implode(', ', $artikel->kategori) : (string) ($artikel->kategori ?? ''),
                'tags' => is_array($artikel->tags) ? implode(', ', $artikel->tags) : (string) ($artikel->tags ?? ''),
            ];

            if ($request->has('wp_status') && in_array($request->wp_status, ['publish', 'draft'])) {
                $body['status'] = $request->wp_status;
            }

            if ($featuredMediaId) {
                $body['featured_media'] = $featuredMediaId;
            }

            if ($artikel->slug) {
                $body['slug'] = $artikel->slug;
            } elseif ($artikel->seo_title) {
                $body['slug'] = \Illuminate\Support\Str::slug($artikel->seo_title);
            } else {
                $body['slug'] = \Illuminate\Support\Str::slug($artikel->judul);
            }

            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withBasicAuth(...$auth)
                    ->timeout(30)
                    ->post($wpApiUrl, $body);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['link'])) {
                        $artikel->update(['wp_url' => $data['link']]);
                    }
                    \Illuminate\Support\Facades\Log::info("Artikel ID {$artikel->id} berhasil diupdate ke WordPress", [
                        'wp_id' => $artikel->wp_id,
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::warning("Gagal update artikel ID {$artikel->id} ke WordPress", [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                    return redirect()->route($redirectRoute)
                        ->with('warning', 'Detail artikel diperbarui di sistem, namun gagal update ke WordPress (HTTP ' . $response->status() . ').');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Exception update artikel ID {$artikel->id} ke WordPress: " . $e->getMessage());
                return redirect()->route($redirectRoute)
                    ->with('warning', 'Detail artikel diperbarui di sistem, namun koneksi ke WordPress gagal: ' . $e->getMessage());
            }
        }

        return redirect()->route($redirectRoute)
            ->with('success', 'Detail artikel berhasil diperbarui!');
    }


    public function destroy(Request $request, $id)
    {
        if ($request->input('type') === 'perintah') {
            $perintah = \App\Models\PerintahArtikel::find($id);
            if ($perintah) {
                $perintah->delete();
                return back()->with('success', 'Data perintah artikel yang gagal berhasil dihapus!');
            }
        }

        $artikel = \App\Models\Artikel::find($id);
        if (!$artikel) {
            $perintah = \App\Models\PerintahArtikel::find($id);
            if ($perintah) {
                $perintah->delete();
                return back()->with('success', 'Data perintah artikel yang gagal berhasil dihapus!');
            }
            abort(404);
        }

        $website = $artikel->websiteKlien;

        $artikel->load('gambars');

        if ($website && $artikel->wp_id) {
            $baseUrl = $website->base_url;
            $auth = [$website->username, $website->password];
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

            try {
                \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withBasicAuth(...$auth)
                    ->timeout(15)
                    ->delete("{$baseUrl}/wp-json/wp/v2/posts/{$artikel->wp_id}?force=true");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal hapus post WP: ' . $e->getMessage());
            }
        }

        foreach ($artikel->gambars as $gambar) {
            if (!empty($gambar->path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($gambar->path);
            }
        }

        $artikel->delete();

        if (request()->header('referer') && str_contains(request()->header('referer'), 'riwayat')) {
            return back()->with('success', 'Artikel berhasil dihapus dari riwayat!');
        }

        return redirect()->route('penjadwalan.index')
            ->with('success', 'Artikel beserta file gambarnya berhasil dihapus!');
    }

    private function uploadGambarsToWordPress(Artikel $artikel): void
    {
        $website = $artikel->websiteKlien;
        if (!$website) {
            \Illuminate\Support\Facades\Log::warning("uploadGambarsToWordPress: website tidak ditemukan untuk artikel ID {$artikel->id}");
            return;
        }

        $wpBaseUrl = $website->base_url;
        $wpMediaEndpoint = "{$wpBaseUrl}/wp-json/wp/v2/media";
        $auth = [$website->username, $website->password];

        $artikel->load('gambars');

        foreach ($artikel->gambars as $gambar) {
            if ($gambar->wp_media_id && $gambar->wp_media_url) {
                continue;
            }

            $pathImage = storage_path('app/public/' . $gambar->path);
            if (!file_exists($pathImage)) {
                $pathImage = storage_path('app/' . $gambar->path);
                if (!file_exists($pathImage)) {
                    \Illuminate\Support\Facades\Log::warning("File gambar tidak ditemukan: {$pathImage}");
                    continue;
                }
            }

            $extension = pathinfo($pathImage, PATHINFO_EXTENSION);
            $filename = \Illuminate\Support\Str::slug($artikel->judul) . '-img' . $gambar->id . '.' . $extension;
            $mimeType = mime_content_type($pathImage);

            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withBasicAuth(...$auth)
                    ->withHeaders([
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                        'Content-Type' => $mimeType,
                    ])
                    ->withBody(file_get_contents($pathImage), $mimeType)
                    ->timeout(30)
                    ->post($wpMediaEndpoint);

                if ($response->successful()) {
                    $mediaId = $response->json('id');
                    $mediaUrl = $response->json('source_url');

                    $altText = $gambar->alt_text ?: $artikel->kata_kunci ?: $artikel->judul;
                    \Illuminate\Support\Facades\Http::withoutVerifying()
                        ->withBasicAuth(...$auth)
                        ->timeout(15)
                        ->patch("{$wpBaseUrl}/wp-json/wp/v2/media/{$mediaId}", [
                            'alt_text' => $altText,
                            'title' => $altText,
                        ]);

                    $gambar->update([
                        'wp_media_id' => $mediaId,
                        'wp_media_url' => $mediaUrl,
                    ]);

                    \Illuminate\Support\Facades\Log::info("Gambar ID {$gambar->id} berhasil diupload ke WP", [
                        'artikel_id' => $artikel->id,
                        'wp_media_id' => $mediaId,
                        'wp_media_url' => $mediaUrl,
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::warning("Gagal upload gambar ID {$gambar->id} ke WordPress", [
                        'artikel_id' => $artikel->id,
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Exception upload gambar ID {$gambar->id} ke WordPress: " . $e->getMessage(), [
                    'artikel_id' => $artikel->id,
                ]);
            }
        }
    }

    public function riwayat(Request $request)
    {
        $limit = $request->get('limit', 10);
        $search = $request->get('search');
        $status = $request->get('status');
        $websiteId = $request->get('website_id');

        $query = Artikel::with(['websiteKlien', 'gambars'])
            ->orderBy('id', 'desc');

        if ($search) {
            $query->where('judul', 'like', "%{$search}%");
        }

        if ($websiteId) {
            $query->where('website_klien_id', $websiteId);
        }

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        } elseif (empty($status) || $status === 'semua') {
            $query->whereIn('status', ['terpublish', 'gagal']);
        }

        $artikelResults = $query->get();

        $perintahResults = collect([]);
        if (empty($status) || $status === 'gagal' || $status === 'semua') {
            $perintahQuery = PerintahArtikel::with('websiteKlien')
                ->where(function ($q) {
                    $q->where('n8n_status', 'error')
                        ->orWhere('status', 'timeout');
                })
                ->orderBy('id', 'desc');

            if ($search) {
                $perintahQuery->where('topik', 'like', "%{$search}%");
            }

            if ($websiteId) {
                $perintahQuery->where('website_klien_id', $websiteId);
            }

            $perintahResults = $perintahQuery->get()->map(function ($p) {
                $dummy = new Artikel();
                $dummy->id = $p->id;
                $dummy->judul = $p->topik;
                $dummy->status = 'gagal';
                $dummy->n8n_status = 'error';
                $dummy->tanggal_jadwal = null;
                $dummy->updated_at = $p->updated_at;
                $dummy->website_klien_id = $p->website_klien_id;
                $dummy->setRelation('websiteKlien', $p->websiteKlien);
                $dummy->is_perintah = true;
                $dummy->perintah_id = $p->id;
                $dummy->created_at = $p->created_at;
                return $dummy;
            });
        }

        $combined = $artikelResults->concat($perintahResults)->sortByDesc('created_at')->values();

        $currentPage = Paginator::resolveCurrentPage();
        $currentItems = $combined->slice(($currentPage - 1) * $limit, $limit)->all();
        $artikels = new LengthAwarePaginator(
            $currentItems,
            $combined->count(),
            $limit,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $websites = WebsiteKlien::orderBy('nama_website', 'asc')->get();

        return view('pages.riwayat.index', compact('artikels', 'limit', 'search', 'status', 'websiteId', 'websites'));
    }
}
