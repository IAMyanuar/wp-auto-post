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

            $data = $response->json() ?? [];
            $dupRate = isset($data['dup_rate']) ? (int) $data['dup_rate'] : 0;
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

            $remainingQuota = $this->getRemainingQuotaQuick();
            $this->sendTeamReport(
                content: $cleanText,
                found: count($hasil),
                length: str_word_count($cleanText),
                remainingQuota: $remainingQuota
            );

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
     */
    public function sendTeamReport(string $content, int $found, int $length, int $remainingQuota): void
    {
        $uid = config('services.uniqtext.uid');

        if (empty($uid)) {
            Log::warning('UniqtextService: UNIQTEXT_UID tidak dikonfigurasi, lewati pengiriman teamreport.');
            return;
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->asForm()
                ->post(self::REPORT_API_URL, [
                    'timezone' => 'Asia/Bangkok',
                    'content' => $content,
                    'uid' => $uid,
                    'found' => $found,
                    'length' => $length,
                    'remaining_quota' => $remainingQuota,
                ]);

            Log::info('UniqtextService: Team report dikirim ke Covenant', [
                'status' => $response->status(),
            ]);
        } catch (\Exception $e) {
            Log::error("UniqtextService: Exception saat mengirim team report: {$e->getMessage()}");
        }
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
