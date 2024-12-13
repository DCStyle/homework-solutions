<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'articles';

    protected $fillable = ['title', 'slug', 'content', 'article_category_id'];

    public function getContentSnippet($length = 100)
    {
        $content = html_entity_decode(strip_tags($this->content));
        return strlen($content) > $length ? substr($content, 0, $length) . '...' : $content;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($article) {
            $article->tags()->detach();
            $article->images()->delete();
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    /**
     * Get the tags associated with the article.
     */
    public function tags()
    {
        return $this->belongsToMany(ArticleTag::class, 'article_tag_article', 'article_id', 'article_tag_id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function getThumbnail()
    {
        $image = $this->images->first();
        if ($image) {
            return $image->url;
        }

        preg_match('/<img[^>]+src="([^">]+)"/', $this->content, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }



        return 'https://placehold.co/600x400?text=' . urlencode($this->title) . '&font=lobster';
    }
}
