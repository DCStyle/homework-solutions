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

    protected $fillable = ['name', 'slug', 'book_id'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
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
