<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CekDuplikasi extends Model
{
    protected $table = 'cek_duplikasi';

    protected $fillable = [
        'artikel_id',
        'skor_keunikan',
        'kata_duplikat',
        'hasil',
        'percobaan_ke',
    ];

    protected $casts = [
        'skor_keunikan' => 'integer',
        'kata_duplikat' => 'array',
        'hasil' => 'array',
        'percobaan_ke' => 'integer',
    ];


    public function artikel()
    {
        return $this->belongsTo(Artikel::class, 'artikel_id');
    }

    public function isUnik(int $threshold = 80): bool
    {
        return $this->skor_keunikan !== null && $this->skor_keunikan >= $threshold;
    }

    public function jumlahKataDuplikat(): int
    {
        return is_array($this->kata_duplikat) ? count($this->kata_duplikat) : 0;
    }
}
