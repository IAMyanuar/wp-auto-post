<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteKlien extends Model
{
    protected $table = 'website_klien';

    protected $fillable = [
        'nama_website',
        'url_website',
        'username',
        'password',
        'publikasi_otomatis',
        'no_telpon',
        'alamat',
    ];

    protected $casts = [
        'publikasi_otomatis' => 'boolean',
        'password' => 'encrypted',
    ];

    public function artikels()
    {
        return $this->hasMany(Artikel::class, 'website_klien_id');
    }

    public function getBaseUrlAttribute()
    {
        return self::extractBaseUrl($this->url_website);
    }

    public static function extractBaseUrl($url)
    {
        $parsed = parse_url($url);
        $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : 'https://';
        $host = isset($parsed['host']) ? $parsed['host'] : '';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        
        return $scheme . $host . $port;
    }
}
