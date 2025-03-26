<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WikiAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'content',
        'user_id',
        'is_ai',
    ];

    protected $casts = [
        'is_ai' => 'boolean',
    ];

    /**
     * Get the question that owns the answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(WikiQuestion::class, 'question_id');
    }

    /**
     * Get the user that created the answer (if not AI).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 