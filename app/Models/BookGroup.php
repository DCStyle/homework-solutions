<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookGroup extends Model
{
    use HasFactory;
    use Sluggable;

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($group)
        {
            if ($group->forceDeleting) {
                $group->books()->detach();
            }
        });
    }

    protected $fillable = ['name', 'slug', 'description', 'category_id'];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function books()
    {
        return $this->hasMany(Book::class)->orderByDesc('created_at');
    }
}
