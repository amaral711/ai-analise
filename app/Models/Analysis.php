<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analysis extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'text',
        'image_path',
        'ai_score',
        'classification',
        'explanation',
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
