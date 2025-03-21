<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prompt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'prompt_text',
        'content_type',
        'ai_model',
        'is_active',
        'created_by_user_id',
        'system_message',
        'description'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user who created the prompt
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * Scope a query to only include active prompts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by content type
     */
    public function scopeForContentType($query, $contentType)
    {
        return $query->where('content_type', $contentType);
    }

    /**
     * Scope a query to filter by AI model
     */
    public function scopeForModel($query, $aiModel)
    {
        if ($aiModel) {
            return $query->where('ai_model', $aiModel);
        }
        return $query;
    }

    /**
     * Get formatted created date
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at->format('Y-m-d H:i');
    }

    /**
     * Get content type label
     */
    public function getContentTypeLabelAttribute()
    {
        $types = [
            'posts' => 'Posts',
            'chapters' => 'Book Chapters',
            'books' => 'Books',
            'book_groups' => 'Book Groups',
        ];

        return $types[$this->content_type] ?? ucfirst($this->content_type);
    }

    /**
     * Get a brief excerpt of the prompt
     */
    public function getPromptExcerptAttribute($length = 100)
    {
        if (strlen($this->prompt_text) <= $length) {
            return $this->prompt_text;
        }

        return substr($this->prompt_text, 0, $length) . '...';
    }
}
