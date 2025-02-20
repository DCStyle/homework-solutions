<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MenuItem extends Model
{
    protected $fillable = [
        'name',
        'url',
        'icon',
        'type',
        'parent_id',
        'order',
        'active',
        'category_id'
    ];

    protected static function boot()
    {
        parent::boot();

        // Clear cache when a menu item is created, updated, or deleted
        static::saved(function () {
            Cache::forget('menu_items');
        });

        static::deleted(function () {
            Cache::forget('menu_items');
        });
    }

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->orderBy('order');
    }

    // Recursive relationship for all nested children
    public function allChildren()
    {
        return $this->children()->with('allChildren');
    }

    // Scope for top-level items
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id')->orderBy('order');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
