<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class BookChapter extends Model
{
    use HasSEO;
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
        if (!$this->description) {
            $siteName = setting('site_name');
            return trim("Soạn bài $this->name - {$this->book->group->name} {$this->book->group->category->name}, giải bài tập tất cả các môn học trên $siteName, cách trình bày dễ hiểu, khoa học.");
        }
        $description = html_entity_decode(strip_tags($this->description));
        return strlen($description) > $length ? substr($description, 0, $length) . '...' : $description;
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->name . ' | ' . $this->book->name . ' | ' . $this->book->group->name . ' ' . $this->book->group->category->name . ' | ' . setting('site_name'),
            description: $this->getDescriptionSnippet(160),
        );
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
