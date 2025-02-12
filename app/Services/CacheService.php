<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class CacheService
{
    public function getTotalCacheSize(): string
    {
        $cacheDir = storage_path('framework/cache/data');
        $size = $this->getDirSize($cacheDir);
        return $this->formatBytes($size);
    }

    private function getDirSize($dir): int
    {
        $size = 0;
        foreach (File::allFiles($dir) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    public function getLastCacheUrl(): string
    {
        return Cache::get('last_cached_url', 'N/A');
    }

    public function getLastCacheTime(): string
    {
        $timestamp = Cache::get('last_cache_time');
        if (!$timestamp) return 'N/A';

        return now()->diffForHumans($timestamp);
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
