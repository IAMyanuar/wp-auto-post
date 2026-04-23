<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebClientController;
use App\Http\Controllers\AiAgentPromptController;
use App\Http\Controllers\ArtikelController;
use App\Http\Controllers\N8nWebhookController;


// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('login.post');

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');


Route::post('/webhook/n8n/content', [N8nWebhookController::class, 'receiveContent'])
    ->name('webhook.n8n.content');

Route::post('/webhook/n8n/seo-result', [N8nWebhookController::class, 'receiveSeoResult'])
    ->name('webhook.n8n.seo-result');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Web Client Routes
    Route::get('/web-client', [WebClientController::class, 'index'])->name('web-client.index');
    Route::get('/web-client/create', [WebClientController::class, 'create'])->name('web-client.create');
    Route::post('/web-client', [WebClientController::class, 'store'])->name('web-client.store');
    Route::get('/web-client/{web_client}/edit', [WebClientController::class, 'edit'])->name('web-client.edit');
    Route::put('/web-client/{web_client}', [WebClientController::class, 'update'])->name('web-client.update');
    Route::delete('/web-client/{web_client}', [WebClientController::class, 'destroy'])->name('web-client.destroy');

    // AI Agent Prompt Routes
    Route::get('/ai-prompt', [AiAgentPromptController::class, 'index'])->name('ai-prompt.index');
    Route::get('/ai-prompt/create', [AiAgentPromptController::class, 'create'])->name('ai-prompt.create');
    Route::post('/ai-prompt', [AiAgentPromptController::class, 'store'])->name('ai-prompt.store');
    Route::get('/ai-prompt/{prompt}/edit', [AiAgentPromptController::class, 'edit'])->name('ai-prompt.edit');
    Route::put('/ai-prompt/{prompt}', [AiAgentPromptController::class, 'update'])->name('ai-prompt.update');
    Route::delete('/ai-prompt/{prompt}', [AiAgentPromptController::class, 'destroy'])->name('ai-prompt.destroy');

    // Penjadwalan Artikel Routes
    Route::get('/penjadwalan', [ArtikelController::class, 'index'])->name('penjadwalan.index');
    Route::get('/penjadwalan/create', [ArtikelController::class, 'create'])->name('penjadwalan.create');
    Route::post('/penjadwalan', [ArtikelController::class, 'store'])->name('penjadwalan.store');
    Route::get('/penjadwalan/{artikel}/edit', [ArtikelController::class, 'edit'])->name('penjadwalan.edit');
    Route::put('/penjadwalan/{artikel}', [ArtikelController::class, 'update'])->name('penjadwalan.update');
    Route::delete('/penjadwalan/{artikel}', [ArtikelController::class, 'destroy'])->name('penjadwalan.destroy');
    Route::post('/penjadwalan/{artikel}/retry', [ArtikelController::class, 'retry'])->name('penjadwalan.retry');
    Route::post('/penjadwalan/{artikel}/retry-yoast', [ArtikelController::class, 'retryYoast'])->name('penjadwalan.retryYoast');

    // Riwayat Artikel Routes
    Route::get('/riwayat', [ArtikelController::class, 'riwayat'])->name('riwayat.index');

    // Endpoint ringan untuk polling status artikel (hanya return id+status)
    Route::get('/penjadwalan/poll-status', [ArtikelController::class, 'pollStatus'])->name('penjadwalan.poll-status');
});
