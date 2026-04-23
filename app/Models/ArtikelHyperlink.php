<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtikelHyperlink extends Model
{
    protected $table = 'artikel_hyperlink';

    protected $fillable = [
        'artikel_id',
        'url',
        'tipe',
    ];


    public function artikel()
    {
        return $this->belongsTo(Artikel::class, 'artikel_id');
    }
}
