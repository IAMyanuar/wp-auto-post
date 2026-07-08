<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtikelGambar extends Model
{
    protected $table = 'artikel_gambar';

    protected $fillable = [
        'artikel_id',
        'nama_gambar',
        'path',
        'alt_text',
        'is_featured',
        'wp_media_id',
        'wp_media_url',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'wp_media_id' => 'integer',
    ];

    public function artikel()
    {
        return $this->belongsTo(Artikel::class, 'artikel_id');
    }
}
