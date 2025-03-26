<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class Category extends Model
{
    use HasSEO;
    use HasFactory;
    use Sluggable;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($category)
        {
            if ($category->forceDeleting) {
                $category->bookGroups()->delete();
            }
        });
    }

    protected $fillable = ['name', 'slug', 'parent_id', 'description'];

    public function getDescriptionSnippet($length = 100)
    {
        if (!$this->description) {
            $siteName = setting('site_name');
            return trim("Soạn bài $this->name, giải bài tập $this->name và tất cả các môn học trên $siteName, cách trình bày dễ hiểu, khoa học.");
        }

        $description = html_entity_decode(strip_tags($this->description));
        return strlen($description) > $length ? substr($description, 0, $length) . '...' : $description;
    }

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->name . ' - Giải bài tập SGK, VBT ' . $this->name . ' soạn bài với đáp án lời giải giúp để học tốt tất cả các môn' . ' | ' . setting('site_name'),
            description: $this->getDescriptionSnippet(160),
        );
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function bookGroups()
    {
        return $this->hasMany(BookGroup::class)->orderBy('created_at');
    }

    public function wikiQuestions()
    {
        return $this->hasMany(WikiQuestion::class);
    }
}
