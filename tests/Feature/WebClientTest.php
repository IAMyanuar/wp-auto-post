<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WebsiteKlien;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebClientTest extends TestCase
{
    use DatabaseTransactions;


    public function test_store_website_klien_with_valid_data(): void
    {
        $user = User::factory()->create();
        //menggunakan respon dummy dari WordPress
        Http::fake([
            '*' => Http::response(['id' => 1, 'name' => 'Admin WP'], 200),
        ]);

        $response = $this->actingAs($user)->post('/web-client', [
            'nama_website' => 'Website Test Valid',
            'url_website' => 'https://webtestvalid.com',
            'username' => 'admin_test',
            'password' => 'password_app_123',
            'publikasi_otomatis' => 1,
        ]);

        $response->assertRedirect(route('web-client.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('website_klien', [
            'nama_website' => 'Website Test Valid',
            'url_website' => 'https://webtestvalid.com',
            'username' => 'admin_test',
        ]);
    }

    public function test_store_website_klien_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('web-client.store'), []);

        $response->assertSessionHasErrors(['nama_website', 'url_website', 'username', 'password']);
    }

    public function test_update_website_klien(): void
    {
        $user = User::factory()->create();

        $client = WebsiteKlien::create([
            'nama_website' => 'Website Lama',
            'url_website' => 'https://websitelama.com',
            'username' => 'admin_lama',
            'password' => 'rahasia123',
            'publikasi_otomatis' => false,
        ]);

        Http::fake([
            '*' => Http::response(['id' => 1, 'name' => 'Admin WP'], 200),
        ]);

        $response = $this->actingAs($user)->put('/web-client/' . $client->id, [
            'nama_website' => 'Website Baru Diperbarui',
            'url_website' => 'https://websitelama.com',
            'username' => 'admin_lama',
            'password' => 'rahasia123',
            'publikasi_otomatis' => true,
        ]);

        $response->assertRedirect(route('web-client.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('website_klien', [
            'id' => $client->id,
            'nama_website' => 'Website Baru Diperbarui',
        ]);
    }

    public function test_delete_website_klien(): void
    {
        $user = User::factory()->create();

        $client = WebsiteKlien::create([
            'nama_website' => 'Website Untuk Dihapus',
            'url_website' => 'https://websitedihapus.com',
            'username' => 'admin_hapus',
            'password' => 'rahasia123',
            'publikasi_otomatis' => false,
        ]);

        $response = $this->actingAs($user)->delete(route('web-client.destroy', $client));

        $response->assertRedirect(route('web-client.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('website_klien', [
            'id' => $client->id,
        ]);
    }
}
