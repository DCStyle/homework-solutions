<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    // Cache duration in minutes
    const CACHE_DURATION = 60; // 1 hour

    /**
     * Constructor to set headers and cache
     */
    public function __construct()
    {
        // Increase memory limit for sitemap generation
        ini_set('memory_limit', '256M');
    }

    /**
     * Generate the main sitemap index
     */
    public function index()
    {
        // Cache only the data, not the response
        $types = Cache::remember('sitemap.index.data', self::CACHE_DURATION, function () {
            return SitemapService::getSitemapTypes();
        });
        
        // Build the response with the cached data
        return response()
            ->view('sitemaps.db-index', compact('types'))
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Generate robots.txt file
     */
    public function robots()
    {
        $content = "User-agent: *\nDisallow:\nSitemap: " . url('sitemap.xml');
        return response($content)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Generate sitemap for a specific type and page
     */
    public function showType($type, $page = 1)
    {
        // Cache only the data, not the response
        $entries = Cache::remember("sitemap.{$type}.page.{$page}.data", self::CACHE_DURATION, function () use ($type, $page) {
            return SitemapService::getEntriesForTypePage($type, $page);
        });
        
        if ($entries->isEmpty()) {
            abort(404);
        }
        
        // Build the response with the cached data
        return response()
            ->view('sitemaps.db-entries', compact('entries'))
            ->header('Content-Type', 'text/xml');
    }
} 