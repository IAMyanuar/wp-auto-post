<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    protected $table = 'artikel';

    protected $fillable = [
        'perintah_artikel_id',
        'website_klien_id',
        'judul',
        'slug',
        'seo_title',
        'konten',
        'meta_deskripsi',
        'kata_kunci',
        'tags',
        'kategori',
        'status',
        'tanggal_jadwal',
        'tanggal_terbit',
        'use_cta',
        'wp_id',
        'wp_url',
        'n8n_execution_id',
        'n8n_status',
    ];

    protected $casts = [
        'tanggal_jadwal' => 'datetime',
        'tanggal_terbit' => 'datetime',
        'use_cta' => 'boolean',
        'tags' => 'array',
        'kategori' => 'array',
    ];


    public function websiteKlien()
    {
        return $this->belongsTo(WebsiteKlien::class, 'website_klien_id');
    }



    public function gambars()
    {
        return $this->hasMany(ArtikelGambar::class, 'artikel_id');
    }

    public function hyperlinks()
    {
        return $this->hasMany(ArtikelHyperlink::class, 'artikel_id');
    }

    public function gambarFeatured()
    {
        return $this->hasOne(ArtikelGambar::class, 'artikel_id')->oldestOfMany();
    }

    public function cekDuplikasi()
    {
        return $this->hasMany(CekDuplikasi::class, 'artikel_id');
    }

    public function cekDuplikasiTerakhir()
    {
        return $this->hasOne(CekDuplikasi::class, 'artikel_id')->latestOfMany();
    }


    public function isPublished(): bool
    {
        return $this->status === 'terpublish';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'terjadwal';
    }

    public function isFailed(): bool
    {
        return $this->status === 'gagal';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'diproses';
    }

    public function setKontenAttribute($value)
    {
        $this->attributes['konten'] = self::cleanDuplicateMarkers($value);
    }

    /**
     * Membersihkan tag penanda sorotan plagiasi/duplikat (<mark>, <span>) agar tidak terbawa saat save atau kirim ke WordPress.
     */
    public static function cleanDuplicateMarkers(?string $konten): string
    {
        if (empty($konten)) {
            return '';
        }

        // Hapus tag <mark class="dup-marker"...> dan <span class="dup-marker"...> tetapi pertahankan isi teksnya
        $cleaned = preg_replace('/<mark[^>]*class="[^"]*dup-marker[^"]*"[^>]*>(.*?)<\/mark>/is', '$1', $konten);
        $cleaned = preg_replace('/<span[^>]*class="[^"]*dup-marker[^"]*"[^>]*>(.*?)<\/span>/is', '$1', $cleaned);
        $cleaned = preg_replace('/<mark[^>]*data-plagiasi="true"[^>]*>(.*?)<\/mark>/is', '$1', $cleaned);

        return $cleaned;
    }
}
