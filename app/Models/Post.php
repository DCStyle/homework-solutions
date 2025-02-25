<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class Post extends Model
{
    use HasSEO;
    use HasFactory;
    use Sluggable;

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'slug',
        'book_chapter_id',
        'source_url'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {
            $post->images()->delete();

            $post->attachments()->delete();
        });
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

    public function getContentSnippet($length = 100)
    {
        $content = html_entity_decode(strip_tags($this->content));
        return strlen($content) > $length ? substr($content, 0, $length) . '...' : $content;
    }

    public function getDynamicSEOData(): SEOData
    {
        $siteName = setting('site_name');

        return new SEOData(
            title: $this->title . ' | ' . $this->chapter->book->name . ' | ' . $this->chapter->book->group->category->name . ' | ' . setting('site_name'),
            description: "Hướng dẫn học bài: $this->title - {$this->chapter->book->group->name}. Đây là sách giáo khoa nằm trong bộ sách '{$this->chapter->book->name} {$this->chapter->book->group->category->name}' trên $siteName được biên soạn theo chương trình đổi mới của Bộ giáo dục. Hi vọng, với cách hướng dẫn cụ thể và giải chi tiết các bé sẽ nắm bài học tốt hơn.",
            image: $this->getThumbnail()
        );
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUser()
    {
        return $this->user()->firstOrFail();
    }

    public function chapter()
    {
        return $this->belongsTo(BookChapter::class, 'book_chapter_id');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function attachments()
    {
        return $this->hasMany(PostAttachment::class);
    }
}
