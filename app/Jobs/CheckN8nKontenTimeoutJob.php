<?php

namespace App\Jobs;

use App\Events\N8nKontenTimeoutDetected;
use App\Models\Artikel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckN8nKontenTimeoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public readonly int $artikelId)
    {
    }

    public function handle(): void
    {
        $artikel = Artikel::find($this->artikelId);

        if (!$artikel) {
            Log::warning("CheckN8nKontenTimeoutJob: Artikel ID {$this->artikelId} tidak ditemukan.");
            return;
        }

        // Jika konten sudah terisi atau status tidak lagi 'diproses', berarti callback sudah tiba atau selesai
        if ($artikel->status !== 'diproses' || !empty($artikel->konten)) {
            Log::info("CheckN8nKontenTimeoutJob: Artikel ID {$artikel->id} sudah berstatus '{$artikel->status}' dan konten sudah diproses, skip timeout check.");
            return;
        }

        if (empty($artikel->n8n_execution_id)) {
            Log::warning("CheckN8nKontenTimeoutJob: Artikel ID {$artikel->id} tidak punya n8n_execution_id, tandai sebagai gagal/timeout agar tidak memblokir antrean.");

            $artikel->update([
                'status' => 'gagal',
            ]);

            broadcast(new N8nKontenTimeoutDetected(
                artikelId: $artikel->id,
                executionId: '(tidak tersedia)',
                n8nStatus: null,
                message: 'Tidak ada respon dari N8N setelah 60 detik saat membuat konten.',
            ));

            return;
        }

        $executionId = $artikel->n8n_execution_id;
        $apiUrl = "https://andy-biform-flukily.ngrok-free.dev/api/v1/executions/{$executionId}?includeData=false";

        Log::info("CheckN8nKontenTimeoutJob: Mengecek eksekusi n8n ID {$executionId} untuk Artikel ID {$artikel->id}");

        $response = Http::withoutVerifying()
            ->withHeaders([
                'X-N8N-API-KEY' => config('services.n8n.api_key', env('N8N_API_TOKEN')),
            ])
            ->timeout(15)
            ->get($apiUrl);

        if ($response->successful()) {
            $data = $response->json();
            $n8nStatus = $data['status'] ?? null;
            $n8nFinished = $data['finished'] ?? null;

            Log::info("CheckN8nKontenTimeoutJob: Response n8n execution ID {$executionId}", [
                'status' => $n8nStatus,
                'finished' => $n8nFinished,
            ]);

            // Set ke gagal agar antrean berikutnya bisa diproses (1 per 1)
            $artikel->update([
                'status' => 'gagal',
                'n8n_status' => $n8nStatus,
            ]);

            $message = match ($n8nStatus) {
                'error' => "N8N mengalami error saat generate konten untuk artikel #{$artikel->id}.",
                'success' => "N8N selesai memproses artikel #{$artikel->id}, tetapi callback belum masuk.",
                'running' => "N8N masih memproses artikel #{$artikel->id} melebihi batas waktu 60 detik.",
                default => "Timeout terdeteksi untuk artikel #{$artikel->id} (Status N8N: {$n8nStatus}).",
            };

            broadcast(new N8nKontenTimeoutDetected(
                artikelId: $artikel->id,
                executionId: $executionId,
                n8nStatus: $n8nStatus,
                message: $message,
            ));
        } else {
            Log::error("CheckN8nKontenTimeoutJob: N8N API mengembalikan HTTP {$response->status()} untuk execution ID {$executionId}", [
                'body' => $response->body(),
            ]);

            $artikel->update([
                'status' => 'gagal',
            ]);

            broadcast(new N8nKontenTimeoutDetected(
                artikelId: $artikel->id,
                executionId: $executionId,
                n8nStatus: 'api_error',
                message: "Timeout terdeteksi saat generate konten artikel #{$artikel->id} (HTTP {$response->status()}).",
            ));
        }
    }
}
