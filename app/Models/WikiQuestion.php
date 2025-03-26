<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Cviebrock\EloquentSluggable\Sluggable;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class WikiQuestion extends Model
{
    use HasSEO, HasFactory, Sluggable;

    protected $fillable = [
        'title',
        'content',
        'slug',
        'user_id',
        'category_id',
        'book_group_id',
        'status',
        'views',
    ];

    protected $casts = [
        'views' => 'integer',
    ];

    /**
     * Return the sluggable configuration array for this model.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function getDescriptionSnippet($length = 100)
    {
        if ($this->answers->count() > 0) {
            $description = html_entity_decode(strip_tags($this->answers->first()->content));
        } else {
            $description = html_entity_decode(strip_tags($this->content));
        }

        return strlen($description) > $length ? substr($description, 0, $length) . '...' : $description;
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->title . ' | ' . setting('site_name'),
            description: $this->getDescriptionSnippet(160),
        );
    }

    /**
     * Get the user that owns the question.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the question.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the book group that owns the question.
     */
    public function bookGroup(): BelongsTo
    {
        return $this->belongsTo(BookGroup::class);
    }

    /**
     * Get the answers for the question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(WikiAnswer::class, 'question_id');
    }

    /**
     * Get the comments for the question.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(WikiComment::class, 'question_id');
    }

    /**
     * Get the embedding for the question.
     */
    public function embedding(): HasOne
    {
        return $this->hasOne(WikiQuestionEmbedding::class, 'question_id');
    }

    /**
     * Scope a query to only include published questions.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Increment the view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }
}
