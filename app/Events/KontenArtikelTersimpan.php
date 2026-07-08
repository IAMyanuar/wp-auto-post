<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KontenArtikelTersimpan implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $artikelId,
        public ?int $websiteKlienId = null,
        public ?string $status = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('penjadwalan'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'KontenArtikelTersimpan';
    }
}
