<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;

class ArticleCategory extends Model
{
    use HasSEO;
    use HasFactory;
    use Sluggable;

    protected $table = 'article_categories';

    protected $fillable = ['name', 'description', 'slug'];

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

    public function getDynamicSEOData(): SEOData
    {
        return new SEOData(
            title: $this->name,
            description: $this->getDescriptionSnippet(160),
        );
    }

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
