<?php

namespace Tests\Feature;

use App\Models\Artikel;
use App\Models\PerintahArtikel;
use App\Models\User;
use App\Models\WebsiteKlien;
use App\Jobs\ProsesDanKirimKeWordPressJob;
use App\Services\UniqtextService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EndToEndIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    private function createDummyArtikel(int $websiteId, array $overrides = []): Artikel
    {
        $user = User::factory()->create();

        $perintah = PerintahArtikel::create([
            'user_id' => $user->id,
            'website_klien_id' => $websiteId,
            'topik' => 'Topik E2E ' . uniqid(),
            'jumlah_artikel' => 1,
            'status' => 'pending',
        ]);

        return Artikel::create(array_merge([
            'perintah_artikel_id' => $perintah->id,
            'website_klien_id' => $websiteId,
            'judul' => 'Judul E2E ' . uniqid(),
            'status' => 'terjadwal',
        ], $overrides));
    }

    public function test_auth_and_dashboard_workflow(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        // 1. Uji Login
        $response = $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // 2. Uji Akses Dashboard
        $dashboardResponse = $this->actingAs($user)->get(route('dashboard'));
        $dashboardResponse->assertStatus(200)
            ->assertViewIs('pages.dashboard.index')
            ->assertViewHasAll(['totalArtikel', 'terjadwal', 'terpublish', 'gagal', 'totalWebsite']);
    }

    public function test_web_client_management_workflow(): void
    {
        $user = User::factory()->create();

        // 1. Buat Website Klien Baru
        $response = $this->actingAs($user)->post(route('web-client.store'), [
            'nama_website' => 'WordPress E2E Klien',
            'url_website' => 'https://wpe2e.test',
            'username' => 'admin_wp',
            'password' => 'pass123',
            'publikasi_otomatis' => true,
        ]);

        $response->assertRedirect(route('web-client.index'));
        $this->assertDatabaseHas('website_klien', [
            'nama_website' => 'WordPress E2E Klien',
            'url_website' => 'https://wpe2e.test',
        ]);
    }

    public function test_full_article_generation_and_publishing_workflow(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'WP Full Workflow',
            'url_website' => 'https://example-wp.test',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => true,
        ]);

        // 1. Uji Kirim Perintah Generate Judul ke N8N
        Http::fake([
            '*auto-post-generate-judul*' => Http::response(['execution_id' => 'exec-judul-100'], 200),
            '*' => Http::response([], 200),
        ]);

        $responseJudul = $this->actingAs($user)->post(route('penjadwalan.generate-judul'), [
            'website_klien_id' => $website->id,
            'topik' => 'Panduan WordPress Lengkap',
            'jumlah_artikel' => 1,
        ]);

        $responseJudul->assertRedirect(route('penjadwalan.index'));
        $this->assertDatabaseHas('perintah_artikel', [
            'website_klien_id' => $website->id,
            'topik' => 'Panduan WordPress Lengkap',
        ]);

        // 2. Buat Artikel Terjadwal & Uji Pengecekan Plagiasi
        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Cara Lengkap Optimasi WordPress',
            'konten' => '<p>Artikel orisinal yang disiapkan untuk dipublish.</p>',
        ]);

        Http::fake([
            '*uniquetext/api/check2*' => Http::response([
                'dup_rate' => 10,
                'snips_dup' => [],
                'hasil' => [],
            ], 200),
            '*uniquetext/api/teamreport*' => Http::response([], 200),
        ]);

        $cekPlagiasiResponse = $this->actingAs($user)->post(route('penjadwalan.cek-plagiasi', $artikel->id));
        $cekPlagiasiResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // 3. Uji Eksekusi Publikasi ke WordPress melalui Job
        Http::fake([
            '*uniquetext/api/check2*' => Http::response([
                'dup_rate' => 10,
                'snips_dup' => [],
                'hasil' => [],
            ], 200),
            '*uniquetext/api/teamreport*' => Http::response([], 200),
            '*wp-json/wp/v2/posts*' => Http::response([
                'id' => 9001,
                'link' => 'https://example-wp.test/cara-lengkap-optimasi-wordpress/',
            ], 201),
        ]);

        $job = new ProsesDanKirimKeWordPressJob($artikel->id);
        $job->handle();

        // Pastikan artikel berhasil terpublish & tersimpan URL WP-nya
        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'status' => 'terpublish',
            'wp_id' => '9001',
            'wp_url' => 'https://example-wp.test/cara-lengkap-optimasi-wordpress/',
        ]);
    }

    public function test_riwayat_and_retry_workflow(): void
    {
        Queue::fake();
        Event::fake();

        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'WP Riwayat Flow',
            'url_website' => 'https://example-wp.test',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $artikelGagal = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel Butuh Retry',
            'status' => 'gagal',
            'n8n_status' => 'error',
        ]);

        // 1. Pastikan Artikel Muncul di Halaman Riwayat
        $riwayatResponse = $this->actingAs($user)->get(route('riwayat.index'));
        $riwayatResponse->assertStatus(200)
            ->assertViewHas('artikels');

        // 2. Uji Retry Artikel Gagal
        Http::fake([
            '*auto-post-generate-konten*' => Http::response(['execution_id' => 'exec-retry-flow-99'], 200),
            '*' => Http::response([], 200),
        ]);

        $retryResponse = $this->actingAs($user)
            ->from(route('riwayat.index'))
            ->post(route('penjadwalan.retry', $artikelGagal->id));

        $retryResponse->assertRedirect(route('riwayat.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('artikel', [
            'id' => $artikelGagal->id,
            'status' => 'diproses',
            'n8n_status' => null,
        ]);
    }
}
