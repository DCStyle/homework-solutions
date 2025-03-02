<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'footer_column_id',
        'title',
        'url',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function column()
    {
        return $this->belongsTo(FooterColumn::class, 'footer_column_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
