<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioAnalysis extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'text',
        'audio_path',
        'ai_score',
        'classification',
        'explanation',
        'status',
    ];

    protected $casts = [
        'ai_score'    => 'float',
        'explanation' => 'array',
        'created_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
