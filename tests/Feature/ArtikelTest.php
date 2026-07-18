<?php

namespace Tests\Feature;

use App\Events\JudulArtikelTersimpan;
use App\Events\KontenArtikelTersimpan;
use App\Jobs\CheckN8nTimeoutJob;
use App\Jobs\ProsesDanKirimKeWordPressJob;
use App\Models\Artikel;
use App\Models\ArtikelGambar;
use App\Models\CekDuplikasi;
use App\Models\PerintahArtikel;
use App\Models\User;
use App\Models\WebsiteKlien;
use App\Services\ArtikelService;
use App\Services\UniqtextService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ArtikelTest extends TestCase
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

    public function test_store_perintah_artikel_with_valid_data(): void
    {
        Queue::fake();
        Event::fake();

        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'Website Klien A',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        Http::fake([
            '*' => Http::response([
                'execution_id' => 'exec-n8n-12345',
                'success' => true,
            ], 200),
        ]);

        $response = $this->actingAs($user)->postJson(route('penjadwalan.generate-judul'), [
            'topik_konten' => 'Tips Kesehatan Mengatur Pola Tidur',
            'jumlah_konten' => 3,
            'website_klien_id' => $website->id,
            'use_cta' => 1,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('perintah_artikel', [
            'topik' => 'Tips Kesehatan Mengatur Pola Tidur',
            'jumlah_artikel' => 3,
            'website_klien_id' => $website->id,
            'n8n_execution_id' => 'exec-n8n-12345',
        ]);
    }

    public function test_store_perintah_artikel_with_empty_prompt(): void
    {
        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'Website Klien B',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $response = $this->actingAs($user)->post(route('penjadwalan.generate-judul'), [
            'topik_konten' => '',
            'jumlah_konten' => 2,
            'website_klien_id' => $website->id,
        ]);

        $response->assertSessionHasErrors(['topik_konten']);
    }

    public function test_update_artikel(): void
    {
        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'Website Klien Update',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Judul Sebelum Update',
            'konten' => 'Konten lama',
        ]);

        $response = $this->actingAs($user)->put(route('penjadwalan.update', $artikel), [
            'judul' => 'Judul Setelah Update',
            'website_klien_id' => $website->id,
            'status' => 'terjadwal',
            'konten' => 'Konten baru yang diperbarui',
        ]);

        $response->assertRedirect(route('penjadwalan.index'));

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'judul' => 'Judul Setelah Update',
            'konten' => 'Konten baru yang diperbarui',
        ]);
    }

    public function test_delete_artikel(): void
    {
        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'Website Klien Hapus',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Judul Untuk Dihapus',
        ]);

        $response = $this->actingAs($user)->delete(route('penjadwalan.destroy', $artikel));

        $response->assertRedirect();

        $this->assertDatabaseMissing('artikel', [
            'id' => $artikel->id,
        ]);
    }

    public function test_send_perintah_to_n8n_success(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'Website Klien N8N',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        Http::fake([
            '*' => Http::response(['execution_id' => 'exec-success-001', 'success' => true], 200),
        ]);

        $service = app(ArtikelService::class);
        $result = $service->generateJudul(
            userId: $user->id,
            websiteKlienId: $website->id,
            topik: 'Topik Sukses N8N',
            jumlahArtikel: 2
        );

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('perintah_artikel', [
            'topik' => 'Topik Sukses N8N',
            'n8n_execution_id' => 'exec-success-001',
        ]);
    }

    public function test_send_perintah_to_n8n_failed(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'Website Klien N8N Fail',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        Http::fake([
            '*' => Http::response(['message' => 'Internal Server Error'], 500),
        ]);

        $service = app(ArtikelService::class);
        $result = $service->generateJudul(
            userId: $user->id,
            websiteKlienId: $website->id,
            topik: 'Topik Gagal N8N',
            jumlahArtikel: 2
        );

        $this->assertFalse($result['success']);
        $this->assertDatabaseMissing('perintah_artikel', [
            'topik' => 'Topik Gagal N8N',
        ]);
    }

    public function test_receive_generated_artikel_from_n8n(): void
    {
        Event::fake();

        config(['services.n8n.api_key' => 'test-secret-token']);

        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'Website Klien Webhook',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $perintah = PerintahArtikel::create([
            'user_id' => $user->id,
            'website_klien_id' => $website->id,
            'topik' => 'Topik Webhook',
            'jumlah_artikel' => 2,
            'status' => 'pending',
        ]);

        $payload = [
            'website_klien_id' => $website->id,
            'perintah_artikel_id' => $perintah->id,
            'judul' => [
                'Judul Artikel N8N Pertama',
                'Judul Artikel N8N Kedua',
            ],
        ];

        $response = $this->withHeaders([
            'X-N8N-API-KEY' => 'test-secret-token',
        ])->postJson(route('api.webhook.n8n.judul-result'), $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('artikel', [
            'judul' => 'Judul Artikel N8N Pertama',
            'perintah_artikel_id' => $perintah->id,
        ]);

        $this->assertDatabaseHas('artikel', [
            'judul' => 'Judul Artikel N8N Kedua',
            'perintah_artikel_id' => $perintah->id,
        ]);

        $this->assertDatabaseHas('perintah_artikel', [
            'id' => $perintah->id,
            'status' => 'selesai',
        ]);
    }

    public function test_receive_invalid_payload_from_n8n(): void
    {
        config(['services.n8n.api_key' => 'test-secret-token']);

        $response = $this->withHeaders([
            'X-N8N-API-KEY' => 'test-secret-token',
        ])->postJson(route('api.webhook.n8n.judul-result'), []);

        $response->assertStatus(422);
    }

    public function test_uniquetext_returns_unique_content(): void
    {
        $website = WebsiteKlien::create([
            'nama_website' => 'Website Uniqtext Unik',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel Unik',
            'konten' => '<p>Konten artikel yang sangat unik dan berkualitas tinggi.</p>',
        ]);

        Http::fake([
            '*uniquetext/api/check2*' => Http::response([
                'dup_rate' => 10,
                'snips_dup' => [],
                'hasil' => [],
            ], 200),
            '*uniquetext/api/teamreport*' => Http::response([], 200),
        ]);

        $service = app(UniqtextService::class);
        $result = $service->checkArticle($artikel);

        $this->assertTrue($result['success']);
        $this->assertEquals(10, $result['dup_rate']);
        $this->assertEquals(90, $result['skor_keunikan']);

        $this->assertDatabaseHas('cek_duplikasi', [
            'artikel_id' => $artikel->id,
            'skor_keunikan' => 90,
        ]);
    }

    public function test_uniquetext_returns_duplicate_content(): void
    {
        Event::fake();

        $website = WebsiteKlien::create([
            'nama_website' => 'Website Uniqtext Duplikat',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel Duplikat',
            'konten' => '<p>Konten artikel duplikat yang sudah ada di internet.</p>',
            'status' => 'diproses',
        ]);

        Http::fake([
            '*uniquetext/api/check2*' => Http::response([
                'dup_rate' => 65,
                'snips_dup' => ['Konten artikel duplikat'],
                'hasil' => ['Match 1'],
            ], 200),
            '*uniquetext/api/teamreport*' => Http::response([], 200),
            '*auto-post-generate-konten*' => Http::response(['execution_id' => 'exec-retry-123'], 200),
            '*' => Http::response([], 200),
        ]);

        $service = app(UniqtextService::class);
        $result = $service->checkArticle($artikel);

        $this->assertTrue($result['success']);
        $this->assertEquals(65, $result['dup_rate']);
        $this->assertEquals(35, $result['skor_keunikan']);
        $this->assertLessThan(50, $result['skor_keunikan']);

        $this->assertDatabaseHas('cek_duplikasi', [
            'artikel_id' => $artikel->id,
            'skor_keunikan' => 35,
        ]);

        $job = new ProsesDanKirimKeWordPressJob($artikel->id);
        $job->handle();

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'status' => 'diproses',
        ]);

        Http::assertSent(function ($request) use ($artikel) {
            return str_contains($request->url(), 'auto-post-generate-konten') &&
                (int) $request['artikel_id'] === (int) $artikel->id &&
                ($request['is_retry_duplikat'] ?? false) === true;
        });
    }

    public function test_uniquetext_api_unavailable(): void
    {
        $website = WebsiteKlien::create([
            'nama_website' => 'Website Uniqtext Error',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel Cek Unavailable',
            'konten' => '<p>Konten untuk pengujian API unavailable.</p>',
        ]);

        Http::fake([
            '*uniquetext/api/check2*' => Http::response('Server Error', 500),
        ]);

        $service = app(UniqtextService::class);
        $result = $service->checkArticle($artikel);

        $this->assertFalse($result['success']);
    }

    public function test_publish_artikel_to_wordpress_success(): void
    {
        Event::fake();

        $website = WebsiteKlien::create([
            'nama_website' => 'WP Sukses',
            'url_website' => 'https://wpsukses.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => true,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel WordPress Sukses',
            'konten' => 'Isi artikel wordpress sukses dan unik',
            'status' => 'diproses',
            'tanggal_jadwal' => now()->subMinute(),
        ]);

        Http::fake([
            '*uniquetext/api/check2*' => Http::response(['dup_rate' => 5], 200),
            '*uniquetext/api/teamreport*' => Http::response([], 200),
            '*wp-json/wp/v2/posts*' => Http::response([
                'id' => 101,
                'link' => 'https://wpsukses.com/post-101',
            ], 201),
        ]);

        $job = new ProsesDanKirimKeWordPressJob($artikel->id);
        $job->handle();

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'status' => 'terpublish',
            'wp_id' => 101,
            'wp_url' => 'https://wpsukses.com/post-101',
        ]);
    }

    public function test_publish_artikel_with_invalid_credentials(): void
    {
        Event::fake();

        $website = WebsiteKlien::create([
            'nama_website' => 'WP Invalid Creds',
            'url_website' => 'https://wpinvalid.com',
            'username' => 'admin_wrong',
            'password' => 'wrongpass',
            'publikasi_otomatis' => true,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel WP Invalid Credentials',
            'konten' => 'Konten untuk pengujian invalid credentials',
            'status' => 'diproses',
            'tanggal_jadwal' => now()->subMinute(),
        ]);

        Http::fake([
            '*uniquetext/api/check2*' => Http::response(['dup_rate' => 5], 200),
            '*uniquetext/api/teamreport*' => Http::response([], 200),
            '*wp-json/wp/v2/posts*' => Http::response(['message' => 'Invalid credentials'], 401),
        ]);

        $job = new ProsesDanKirimKeWordPressJob($artikel->id);
        $job->handle();

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'status' => 'gagal',
        ]);
    }

    public function test_publish_artikel_wordpress_unreachable(): void
    {
        Event::fake();

        $website = WebsiteKlien::create([
            'nama_website' => 'WP Unreachable',
            'url_website' => 'https://wpunreachable.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => true,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel WP Unreachable',
            'konten' => 'Konten untuk pengujian unreachable WordPress',
            'status' => 'diproses',
            'tanggal_jadwal' => now()->subMinute(),
        ]);

        Http::fake([
            '*uniquetext/api/check2*' => Http::response(['dup_rate' => 5], 200),
            '*uniquetext/api/teamreport*' => Http::response([], 200),
            '*wp-json/wp/v2/posts*' => Http::response('Server Error', 500),
        ]);

        $job = new ProsesDanKirimKeWordPressJob($artikel->id);
        $job->handle();

        $this->assertDatabaseHas('artikel', [
            'id' => $artikel->id,
            'status' => 'gagal',
        ]);
    }

    public function test_upload_gambar_to_wordpress_success(): void
    {
        Event::fake();

        $website = WebsiteKlien::create([
            'nama_website' => 'WP Gambar Sukses',
            'url_website' => 'https://wpgambar.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => true,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel Gambar Sukses',
            'konten' => 'Konten dengan gambar',
            'status' => 'diproses',
            'tanggal_jadwal' => now()->subMinute(),
        ]);

        $dir = storage_path('app/public');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $filePath = storage_path('app/public/test-gambar-unit.gif');
        file_put_contents($filePath, base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));

        $gambar = ArtikelGambar::create([
            'artikel_id' => $artikel->id,
            'nama_gambar' => 'test-gambar-unit.gif',
            'path' => 'test-gambar-unit.gif',
            'alt_text' => 'Alt text tes',
        ]);

        Http::fake([
            '*wp-json/wp/v2/media*' => Http::response([
                'id' => 777,
                'source_url' => 'https://wpgambar.com/wp-content/uploads/test-gambar-unit.gif',
            ], 201),
            '*uniquetext/api/check2*' => Http::response(['dup_rate' => 0], 200),
            '*uniquetext/api/teamreport*' => Http::response([], 200),
            '*wp-json/wp/v2/posts*' => Http::response(['id' => 102, 'link' => 'https://wpgambar.com/post-102'], 201),
        ]);

        try {
            $job = new ProsesDanKirimKeWordPressJob($artikel->id);
            $job->handle();

            $this->assertDatabaseHas('artikel_gambar', [
                'id' => $gambar->id,
                'wp_media_id' => 777,
                'wp_media_url' => 'https://wpgambar.com/wp-content/uploads/test-gambar-unit.gif',
            ]);
        } finally {
            @unlink($filePath);
        }
    }

    public function test_upload_gambar_with_invalid_format(): void
    {
        $user = User::factory()->create();
        $website = WebsiteKlien::create([
            'nama_website' => 'WP Klien Invalid Img',
            'url_website' => 'https://example.com',
            'username' => 'admin',
            'password' => 'secret123',
            'publikasi_otomatis' => false,
        ]);

        $artikel = $this->createDummyArtikel($website->id, [
            'judul' => 'Artikel Cek Format Gambar',
        ]);

        $file = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->put(route('penjadwalan.update', $artikel), [
            'judul' => 'Artikel Cek Format Gambar',
            'website_klien_id' => $website->id,
            'gambar' => $file,
        ]);

        $response->assertSessionHasErrors(['gambar']);
    }
}
