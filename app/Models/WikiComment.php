<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WikiComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'user_id',
        'content',
        'parent_id',
    ];

    /**
     * Get the question that owns the comment.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(WikiQuestion::class, 'question_id');
    }

    /**
     * Get the user that created the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(WikiComment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(WikiComment::class, 'parent_id');
    }

    /**
     * Scope a query to only include root comments (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
} 