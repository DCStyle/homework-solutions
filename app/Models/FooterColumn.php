<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterColumn extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function links()
    {
        return $this->hasMany(FooterLink::class)->orderBy('position');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
