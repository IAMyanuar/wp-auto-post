<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerintahArtikel extends Model
{
    protected $table = 'perintah_artikel';

    protected $fillable = [
        'user_id',
        'website_klien_id',
        'topik',
        'jumlah_artikel',
        'use_cta',
        'n8n_execution_id',
        'status',
        'n8n_status',
    ];

    protected $casts = [
        'use_cta' => 'boolean',
    ];


    public function websiteKlien()
    {
        return $this->belongsTo(WebsiteKlien::class, 'website_klien_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
