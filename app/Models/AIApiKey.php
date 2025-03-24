<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIApiKey extends Model
{
    use HasFactory;
    
    protected $table = 'ai_api_keys';
    
    protected $fillable = [
        'provider',
        'api_key',
        'email',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
    
    /**
     * Get a random active API key for a provider
     *
     * @param string $provider
     * @return \App\Models\AIApiKey|null
     */
    public static function getRandomKeyForProvider($provider)
    {
        return self::where('provider', $provider)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();
    }
    
    /**
     * Get all active providers that have at least one API key
     *
     * @return array
     */
    public static function getActiveProviders()
    {
        return self::where('is_active', true)
            ->groupBy('provider')
            ->pluck('provider')
            ->toArray();
    }
}
