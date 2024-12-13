<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleTag extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'article_tags';

    protected $fillable = ['name'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    /**
     * Get the articles associated with the tag.
     */
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_tag_article', 'article_tag_id', 'article_id');
    }
}
