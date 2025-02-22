<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookChapter extends Model
{
    use HasFactory;
    use Sluggable;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($chapter)
        {
            if ($chapter->forceDeleting) {
                $chapter->posts()->detach();
            }
        });
    }

    protected $fillable = [
        'name',
        'description',
        'slug',
        'book_id'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function getDescriptionSnippet($length = 100)
    {
        $description = html_entity_decode(strip_tags($this->description));
        return strlen($description) > $length ? substr($description, 0, $length) . '...' : $description;
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class)->orderBy('title');
    }
}
