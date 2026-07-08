<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class N8nTimeoutDetected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $perintahId,
        public readonly string $executionId,
        public readonly ?string $n8nStatus,
        public readonly string $message,
    ) {
    }

    /**
     * Nama event yang dikirim ke frontend (menjadi nama pada Echo.listen())
     */
    public function broadcastAs(): string
    {
        return 'N8nTimeoutDetected';
    }

    public function broadcastWith(): array
    {
        return [
            'perintah_id' => $this->perintahId,
            'execution_id' => $this->executionId,
            'n8n_status' => $this->n8nStatus,
            'message' => $this->message,
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('penjadwalan'),
        ];
    }
}
