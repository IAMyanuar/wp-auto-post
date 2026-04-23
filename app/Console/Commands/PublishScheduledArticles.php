<?php

namespace App\Console\Commands;

use App\Models\Artikel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublishScheduledArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'artikel:check-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and publish scheduled articles to WordPress';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Cari artikel dengan status terjadwal, yang waktu jadwalnya (tanggal_jadwal) <= sekarang
        $artikels = Artikel::with('websiteKlien')
            ->where('status', 'terjadwal')
            ->whereNotNull('tanggal_jadwal')
            ->where('tanggal_jadwal', '<=', now())
            ->whereNotNull('wp_id')
            ->get();

        if ($artikels->isEmpty()) {
            $this->info('Tidak ada artikel yang perlu di-publish saat ini.');
            return;
        }

        foreach ($artikels as $artikel) {
            $website = $artikel->websiteKlien;

            if (!$website) {
                $this->error("Website klien tidak ditemukan untuk artikel ID: {$artikel->id}");
                continue;
            }

            $wpApiUrl = "{$website->base_url}/wp-json/wp/v2/posts/{$artikel->wp_id}";

            try {
                // Update post di WordPress menjadi publish
                $response = Http::withoutVerifying()
                    ->withBasicAuth($website->username, $website->password)
                    ->timeout(15)
                    ->patch($wpApiUrl, [
                        'status' => 'publish'
                    ]);

                if ($response->successful()) {
                    // Update database lokal
                    $artikel->update([
                        'status' => 'terpublish',
                        'tanggal_terbit' => now(),
                    ]);

                    $this->info("Artikel ID {$artikel->id} berhasil di-publish.");
                } else {
                    $this->error("Gagal mempublish artikel ID {$artikel->id} ke WordPress. Response: " . $response->body());
                    Log::error("Gagal auto-publish WP", ['artikel_id' => $artikel->id, 'response' => $response->body()]);
                }
            } catch (\Exception $e) {
                $this->error("Exception saat publish artikel ID {$artikel->id}: " . $e->getMessage());
                Log::error("Exception auto-publish WP", ['artikel_id' => $artikel->id, 'exception' => $e->getMessage()]);
            }
        }
    }
}
