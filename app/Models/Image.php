<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'name',
        'alt',
        'imageable_type',
        'imageable_id'
    ];

    public function imageable()
    {
        return $this->morphTo();
    }

    // Get full URL for the image
    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
}
