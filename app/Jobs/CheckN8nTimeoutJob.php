<?php

namespace App\Jobs;

use App\Events\N8nTimeoutDetected;
use App\Models\PerintahArtikel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckN8nTimeoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public readonly int $perintahArtikelId)
    {
    }

    public function handle(): void
    {
        $perintah = PerintahArtikel::find($this->perintahArtikelId);

        if (!$perintah) {
            Log::warning("CheckN8nTimeoutJob: PerintahArtikel ID {$this->perintahArtikelId} tidak ditemukan.");
            return;
        }

        if ($perintah->status !== 'pending') {
            Log::info("CheckN8nTimeoutJob: perintah_artikel ID {$perintah->id} sudah berstatus '{$perintah->status}', skip timeout check.");
            return;
        }
        if (empty($perintah->n8n_execution_id)) {
            Log::warning("CheckN8nTimeoutJob: perintah_artikel ID {$perintah->id} tidak punya n8n_execution_id, tandai sebagai timeout.");

            $perintah->update([
                'status' => 'timeout',
            ]);

            broadcast(new N8nTimeoutDetected(
                perintahId: $perintah->id,
                executionId: '(tidak tersedia)',
                n8nStatus: null,
                message: 'Tidak ada respon dari N8N setelah 60 detik. Execution ID tidak diketahui.',
            ));

            return;
        }

        $executionId = $perintah->n8n_execution_id;
        $apiUrl = "https://andy-biform-flukily.ngrok-free.dev/api/v1/executions/{$executionId}?includeData=false";

        Log::info("CheckN8nTimeoutJob: Mengecek eksekusi n8n ID {$executionId} untuk perintah_artikel ID {$perintah->id}");

        $response = Http::withoutVerifying()
            ->withHeaders([
                'X-N8N-API-KEY' => config('services.n8n.api_key'),
            ])
            ->timeout(15)
            ->get($apiUrl);

        if ($response->successful()) {
            $data = $response->json();
            $n8nStatus = $data['status'] ?? null;
            $n8nFinished = $data['finished'] ?? null;

            Log::info("CheckN8nTimeoutJob: Response n8n execution ID {$executionId}", [
                'status' => $n8nStatus,
                'finished' => $n8nFinished,
            ]);

            $statusToSet = ($n8nStatus === 'success') ? 'selesai' : 'timeout';
            $perintah->update([
                'status' => $statusToSet,
                'n8n_status' => $n8nStatus,
            ]);

            $message = match ($n8nStatus) {
                'error' => "N8N gagal membuat judul, silahkan generate judul lagi.",
                'success' => "N8N selesai membuat judul.",
                'running' => "N8N masih memproses eksekusi judul.",
            };

            broadcast(new N8nTimeoutDetected(
                perintahId: $perintah->id,
                executionId: $executionId,
                n8nStatus: $n8nStatus,
                message: $message,
            ));
        } else {
            Log::error("CheckN8nTimeoutJob: N8N API mengembalikan HTTP {$response->status()} untuk execution ID {$executionId}", [
                'body' => $response->body(),
            ]);

            $perintah->update([
                'status' => 'timeout',
            ]);

            broadcast(new N8nTimeoutDetected(
                perintahId: $perintah->id,
                executionId: $executionId,
                n8nStatus: 'api_error',
                message: "Timeout terdeteksi. Gagal mengecek status eksekusi #{$executionId} dari N8N API (HTTP {$response->status()}).",
            ));
        }
    }
}
