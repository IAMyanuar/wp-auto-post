<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAgentPrompt extends Model
{
    protected $table = 'ai_agent_prompt';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'prompt',
    ];

    public function artikels()
    {
        return $this->hasMany(Artikel::class, 'ai_agent_prompt_id');
    }
}
