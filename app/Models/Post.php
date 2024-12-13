<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    use Sluggable;

    protected $fillable = ['title', 'content', 'user_id', 'slug', 'book_chapter_id'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {
            $post->images()->delete();
        });
    }

    public function getContentSnippet($length = 100)
    {
        $content = html_entity_decode(strip_tags($this->content));
        return strlen($content) > $length ? substr($content, 0, $length) . '...' : $content;
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
}
