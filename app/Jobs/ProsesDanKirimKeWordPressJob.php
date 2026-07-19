<?php

namespace App\Jobs;

use App\Models\Artikel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProsesDanKirimKeWordPressJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan maksimal jika job gagal.
     */
    public int $tries = 1;

    /**
     * Timeout maksimal untuk job ini (detik).
     * Dibuat panjang karena ada proses upload gambar + POST ke WP.
     */
    public int $timeout = 180;

    public function __construct(public readonly int $artikelId)
    {
    }

    /**
     * Eksekusi job: cek plagiasi, upload gambar ke WP, kirim artikel ke WP,
     * lalu broadcast event ke browser via WebSocket.
     */
    public function handle(): void
    {
        $artikel = Artikel::with('websiteKlien')->find($this->artikelId);

        if (!$artikel) {
            Log::warning("ProsesDanKirimKeWordPressJob: Artikel ID {$this->artikelId} tidak ditemukan.");
            return;
        }

        if ($artikel->status !== 'diproses') {
            Log::info("ProsesDanKirimKeWordPressJob: Artikel ID {$artikel->id} sudah berstatus '{$artikel->status}', skip proses.");
            return;
        }
        $uniqtextService = app(\App\Services\UniqtextService::class);
        $checkResult = $uniqtextService->checkArticle($artikel);

        if ($checkResult['success'] && ($checkResult['dup_rate'] ?? 0) > 50) {
            $percobaanKe = (int) ($checkResult['percobaan_ke'] ?? 1);

            if ($percobaanKe < 3) {
                Log::info("ProsesDanKirimKeWordPressJob: Artikel ID {$artikel->id} terdeteksi duplikat {$checkResult['dup_rate']}% (Percobaan ke-{$percobaanKe}). Memicu regenerasi N8N.");

                $artikelService = app(\App\Services\ArtikelService::class);
                $retryPayload = [
                    'is_retry_duplikat' => true,
                    'percobaan_ke' => $percobaanKe + 1,
                    'dup_rate_sebelumnya' => $checkResult['dup_rate'],
                    'snips_dup' => $checkResult['snips_dup'] ?? [],
                    'catatan_koreksi' => "Konten sebelumnya terdeteksi duplikat sebesar {$checkResult['dup_rate']}%. Tolong tulis ulang artikel ini dengan parafrase total agar 100% unik.",
                ];

                $artikelService->generateKonten($artikel->website_klien_id, $artikel->id, $retryPayload);
                return;
            }

            Log::info("ProsesDanKirimKeWordPressJob: Artikel ID {$artikel->id} telah mencapai batas maksimal percobaan ({$percobaanKe}x) dengan duplikasi {$checkResult['dup_rate']}%. Tetap dikirim ke WordPress.");
        }

        $this->ensureImagesUploadedToWordPress($artikel);

        $wpResult = $this->sendToWordPress($artikel);

        if ($wpResult['success']) {
            $artikel->loadMissing('gambars');
            $hasCompleteData = !empty(trim($artikel->konten ?? '')) && $artikel->gambars->isNotEmpty();
            $statusAkhir = ($artikel->tanggal_jadwal && $artikel->tanggal_jadwal <= now() && $hasCompleteData) ? 'terpublish' : 'terjadwal';

            $updateData = [
                'status' => $statusAkhir,
                'wp_id' => $wpResult['wp_id'],
                'wp_url' => $wpResult['wp_url'],
            ];

            if ($statusAkhir === 'terpublish') {
                $updateData['tanggal_terbit'] = now();
            }

            $artikel->update($updateData);

            Log::info("ProsesDanKirimKeWordPressJob: Artikel ID {$artikel->id} berhasil dikirim ke WordPress dengan status '{$statusAkhir}'.", [
                'wp_id' => $wpResult['wp_id'],
                'wp_url' => $wpResult['wp_url'],
            ]);

            broadcast(new \App\Events\JudulArtikelTersimpan($artikel->website_klien_id));
            broadcast(new \App\Events\KontenArtikelTersimpan($artikel->id, $artikel->website_klien_id, $statusAkhir));

            return;
        }

        // Jika gagal kirim ke WordPress → tandai gagal dan broadcast agar UI update
        $artikel->update(['status' => 'gagal']);

        Log::error("ProsesDanKirimKeWordPressJob: Gagal mengirim artikel ID {$artikel->id} ke WordPress.", [
            'error' => $wpResult['error'] ?? 'Unknown error',
        ]);

        $pesanError = $wpResult['error'] ?? 'Gagal memproses/mengirim artikel ke WordPress.';
        broadcast(new \App\Events\JudulArtikelTersimpan($artikel->website_klien_id));
        broadcast(new \App\Events\KontenArtikelTersimpan($artikel->id, $artikel->website_klien_id, 'gagal', $pesanError));
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
            'website' => $website->nama_website,
        ]);

        $featuredMediaId = null;
        $artikel->load('gambars');
        $gambar = $artikel->gambars->first();

        if ($gambar && $gambar->wp_media_id) {
            $featuredMediaId = $gambar->wp_media_id;
            Log::info("Menggunakan featured media ID dari DB: {$featuredMediaId}", [
                'artikel_id' => $artikel->id,
            ]);
        }

        $wpStatus = ($artikel->tanggal_jadwal && $artikel->tanggal_jadwal <= now() && $artikel->gambars->isNotEmpty()) ? 'publish' : 'draft';

        $body = [
            'title' => $artikel->judul,
            'content' => \App\Models\Artikel::cleanDuplicateMarkers($artikel->konten),
            'category' => (string) ($artikel->kategori ?? ''),
            'tags' => (string) ($artikel->tags ?? ''),
            'status' => $wpStatus,
        ];

        if ($featuredMediaId) {
            $body['featured_media'] = $featuredMediaId;
        }

        if ($artikel->seo_title) {
            $body['slug'] = Str::slug($artikel->seo_title);
        }

        try {
            $response = Http::withoutVerifying()
                ->withBasicAuth($website->username, $website->password)
                ->timeout(30)
                ->post($wpApiUrl, $body);

            if ($response->successful()) {
                $data = $response->json();

                Log::info("Artikel ID {$artikel->id} berhasil diupdate ke WordPress", [
                    'wp_id' => $data['id'] ?? null,
                ]);

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

    /**
     * Memastikan semua gambar artikel sudah diupload ke WordPress.
     * Method ini dipindahkan dari N8nWebhookController::ensureImagesUploadedToWordPress().
     */
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

        // Jika artikel tidak memiliki gambar sama sekali, tidak usah proses upload
        if ($artikel->gambars->isEmpty()) {
            return;
        }

        foreach ($artikel->gambars as $gambar) {
            // Sudah pernah diupload sebelumnya, lewati
            if ($gambar->wp_media_id && $gambar->wp_media_url) {
                continue;
            }

            $pathImage = storage_path('app/public/' . $gambar->path);
            if (!file_exists($pathImage)) {
                $pathImage = storage_path('app/' . $gambar->path);
                if (!file_exists($pathImage)) {
                    Log::warning("ProsesDanKirimKeWordPressJob: File gambar tidak ditemukan: {$pathImage}");
                    continue;
                }
            }

            $extension = pathinfo($pathImage, PATHINFO_EXTENSION);
            $filename = Str::slug($artikel->judul) . '-img' . $gambar->id . '.' . $extension;
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

                    Log::info("Gambar ID {$gambar->id} berhasil diupload otomatis ke WP", [
                        'artikel_id' => $artikel->id,
                        'wp_media_id' => $mediaId,
                        'wp_media_url' => $mediaUrl,
                    ]);
                } else {
                    Log::warning("Gagal upload gambar ID {$gambar->id} ke WordPress", [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Exception upload gambar ID {$gambar->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Jika job gagal total (setelah semua tries), tandai artikel sebagai gagal
     * dan broadcast ke browser agar UI diperbarui.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProsesDanKirimKeWordPressJob: Job gagal untuk Artikel ID {$this->artikelId}.", [
            'error' => $exception->getMessage(),
        ]);

        $artikel = Artikel::find($this->artikelId);
        if ($artikel && $artikel->status === 'diproses') {
            $artikel->update(['status' => 'gagal']);

            $pesanError = 'Proses background gagal: ' . $exception->getMessage();
            broadcast(new \App\Events\KontenArtikelTersimpan($artikel->id, $artikel->website_klien_id, 'gagal', $pesanError));
        }
    }
}
