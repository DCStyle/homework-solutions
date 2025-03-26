<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WikiQuestionEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'embedding',
    ];
    
    protected $casts = [
        'embedding' => 'array',
    ];

    /**
     * Get the question that owns the embedding.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(WikiQuestion::class, 'question_id');
    }
} 