<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Services\ArtikelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nWebhookController extends Controller
{
    public function __construct(protected ArtikelService $artikelService)
    {
    }

    public function receiveJudulResult(Request $request)
    {
        $validated = validator($request->all(), [
            'website_klien_id' => 'required|integer|exists:website_klien,id',
            'perintah_artikel_id' => 'required|integer|exists:perintah_artikel,id',
            'judul' => 'required|array|min:1',
            'judul.*' => 'required|string|max:500',
            'tanggal_jadwal' => 'nullable|date',
        ], [
            'website_klien_id.required' => 'ID website klien wajib disertakan.',
            'perintah_artikel_id.required' => 'ID perintah artikel wajib disertakan.',
            'website_klien_id.exists' => 'Website klien tidak ditemukan.',
            'perintah_artikel_id.exists' => 'Perintah artikel tidak ditemukan.',
            'judul.required' => 'Daftar judul wajib disertakan.',
            'judul.array' => 'Daftar judul harus berupa array.',
            'judul.min' => 'Minimal satu judul harus disertakan.',
        ])->validate();

        Log::info('Menerima daftar judul artikel dari n8n', [
            'website_klien_id' => $validated['website_klien_id'],
            'jumlah_judul' => count($validated['judul']),
        ]);

        $result = $this->artikelService->simpanJudulDariN8n(
            juduls: $validated['judul'],
            perintahArtikelId: (int) $validated['perintah_artikel_id'],
            websiteKlienId: (int) $validated['website_klien_id'],
            tanggalJadwal: $validated['tanggal_jadwal'] ?? null,
        );

        if (!$result['success']) {
            Log::warning('Tidak ada judul yang berhasil disimpan dari n8n', [
                'website_klien_id' => $validated['website_klien_id'],
            ]);

            return response()->json([
                'message' => 'Tidak ada judul yang berhasil disimpan.',
                'count' => 0,
                'ids' => [],
            ], 422);
        }

        return response()->json([
            'message' => 'Judul artikel berhasil disimpan.',
            'count' => $result['count'],
            'artikel_ids' => $result['ids'],
        ], 200);
    }

    public function receiveKonten(Request $request)
    {
        $data = $request->merge([
            'artikel_id' => $request->input('artikel_id'),
            'konten' => $request->input('konten'),
            'judul' => $request->input('judul'),
            'slug' => $request->input('slug'),
            'meta_deskripsi' => $request->input('meta_deskripsi'),
            'kata_kunci' => $request->input('kata_kunci'),
            'tags' => $request->input('tags'),
            'kategori' => $request->input('kategori'),
        ])->all();

        $validated = validator($data, [
            'artikel_id' => 'required|integer|exists:artikel,id',
            'konten' => 'required|string',
            'judul' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'meta_deskripsi' => 'nullable|string',
            'kata_kunci' => 'nullable|string',
            'tags' => 'nullable|string',
            'kategori' => 'nullable|string',
        ], [
            'artikel_id.required' => 'Kolom ID artikel wajib diisi.',
            'artikel_id.exists' => 'Artikel dengan ID tersebut tidak ditemukan.',
            'konten.required' => 'Konten artikel wajib diisi.',
        ])->validate();

        $artikel = Artikel::with('websiteKlien')->findOrFail($validated['artikel_id']);
        $websiteKlien = $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : 'Tidak ada';

        Log::info("Menerima data dari n8n (Konten Artikel)", [
            'artikel_id' => $artikel->id,
            'website' => $websiteKlien
        ]);

        $artikel->update([
            'status' => 'diproses',
            'konten' => $validated['konten'],
            'judul' => $validated['judul'] ?? $artikel->judul,
            'slug' => $validated['slug'],
            'meta_deskripsi' => $validated['meta_deskripsi'],
            'kata_kunci' => $validated['kata_kunci'],
            'tags' => $validated['tags'],
            'kategori' => $validated['kategori'],
        ]);

        $wpResult = $this->sendToWordPress($artikel);

        if ($wpResult['success']) {
            $statusAkhir = ($artikel->tanggal_jadwal && $artikel->tanggal_jadwal <= now()) ? 'terpublish' : 'terjadwal';
            $updateData = [
                'status' => $statusAkhir,
                'wp_id' => $wpResult['wp_id'],
                'wp_url' => $wpResult['wp_url'],
            ];
            if ($statusAkhir === 'terpublish') {
                $updateData['tanggal_terbit'] = now();
            }

            $artikel->update($updateData);

            broadcast(new \App\Events\JudulArtikelTersimpan($artikel->website_klien_id));
            broadcast(new \App\Events\KontenArtikelTersimpan($artikel->id, $artikel->website_klien_id, $statusAkhir));

            return response()->json([
                'message' => 'Konten berhasil diterima dan dikirim ke WordPress.',
                'artikel_id' => $artikel->id,
                'wp_id' => $wpResult['wp_id'],
                'wp_url' => $wpResult['wp_url'],
            ], 200);
        }

        // Gagal kirim ke WordPress → tandai gagal
        $artikel->update(['status' => 'gagal']);

        broadcast(new \App\Events\JudulArtikelTersimpan($artikel->website_klien_id));
        broadcast(new \App\Events\KontenArtikelTersimpan($artikel->id, $artikel->website_klien_id, 'gagal'));

        return response()->json([
            'message' => 'Konten diterima, tetapi gagal dikirim ke WordPress.',
            'artikel_id' => $artikel->id,
            'error' => $wpResult['error'],
            'status' => 'gagal',
        ], 422);
    }

    private function sendToWordPress(Artikel $artikel): array
    {
        $website = $artikel->websiteKlien;

        if (!$website) {
            return ['success' => false, 'error' => 'Website klien tidak ditemukan.'];
        }

        $wpBaseUrl = $website->base_url;
        $wpApiUrl = "{$wpBaseUrl}/wp-json/wp/v2/posts";

        Log::info("Mengirim ke WordPress", [
            'artikel_id' => $artikel->id,
            'website' => $website->nama_website
        ]);


        // Jika gambar belum diupload ke WP sebelumnya, upload otomatis sekarang
        $this->ensureImagesUploadedToWordPress($artikel);

        $featuredMediaId = null;
        $artikel->load('gambars');
        // Ambil gambar pertama dari tabel artikel_gambar
        $gambar = $artikel->gambars->first();
        if ($gambar && $gambar->wp_media_id) {
            $featuredMediaId = $gambar->wp_media_id;
            Log::info("Menggunakan featured media ID dari DB: {$featuredMediaId}", [
                'artikel_id' => $artikel->id,
            ]);
        }

        $wpStatus = ($artikel->tanggal_jadwal && $artikel->tanggal_jadwal <= now()) ? 'publish' : 'draft';

        $body = [
            'title' => $artikel->judul,
            'content' => $artikel->konten,
            'category' => (string) ($artikel->kategori ?? ''),
            'tags' => (string) ($artikel->tags ?? ''),
            'status' => $wpStatus,
        ];

        if ($featuredMediaId) {
            $body['featured_media'] = $featuredMediaId;
        }

        if ($artikel->seo_title) {
            $body['slug'] = \Illuminate\Support\Str::slug($artikel->seo_title);
        }

        try {
            $response = Http::withoutVerifying()
                ->withBasicAuth($website->username, $website->password)
                ->timeout(30)
                ->post($wpApiUrl, $body);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'wp_id' => $data['id'] ?? null,
                    'wp_url' => $data['link'] ?? null,
                ];
            }

            Log::error('WordPress API error', [
                'artikel_id' => $artikel->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => "WordPress API error [{$response->status()}]: " . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Exception sending to WordPress', [
                'artikel_id' => $artikel->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function ensureImagesUploadedToWordPress(Artikel $artikel): void
    {
        $website = $artikel->websiteKlien;
        if (!$website) {
            return;
        }

        $wpBaseUrl = $website->base_url;
        $wpMediaEndpoint = "{$wpBaseUrl}/wp-json/wp/v2/media";
        $auth = [$website->username, $website->password];

        $artikel->load('gambars');

        // Jika artikel tidak memiliki gambar sama sekali, tidak usah proses upload (upload gambar opsional)
        if ($artikel->gambars->isEmpty()) {
            return;
        }

        foreach ($artikel->gambars as $gambar) {
            if ($gambar->wp_media_id && $gambar->wp_media_url) {
                continue;
            }

            $pathImage = storage_path('app/public/' . $gambar->path);
            if (!file_exists($pathImage)) {
                $pathImage = storage_path('app/' . $gambar->path);
                if (!file_exists($pathImage)) {
                    Log::warning("ensureImagesUploadedToWordPress: File gambar tidak ditemukan: {$pathImage}");
                    continue;
                }
            }

            $extension = pathinfo($pathImage, PATHINFO_EXTENSION);
            $filename = \Illuminate\Support\Str::slug($artikel->judul) . '-img' . $gambar->id . '.' . $extension;
            $mimeType = mime_content_type($pathImage);

            try {
                $response = Http::withoutVerifying()
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
                    Http::withoutVerifying()
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

                    Log::info("Gambar ID {$gambar->id} berhasil diupload otomatis ke WP saat receiveKonten", [
                        'artikel_id' => $artikel->id,
                        'wp_media_id' => $mediaId,
                        'wp_media_url' => $mediaUrl,
                    ]);
                } else {
                    Log::warning("Gagal upload gambar ID {$gambar->id} ke WordPress saat receiveKonten", [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Exception upload gambar ID {$gambar->id} saat receiveKonten: " . $e->getMessage());
            }
        }
    }

    public function receiveKontenResult(Request $request)
    {
        return $this->receiveKonten($request);
    }
}