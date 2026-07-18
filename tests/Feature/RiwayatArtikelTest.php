<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\PerintahArtikel;
use App\Models\User;
use App\Models\WebsiteKlien;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RiwayatArtikelTest extends TestCase
{
    use DatabaseTransactions;

    private function createDummyArtikel(int $websiteId, array $overrides = []): Artikel
    {
        $user = User::factory()->create();

        $perintah = PerintahArtikel::create([
            'user_id' => $user->id,
            'website_klien_id' => $websiteId,
            'topik' => 'Topik Dummy ' . uniqid(),
            'jumlah_artikel' => 1,
            'status' => 'pending',
        ]);

        return Artikel::create(array_merge([
            'perintah_artikel_id' => $perintah->id,
            'website_klien_id' => $websiteId,
            'judul' => 'Judul Dummy ' . uniqid(),
            'status' => 'terjadwal',
        ], $overrides));
    }

    public function test_index_riwayat_artikel(): void
    {
        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'WP Klien Riwayat',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel Riwayat Terpublish',
            'status' => 'terpublish',
        ]);

        $response = $this->actingAs($user)->get(route('riwayat.index'));

        $response->assertStatus(200)
            ->assertViewIs('pages.riwayat.index')
            ->assertViewHas('artikels');

        $artikels = $response->viewData('artikels');
        $this->assertTrue(collect($artikels->items())->contains('id', $artikel->id));
    }

    public function test_retry_artikel_gagal(): void
    {
        Queue::fake();
        Event::fake();

        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'WP Klien Retry',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel Gagal Untuk Retry',
            'status' => 'gagal',
            'n8n_status' => 'error',
        ]);

        Http::fake([
            '*auto-post-generate-konten*' => Http::response(['execution_id' => 'exec-retry-konten-101', 'success' => true], 200),
            '*' => Http::response([], 200),
        ]);

        $response = $this->actingAs($user)->from(route('riwayat.index'))->post(route('penjadwalan.retry', $artikel->id));

        $response->assertRedirect(route('riwayat.index'))
            ->assertSessionHas('success', 'Berhasil mengulangi proses generate konten!');

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'status' => 'diproses',
            'n8n_status' => null,
        ]);
    }
}
