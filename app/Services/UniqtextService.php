<?php

namespace App\Services;

use App\Models\Artikel;
use App\Models\CekDuplikasi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UniqtextService
{
    const CHECK_API_URL = 'https://covenantlinks.com/id/uniquetext/api/check2';
    const REPORT_API_URL = 'https://covenantlinks.com/id/uniquetext/api/teamreport';

    /**
     * Memeriksa keunikan/duplikasi artikel menggunakan API Uniqtext,
     * menyimpan hasil ke tabel cek_duplikasi, dan mengirimkan laporan ke Covenant.
     */
    public function checkArticle(Artikel $artikel): array
    {
        $email = config('services.uniqtext.email');
        $tokenApi = config('services.uniqtext.token_api');
        $whitelist = config('services.uniqtext.whitelist', '');

        $cleanText = trim(strip_tags($artikel->konten ?? ''));
        if (empty($cleanText)) {
            Log::warning("UniqtextService: Konten artikel ID {$artikel->id} kosong saat diuji.");
            return [
                'success' => false,
                'message' => 'Konten artikel kosong.',
                'dup_rate' => 0,
                'skor_keunikan' => 100,
                'percobaan_ke' => 1,
                'snips_dup' => [],
                'hasil' => [],
            ];
        }

        Log::info("UniqtextService: Menguji plagiasi artikel ID {$artikel->id}");

        try {
            $response = Http::withoutVerifying()
                ->timeout(60)
                ->asForm()
                ->post(self::CHECK_API_URL, [
                    'email' => $email,
                    'tokenapi' => $tokenApi,
                    'cmd' => 'sh_result',
                    'article' => $cleanText,
                    'whitelist' => (string) $whitelist,
                ]);

            if (!$response->successful()) {
                Log::error("UniqtextService: API Uniqtext mengembalikan HTTP {$response->status()}", [
                    'artikel_id' => $artikel->id,
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => "HTTP {$response->status()}: Gagal menghubungi API Uniqtext.",
                    'dup_rate' => 0,
                    'skor_keunikan' => null,
                    'percobaan_ke' => CekDuplikasi::where('artikel_id', $artikel->id)->count() + 1,
                    'snips_dup' => [],
                    'hasil' => [],
                ];
            }

            $rawBody = trim($response->body());
            Log::info("UniqtextService: Raw response dari API Uniqtext (Artikel ID {$artikel->id})", [
                'body' => $rawBody,
            ]);

            // Decode robust untuk menangani bila respons berupa string JSON ganda, ada BOM, atau Content-Type bukan JSON murni
            $data = $response->json();
            if (!is_array($data)) {
                $cleanBody = preg_replace('/^[\xEF\xBB\xBF]+/', '', $rawBody);
                $decoded = json_decode($cleanBody, true);
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                if (!is_array($decoded) && preg_match('/\{.*\}/s', $cleanBody, $matches)) {
                    $decoded = json_decode($matches[0], true);
                    if (is_string($decoded)) {
                        $decoded = json_decode($decoded, true);
                    }
                }
                $data = is_array($decoded) ? $decoded : [];
            }

            if (isset($data['snips_dup']) && is_string($data['snips_dup'])) {
                $decodedSnips = json_decode($data['snips_dup'], true);
                if (is_array($decodedSnips)) {
                    $data['snips_dup'] = $decodedSnips;
                }
            }
            if (isset($data['hasil']) && is_string($data['hasil'])) {
                $decodedHasil = json_decode($data['hasil'], true);
                if (is_array($decodedHasil)) {
                    $data['hasil'] = $decodedHasil;
                }
            }

            $dupRate = isset($data['dup_rate']) ? (int) $data['dup_rate'] : (isset($data['percent_dup']) ? (int) $data['percent_dup'] : 0);
            $snipsDup = is_array($data['snips_dup'] ?? null) ? $data['snips_dup'] : [];
            $hasil = is_array($data['hasil'] ?? null) ? $data['hasil'] : [];

            $skorKeunikan = max(0, 100 - $dupRate);
            $percobaanKe = CekDuplikasi::where('artikel_id', $artikel->id)->count() + 1;

            $cekDuplikasi = CekDuplikasi::create([
                'artikel_id' => $artikel->id,
                'skor_keunikan' => $skorKeunikan,
                'kata_duplikat' => $snipsDup,
                'hasil' => $hasil,
                'percobaan_ke' => $percobaanKe,
            ]);

            Log::info("UniqtextService: Hasil uji plagiasi artikel ID {$artikel->id} tersimpan", [
                'dup_rate' => $dupRate,
                'skor_keunikan' => $skorKeunikan,
                'percobaan_ke' => $percobaanKe,
                'found' => count($hasil),
            ]);

            return [
                'success' => true,
                'cek_duplikasi_id' => $cekDuplikasi->id,
                'dup_rate' => $dupRate,
                'skor_keunikan' => $skorKeunikan,
                'percobaan_ke' => $percobaanKe,
                'snips_dup' => $snipsDup,
                'hasil' => $hasil,
            ];
        } catch (\Exception $e) {
            Log::error("UniqtextService: Exception saat menguji artikel ID {$artikel->id}: {$e->getMessage()}");

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'dup_rate' => 0,
                'skor_keunikan' => null,
                'percobaan_ke' => CekDuplikasi::where('artikel_id', $artikel->id)->count() + 1,
                'snips_dup' => [],
                'hasil' => [],
            ];
        }
    }

    /**
     * Mengirimkan laporan hasil pengecekan agar tersimpan di Web Covenant.
     * Dinonaktifkan sementara karena kendala error dari server Covenant (Team_model.php).
     */
    public function sendTeamReport(string $content, int $found, int $length, int $remainingQuota): void
    {
        Log::info('UniqtextService: Pengiriman team report dinonaktifkan.');
        return;
    }

    /**
     * Cek sisa kuota pengecekan dari Uniqtext API.
     */
    public function checkQuota(): array
    {
        $email = config('services.uniqtext.email');
        $tokenApi = config('services.uniqtext.token_api');

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->asForm()
                ->post(self::CHECK_API_URL, [
                    'email' => $email,
                    'tokenapi' => $tokenApi,
                    'cmd' => 'sh_quota',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => "HTTP {$response->status()}",
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function getRemainingQuotaQuick(): int
    {
        $quota = $this->checkQuota();
        if ($quota['success'] && isset($quota['data']['quota'])) {
            return (int) $quota['data']['quota'];
        }
        return 0;
    }
}
