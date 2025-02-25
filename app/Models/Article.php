<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class Article extends Model
{
    use HasSEO;
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

    public function exceprt($length = 200)
    {
        return Str::limit(strip_tags(html_entity_decode($this->content)), $length);
    }

    public function readTime()
    {
        $wordCount = str_word_count(strip_tags(html_entity_decode($this->content)));
        $minutes = floor($wordCount / 200);
        $seconds = floor($wordCount % 200 / (200 / 60));
        $time = '';
        if ($minutes) {
            $time .= $minutes . ' phút';
        }
        if ($seconds) {
            $time .= ' ' . $seconds . ' giây';
        }
        return $time;
    }

    public function getThumbnail()
    {
        // If not, check if the article has any images
        if ($this->images->count() > 0) {
            return Storage::url($this->images->first()->path);
        }

        // If not, check if article content contains any images
        $matches = [];
        preg_match_all('/<img[^>]+>/i', $this->content, $matches);
        if (count($matches) > 0) {
            $img = (isset($matches[0][0])) ? $matches[0][0] : '';
            preg_match('/src="([^"]+)"/', $img, $src);
            return $src[1] ?? 'https://placehold.co/300?text=' . urlencode($this->title);
        }

        // If not, return a default image
        return 'https://placehold.co/300?text=' . urlencode($this->title);
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->title . ' | ' . setting('site_name', 'Homework Solutions'),
            description: $this->exceprt(160),
            image: $this->getThumbnail()
        );
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
}
