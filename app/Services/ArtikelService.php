<?php

namespace App\Services;

use App\Jobs\CheckN8nTimeoutJob;
use App\Models\PerintahArtikel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArtikelService
{
    const N8N_GENERATE_JUDUL_URL = 'https://andy-biform-flukily.ngrok-free.dev/webhook/auto-post-generate-judul';
    const N8N_GENERATE_KONTEN_URL = 'https://andy-biform-flukily.ngrok-free.dev/webhook/auto-post-generate-konten';

    public function generateJudul(int $userId, int $websiteKlienId, string $topik, int $jumlahArtikel, ?int $existingPerintahId = null, bool $useCta = false): array
    {
        $perintah = null;
        if ($existingPerintahId) {
            $perintah = PerintahArtikel::find($existingPerintahId);
        }

        if (!$perintah) {
            $perintah = PerintahArtikel::create([
                'user_id' => $userId,
                'website_klien_id' => $websiteKlienId,
                'topik' => $topik,
                'jumlah_artikel' => $jumlahArtikel,
                'use_cta' => $useCta,
            ]);
        } else {
            $perintah->update([
                'status' => 'pending',
                'n8n_status' => null,
                'use_cta' => $useCta,
            ]);
        }

        $payload = [
            'perintah_artikel_id' => $perintah->id,
            'prompt_artikel' => $topik,
            'jumlah' => $jumlahArtikel,
            'website_klien_id' => $websiteKlienId,
        ];

        Log::info('Mengirim permintaan generate judul ke n8n', $payload);

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                    'Accept' => 'application/json',
                    'auth' => '12341234',
                ])
                ->timeout(30)
                ->post(self::N8N_GENERATE_JUDUL_URL, $payload);

            if ($response->successful()) {
                $responseData = $response->json();

                $executionId = $responseData['execution_id'] ?? null;
                if ($executionId) {
                    $perintah->update(['n8n_execution_id' => (string) $executionId]);
                    Log::info('execution_id n8n disimpan ke DB', [
                        'perintah_id' => $perintah->id,
                        'execution_id' => $executionId,
                    ]);
                }

                CheckN8nTimeoutJob::dispatch($perintah->id)->delay(now()->addSeconds(12));

                Log::info('Berhasil mengirim permintaan generate judul ke n8n', [
                    'perintah_id' => $perintah->id,
                    'status' => $response->status(),
                    'execution_id' => $executionId,
                ]);

                return [
                    'success' => true,
                    'message' => 'Permintaan generate judul berhasil dikirim ke n8n.',
                    'perintah' => $perintah,
                    'data' => $responseData,
                ];
            }

            Log::error('N8n mengembalikan error saat generate judul', [
                'perintah_id' => $perintah->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            $perintah->delete();

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke n8n. Status: ' . $response->status(),
                'perintah' => null,
                'data' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Exception saat mengirim ke n8n (generate judul): ' . $e->getMessage(), [
                'perintah_id' => $perintah->id,
            ]);

            $perintah->delete();

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke n8n: ' . $e->getMessage(),
                'perintah' => null,
                'data' => null,
            ];
        }
    }

    public function generateKonten(?int $websiteKlienId = null, ?int $specificArtikelId = null, array $retryDuplikatData = []): array
    {
        // 1. Reset otomatis artikel yang stuck di 'diproses' lebih dari 10 menit menjadi 'gagal' agar tidak memblokir antrean
        \App\Models\Artikel::where('status', 'diproses')
            ->where('updated_at', '<=', now()->subMinutes(10))
            ->update([
                'status' => 'gagal',
            ]);

        $artikel = null;
        if ($specificArtikelId) {
            $artikel = \App\Models\Artikel::with('websiteKlien')->find($specificArtikelId);
        }

        if (!$artikel) {
            $isProcessing = \App\Models\Artikel::where('status', 'diproses')->exists();
            if ($isProcessing) {
                $stuck = \App\Models\Artikel::where('status', 'diproses')->first();
                Log::info("generateKonten ditunda: Masih ada artikel yang sedang diproses (ID: {$stuck?->id}, Judul: {$stuck?->judul}).");
                return [
                    'success' => false,
                    'message' => 'Masih ada artikel yang sedang diproses. Eksekusi ditunda.',
                ];
            }

            $query = \App\Models\Artikel::with('websiteKlien')->where('status', 'terjadwal')
                ->where(function ($q) {
                    $q->whereNull('konten')
                        ->orWhere('konten', '')
                        ->orWhereRaw('TRIM(konten) = ""')
                        ->orWhere('konten', '<p></p>');
                });

            if ($websiteKlienId) {
                $query->where('website_klien_id', $websiteKlienId);
            }

            $artikel = $query->oldest('id')->first();
        }

        if (!$artikel) {
            return [
                'success' => true,
                'message' => 'Tidak ada artikel terjadwal yang menunggu generate konten.',
                'artikel' => null,
            ];
        }


        $artikel->update([
            'status' => 'diproses',
        ]);

        // Beritahu browser secara realtime bahwa artikel mulai diproses
        broadcast(new \App\Events\KontenArtikelTersimpan($artikel->id, $artikel->website_klien_id, 'diproses'));

        // Cari internal link
        $internalLink = \App\Models\Artikel::where('website_klien_id', $artikel->website_klien_id)
            ->whereNotNull('wp_url')
            ->where('wp_url', '!=', '')
            ->where('id', '!=', $artikel->id)
            ->latest('id')
            ->value('wp_url');

        if (!empty($internalLink)) {
            \App\Models\ArtikelHyperlink::firstOrCreate([
                'artikel_id' => $artikel->id,
                'url' => $internalLink,
                'tipe' => 'internal',
            ]);
        }

        $payload = array_merge([
            'artikel_id' => $artikel->id,
            'website_klien_id' => $artikel->website_klien_id,
            'perintah_artikel_id' => $artikel->perintah_artikel_id,
            'url_website' => $artikel->websiteKlien?->url_website,
            'judul' => $artikel->judul,
            'use_cta' => $artikel->use_cta,
            'internal_link' => $internalLink,
        ], $retryDuplikatData);

        Log::info("Mengirim permintaan generate konten ke n8n untuk artikel ID {$artikel->id}", [
            'judul' => $artikel->judul,
            'is_retry_duplikat' => !empty($retryDuplikatData),
        ]);

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                    'Accept' => 'application/json',
                    'auth' => '12341234',
                ])
                ->timeout(30)
                ->post(self::N8N_GENERATE_KONTEN_URL, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                $executionId = $responseData['execution_id'] ?? null;

                if ($executionId) {
                    $artikel->update(['n8n_execution_id' => (string) $executionId]);
                    Log::info('execution_id n8n konten disimpan ke DB', [
                        'artikel_id' => $artikel->id,
                        'execution_id' => $executionId,
                    ]);
                }

                // Dispatch job timeout 60 detik
                \App\Jobs\CheckN8nKontenTimeoutJob::dispatch($artikel->id)->delay(now()->addSeconds(60));

                Log::info('Berhasil mengirim permintaan generate konten ke n8n', [
                    'artikel_id' => $artikel->id,
                    'status' => $response->status(),
                    'execution_id' => $executionId,
                ]);

                return [
                    'success' => true,
                    'message' => 'Permintaan generate konten berhasil dikirim ke n8n.',
                    'artikel' => $artikel,
                    'data' => $responseData,
                ];
            } else {
                Log::error("generateKonten: n8n mengembalikan error HTTP {$response->status()} untuk artikel ID {$artikel->id}", [
                    'body' => $response->body(),
                ]);

                // Ambil detail error dari body n8n agar bisa ditampilkan di alert browser
                $errorData = json_decode($response->body(), true);
                $pesanDetail = is_array($errorData) ? ($errorData['message'] ?? $response->body()) : $response->body();
                $pesanAlert = "N8N Error HTTP {$response->status()}: " . ($pesanDetail ?: 'Webhook n8n tidak merespons');

                // Jika gagal kirim, ubah status jadi gagal agar tidak memblokir antrean berikutnya
                $artikel->update([
                    'status' => 'gagal',
                ]);

                // Beritahu browser bahwa artikel gagal diproses beserta pesan error detail
                broadcast(new \App\Events\KontenArtikelTersimpan($artikel->id, $artikel->website_klien_id, 'gagal', $pesanAlert));

                return [
                    'success' => false,
                    'message' => $pesanAlert,
                    'artikel' => $artikel,
                ];
            }
        } catch (\Exception $e) {
            Log::error("generateKonten: Exception saat mengirim ke n8n untuk artikel ID {$artikel->id}", [
                'error' => $e->getMessage(),
            ]);

            $pesanAlert = 'Gagal mengirim ke N8N: ' . $e->getMessage();

            $artikel->update([
                'status' => 'gagal',
            ]);

            // Beritahu browser bahwa artikel gagal diproses (exception)
            broadcast(new \App\Events\KontenArtikelTersimpan($artikel->id, $artikel->website_klien_id, 'gagal', $pesanAlert));

            return [
                'success' => false,
                'message' => $pesanAlert,
                'artikel' => $artikel,
            ];
        }
    }

    public function simpanJudulDariN8n(int $perintahArtikelId, array $juduls, int $websiteKlienId, ?string $tanggalJadwal = null): array
    {
        $savedIds = [];

        $currentDate = $tanggalJadwal
            ? \Carbon\Carbon::parse($tanggalJadwal)->startOfDay()
            : \Carbon\Carbon::today();
        $usedDatesInBatch = [];

        foreach ($juduls as $judulData) {
            $judul = is_array($judulData) ? ($judulData['judul'] ?? null) : $judulData;

            if (empty($judul)) {
                continue;
            }

            while (true) {
                $dateStr = $currentDate->toDateString();
                $dipakaiBatch = in_array($dateStr, $usedDatesInBatch);
                $sudahAdaDiDb = \App\Models\Artikel::where('website_klien_id', $websiteKlienId)
                    ->whereDate('tanggal_jadwal', $dateStr)
                    ->exists();

                if ($dipakaiBatch || $sudahAdaDiDb) {
                    $currentDate->addDay();
                    continue;
                }
                break;
            }

            // Ambil use_cta dari perintah induk agar diwariskan ke artikel ini
            $perintah = \App\Models\PerintahArtikel::find($perintahArtikelId);

            $artikel = \App\Models\Artikel::create([
                'judul' => $judul,
                'perintah_artikel_id' => $perintahArtikelId,
                'website_klien_id' => $websiteKlienId,
                'status' => 'terjadwal',
                'tanggal_jadwal' => $currentDate->copy(),
                'use_cta' => $perintah?->use_cta ?? false,
            ]);

            $savedIds[] = $artikel->id;

            $usedDatesInBatch[] = $currentDate->toDateString();
            $currentDate->addDay();
        }

        Log::info('Judul artikel dari n8n berhasil disimpan', [
            'count' => count($savedIds),
            'artikel_id' => $savedIds,
            'website_klien_id' => $websiteKlienId,
        ]);

        if (count($savedIds) > 0) {
            PerintahArtikel::where('id', $perintahArtikelId)->update([
                'status' => 'selesai',
                'n8n_status' => 'success',
            ]);

            broadcast(new \App\Events\JudulArtikelTersimpan($websiteKlienId));
        }

        return [
            'success' => count($savedIds) > 0,
            'count' => count($savedIds),
            'ids' => $savedIds,
        ];
    }
}
