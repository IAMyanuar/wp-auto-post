<?php

use App\Http\Controllers\N8nWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api-token'])->group(function () {
    Route::post('/webhook/n8n/judul-result', [N8nWebhookController::class, 'receiveJudulResult'])
        ->name('api.webhook.n8n.judul-result');
    Route::post('/webhook/n8n/konten-result', [N8nWebhookController::class, 'receiveKontenResult'])
        ->name('api.webhook.n8n.konten-result');
});
