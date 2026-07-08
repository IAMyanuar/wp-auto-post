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
}
