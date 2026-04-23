<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Nullable;

class N8nWebhookController extends Controller
{
    public function receiveContent(Request $request)
    {
        $data = $request->merge([
            'artikel_id' => $request->input('artikel_id'),
            'konten' => $request->input('konten')
                ?? $request->input('artikel_konten'),
            'judul' => $request->input('judul')
                ?? $request->input('judul_artikel'),
            'slug' => $request->input('slug')
                ?? $request->input('slug_artikel'),
            'seo_title' => $request->input('seo_title')
                ?? $request->input('artikel_seo_title'),
            'meta_deskripsi' => $request->input('meta_deskripsi')
                ?? $request->input('meta_desripsi'),
            'kata_kunci' => $request->input('kata_kunci'),
            'tags' => $request->input('tags') ?? $request->input('tag_artikel'),
            'kategori' => $request->input('kategori') ?? $request->input('kategori_artikel'),
        ])->all();

        // Validasi setelah normalisasi
        $validated = validator($data, [
            'artikel_id' => 'required|integer|exists:artikel,id',
            'konten' => 'required|string',
            'judul' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:255',
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
            'slug' => $validated['slug'] ?? $artikel->slug,
            'seo_title' => $validated['seo_title'] ?? $artikel->seo_title,
            'meta_deskripsi' => $validated['meta_deskripsi'] ?? $artikel->meta_deskripsi,
            'kata_kunci' => $validated['kata_kunci'] ?? $artikel->kata_kunci,
            'tags' => $validated['tags'] ?? $artikel->tags,
            'kategori' => $validated['kategori'] ?? $artikel->kategori,
            'keterangan_proses' => 'Menerima konten',
            'persentase_proses' => 35,
        ]);

        // 2. Kirim ke WordPress
        $wpResult = $this->sendToWordPress($artikel);

        if ($wpResult['success']) {
            // 3. Simpan wp_id & wp_url. 
            // CATATAN: Status tetap dibiarkan 'diproses' agar progress tidak hilang di frontend, nanti diupdate di akhir fungsi result yoast!
            $artikel->update([
                'wp_id' => $wpResult['wp_id'],
                'wp_url' => $wpResult['wp_url'],
            ]);

            // 4. Update data Yoast SEO ke WordPress
            $this->updateYoastMeta($artikel, $wpResult['wp_id']);

            // 5. Trigger n8n untuk cek skor SEO Yoast
            $this->triggerSeoCheck($artikel, $wpResult['wp_id']);

            return response()->json([
                'message' => 'Konten berhasil diterima dan dikirim ke WordPress.',
                'artikel_id' => $artikel->id,
                'wp_id' => $wpResult['wp_id'],
                'wp_url' => $wpResult['wp_url'],
                // 'status' => $newStatus,
            ], 200);
        }

        // Gagal kirim ke WordPress → tandai gagal
        $artikel->update(['status' => 'gagal']);

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

        // Gunakan accessor dari model untuk mendapatkan base url secara langsung
        $wpBaseUrl = $website->base_url;
        $wpMediaUrl = "{$wpBaseUrl}/wp-json/wp/v2/media";
        $wpApiUrl = "{$wpBaseUrl}/wp-json/wp/v2/posts";

        Log::info("Mengirim ke WordPress", [
            'artikel_id' => $artikel->id,
            'website' => $website->nama_website
        ]);

        $artikel->update([
            'keterangan_proses' => 'Mengirim artikel ke WordPress tujuan',
            'persentase_proses' => 50,
        ]);


        $featuredMediaId = null;
        $gambarFeatured = $artikel->gambarFeatured;

        if ($gambarFeatured) {
            $pathImage = storage_path('app/public/' . $gambarFeatured->path);
            if (file_exists($pathImage)) {
                $extension = pathinfo($pathImage, PATHINFO_EXTENSION);
                $filename = \Illuminate\Support\Str::slug($artikel->judul) . '.' . $extension;
                $mimeType = mime_content_type($pathImage);

                try {
                    $responseMedia = Http::withoutVerifying()
                        ->withBasicAuth($website->username, $website->password)
                        ->withHeaders([
                            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                            'Content-Type' => $mimeType,
                        ])
                        ->withBody(file_get_contents($pathImage), $mimeType)
                        ->timeout(30)
                        ->post($wpMediaUrl);

                    if ($responseMedia->successful()) {
                        $featuredMediaId = $responseMedia->json('id');

                        // Simpan wp_media_id ke DB agar bisa dihapus nanti dari WP
                        $gambarFeatured->update(['wp_media_id' => $featuredMediaId]);

                        // Update alt_text media via PATCH (tidak bisa dikirim bersamaan dengan binary upload)
                        if ($artikel->kata_kunci) {
                            Http::withoutVerifying()
                                ->withBasicAuth($website->username, $website->password)
                                ->timeout(15)
                                ->patch("{$wpBaseUrl}/wp-json/wp/v2/media/{$featuredMediaId}", [
                                    'alt_text' => $artikel->kata_kunci,
                                ]);
                        }
                    } else {
                        Log::warning('Gagal upload gambar ke WordPress', [
                            'artikel_id' => $artikel->id,
                            'response' => $responseMedia->body()
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Exception upload media ke WordPress: ' . $e->getMessage());
                }
            }
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

    private function updateYoastMeta(Artikel $artikel, $wpId)
    {
        $website = $artikel->websiteKlien;
        if (!$website) {
            return;
        }

        $wpBaseUrl = $website->base_url;
        $yoastEndpoint = "{$wpBaseUrl}/wp-json/yoast/v1/update";

        $body = [
            'post_id' => $wpId,
            'focus_keyphrase' => $artikel->kata_kunci,
            'description' => $artikel->meta_deskripsi,
        ];

        try {
            $response = Http::withoutVerifying()
                ->withBasicAuth($website->username, $website->password)
                ->timeout(30)
                ->post($yoastEndpoint, $body);

            if (!$response->successful()) {
                Log::warning('Gagal mengupdate Yoast SEO', [
                    'artikel_id' => $artikel->id,
                    'wp_id' => $wpId,
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception update Yoast: ' . $e->getMessage());
        }
    }
    private function triggerSeoCheck(Artikel $artikel, $wpId)
    {
        $website = $artikel->websiteKlien;
        if (!$website || !$wpId) {
            return;
        }

        Log::info("Mengirim ke n8n untuk mengambil nilai yoast", [
            'artikel_id' => $artikel->id,
            'website' => $website->nama_website
        ]);

        $artikel->update([
            'keterangan_proses' => 'Menganalisis Skor SEO Artikel',
            'persentase_proses' => 85,
        ]);

        try {
            $response = Http::withoutVerifying()
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
                    'wp_id' => $wpId,
                ]);

            if (!$response->successful()) {
                Log::error('Error dari n8n saat request cek seo yoast: ' . $response->body(), [
                    'artikel_id' => $artikel->id,
                    'website' => $website->nama_website
                ]);
                $isLangsungPublish = $artikel->tanggal_jadwal && $artikel->tanggal_jadwal <= now();
                $artikel->update([
                    'status' => $isLangsungPublish ? 'terpublish' : 'terjadwal',
                    'keterangan_proses' => 'Selesai (Gagal ambil Yoast)',
                    'persentase_proses' => 100,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim ke n8n untuk cek SEO Yoast: ' . $e->getMessage(), [
                'artikel_id' => $artikel->id,
                'website' => $website->nama_website
            ]);
            $isLangsungPublish = $artikel->tanggal_jadwal && $artikel->tanggal_jadwal <= now();
            $artikel->update([
                'status' => $isLangsungPublish ? 'terpublish' : 'terjadwal',
                'keterangan_proses' => 'Selesai (Gagal ambil Yoast)',
                'persentase_proses' => 100,
            ]);
        }
    }


    public function receiveSeoResult(Request $request)
    {
        $validated = validator($request->all(), [
            'artikel_id' => 'required|integer|exists:artikel,id',
            'skor_seo' => 'nullable|integer|min:0|max:100',
            'skor_readability' => 'nullable|integer|min:0|max:100',
            'deskripsi_yoast' => 'nullable|string',
        ])->validate();

        $artikel = Artikel::with('websiteKlien')->findOrFail($validated['artikel_id']);
        $websiteKlien = $artikel->websiteKlien ? $artikel->websiteKlien->nama_website : 'Tidak ada';

        Log::info("Menerima nilai dari n8n (Skor Yoast SEO)", [
            'artikel_id' => $artikel->id,
            'website' => $websiteKlien,
            'skor_seo' => $validated['skor_seo'] ?? null
        ]);

        $isLangsungPublish = $artikel->tanggal_jadwal && $artikel->tanggal_jadwal <= now();

        $artikel->update([
            'status' => $isLangsungPublish ? 'terpublish' : 'terjadwal',
            'skor_seo' => $validated['skor_seo'] ?? $artikel->skor_seo,
            'skor_readability' => $validated['skor_readability'] ?? $artikel->skor_readability,
            'deskripsi_yoast' => $validated['deskripsi_yoast'] ?? $artikel->deskripsi_yoast,
            'keterangan_proses' => 'Selesai!',
            'persentase_proses' => 100,
        ]);

        Log::info('Skor SEO Yoast berhasil diperbarui', [
            'artikel_id' => $artikel->id,
            'skor_seo' => $artikel->skor_seo,
            'skor_readability' => $artikel->skor_readability,
        ]);

        return response()->json([
            'message' => 'Skor SEO berhasil diperbarui.',
            'artikel_id' => $artikel->id,
            'skor_seo' => $artikel->skor_seo,
            'skor_readability' => $artikel->skor_readability,
        ], 200);
    }
}
