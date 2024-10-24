<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'books';

    protected $fillable = ['name', 'slug', 'description', 'book_group_id'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function group()
    {
        return $this->belongsTo(BookGroup::class, 'book_group_id');
    }

    public function chapters()
    {
        return $this->hasMany(BookChapter::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
