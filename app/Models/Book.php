<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Book extends Model
{
    use HasFactory;
    use Sluggable;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($book)
        {
            if ($book->forceDeleting) {
                $book->chapters()->detach();
                $book->posts()->detach();
            }
        });
    }

    protected $table = 'books';

    protected $fillable = ['name', 'slug', 'description', 'book_group_id'];

    public function getDescriptionSnippet($length = 100)
    {
        $description = html_entity_decode(strip_tags($this->description));
        return strlen($description) > $length ? substr($description, 0, $length) . '...' : $description;
    }

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
        return $this->hasMany(BookChapter::class)
            ->orderByRaw("
            CASE
                WHEN name REGEXP '^Tuần (\\d+)' THEN
                    CAST(REGEXP_REPLACE(name, '^Tuần (\\d+).*$', '\\1') AS UNSIGNED)
                WHEN name REGEXP 'Tiết (\\d+)' THEN
                    CAST(REGEXP_REPLACE(name, 'Tiết (\\d+).*$', '\\1') AS UNSIGNED)
                WHEN name REGEXP '^Bài (\\d+)' THEN
                    CAST(REGEXP_REPLACE(name, '^Bài (\\d+).*$', '\\1') AS UNSIGNED)
                WHEN name REGEXP '.*trang (\\d+)' THEN
                    CAST(REGEXP_REPLACE(REGEXP_REPLACE(name, '.*trang (\\d+).*', '\\1'), '[^0-9]', '') AS UNSIGNED)
                ELSE NULL
            END,
            name
        ");
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
