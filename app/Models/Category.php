<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
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
        return $this->hasMany(BookGroup::class)->orderByDesc('created_at');
    }
}
