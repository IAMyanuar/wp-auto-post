<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Artikel extends Model
{
    protected $table = 'artikel';

    protected $fillable = [
        'website_klien_id',
        'ai_agent_prompt_id',
        'judul',
        'slug',
        'konten',
        'seo_title',
        'meta_deskripsi',
        'kata_kunci',
        'deskripsi_yoast',
        'skor_seo',
        'skor_readability',
        'tags',
        'kategori',
        'status',
        'keterangan_proses',
        'persentase_proses',
        'tanggal_jadwal',
        'tanggal_terbit',
        'wp_id',
        'wp_url',
        'use_cta',
    ];

    protected $casts = [
        'tanggal_jadwal' => 'datetime',
        'tanggal_terbit' => 'datetime',
        'skor_seo' => 'integer',
        'skor_readability' => 'integer',
        'use_cta' => 'boolean',
    ];



    public function websiteKlien()
    {
        return $this->belongsTo(WebsiteKlien::class, 'website_klien_id');
    }

    public function aiAgentPrompt()
    {
        return $this->belongsTo(AiAgentPrompt::class, 'ai_agent_prompt_id');
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
        return $this->hasOne(ArtikelGambar::class, 'artikel_id')->where('is_featured', true);
    }


    public function isPublished(): bool
    {
        return $this->status === 'terpublish';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'terjadwal';
    }
}
