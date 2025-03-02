<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class PostsController extends Controller
{
    // Cache duration in minutes
    private const CACHE_DURATION = 60;
    
    // Additional domains to replace in content
    private const ADDITIONAL_DOMAINS = [
        'loigiaihay.com',
        'toanmath.com',
        'thcs.toanmath.com'
    ];
    
    // Case-sensitive domain replacements (exact matches to preserve case sensitivity)
    private const CASE_SENSITIVE_DOMAINS = [
        'TOANMATH.com'
    ];

    /**
     * Get post content from database or external source
     */
    private function getPostContent(Post $post)
    {
        // Return early if no content sources are available
        if ($post->content == null && $post->source_url == null) {
            return null;
        }

        // Return content if already available
        if ($post->content !== null) {
            return $post->content;
        }

        // Use cache to avoid repeated external requests
        $cacheKey = 'post_content_' . $post->id;
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($post) {
            return $this->fetchExternalContent($post);
        });
    }

    /**
     * Fetch content from external source
     */
    private function fetchExternalContent(Post $post)
    {
        // Base64 encode the full URL
        $encodedUrl = base64_encode($post->source_url);
        $proxyUrl = 'https://yopovn.com/proxy/?url=' . $encodedUrl;

        Log::info('Making proxy request', [
            'post_id' => $post->id,
            'original_url' => $post->source_url,
            'proxy_url' => $proxyUrl
        ]);

        try {
            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => false,
                    'connect_timeout' => 30,
                    'timeout' => 30
                ])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124',
                    'Accept' => '*/*',
                    'Accept-Language' => 'en-US,en;q=0.9,vi;q=0.8',
                    'Cache-Control' => 'no-cache'
                ])
                ->get($proxyUrl);

            $content = $this->extractContentFromResponse($response->body());
            
            // Source and target domains for replacement
            $sourceBaseUrl = parse_url($post->source_url, PHP_URL_HOST);
            $ourBaseUrl = parse_url(config('app.url'), PHP_URL_HOST);

            // Process the content
            $content = $this->replaceLinks($content, $sourceBaseUrl, $ourBaseUrl);
            $content = $this->removeUnwantedElements($content);
            
            // Apply a final URL fix to ensure all problematic URLs are fixed before saving
            $content = $this->fixProblematicUrls($content);

            // Save the processed content
            $post->content = $content;
            $post->save();

            return $content;
        } catch (\Exception $e) {
            Log::error('Proxy request failed', [
                'post_id' => $post->id,
                'url' => $post->source_url,
                'proxy_url' => $proxyUrl,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Extract content from HTML response
     */
    private function extractContentFromResponse($html)
    {
        $crawler = new Crawler($html);
        
        try {
            $content = $crawler->filter('.detail_new #box-content')->html();
        } catch (\Exception $e) {
            try {
                $content = $crawler->filter('.box_content .detail_new')->html();
            } catch (\Exception $e) {
                Log::warning('Failed to extract content using primary selectors', [
                    'error' => $e->getMessage()
                ]);
                // Return empty content if both selectors fail
                return '';
            }
        }

        // Remove all script tags immediately
        return preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $content);
    }

    /**
     * Replace links and domain references in content
     */
    public function replaceLinks($content, $sourceBaseUrl, $ourBaseUrl)
    {
        // Skip processing if content is empty
        if (empty($content)) {
            return $content;
        }

        // Create a DOM Document to properly manipulate the HTML
        $dom = new \DOMDocument();

        // Disable libxml errors temporarily to prevent issues with malformed HTML
        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        // Process text nodes only
        $xpath = new \DOMXPath($dom);
        $textNodes = $xpath->query('//text()[not(ancestor::a) and not(ancestor::img)]');

        foreach ($textNodes as $textNode) {
            // Skip if the node is empty or only whitespace
            if (trim($textNode->nodeValue) === '') {
                continue;
            }

            $newValue = $textNode->nodeValue;
            
            // Replace the source base URL with our base URL in text
            $newValue = str_replace($sourceBaseUrl, $ourBaseUrl, $newValue);

            // Replace additional case-insensitive domains
            foreach (self::ADDITIONAL_DOMAINS as $domain) {
                $newValue = str_ireplace($domain, $ourBaseUrl, $newValue);
            }
            
            // Replace case-sensitive domains (exact match)
            foreach (self::CASE_SENSITIVE_DOMAINS as $domain) {
                $newValue = str_replace($domain, $ourBaseUrl, $newValue);
            }

            $textNode->nodeValue = $newValue;
        }

        // Get the modified content
        $content = $dom->saveHTML();
        
        // Apply regex replacements for domain references outside of attributes
        $content = $this->applyDomainRegexReplacements($content, $sourceBaseUrl, $ourBaseUrl);
        
        // Remove links from source domains
        $content = $this->removeExternalLinks($content, $sourceBaseUrl);
        
        // Fix problematic URLs
        $content = $this->fixProblematicUrls($content);

        return $content;
    }

    /**
     * Apply regex replacements for domain references
     */
    private function applyDomainRegexReplacements($content, $sourceBaseUrl, $ourBaseUrl)
    {
        // Replace source domain outside of href/src attributes
        $content = preg_replace(
            '/(?<!href=["|\'])(?<!src=["|\'])(?<!href=)(?<!src=)(' . preg_quote($sourceBaseUrl, '/') . ')/i',
            $ourBaseUrl,
            $content
        );

        // Replace additional domains (case insensitive)
        foreach (self::ADDITIONAL_DOMAINS as $domain) {
            $content = preg_replace(
                '/(?<!href=["|\'])(?<!src=["|\'])(?<!href=)(?<!src=)(' . preg_quote($domain, '/') . ')/i',
                $ourBaseUrl,
                $content
            );
        }
        
        // Replace case-sensitive domains (exact match)
        foreach (self::CASE_SENSITIVE_DOMAINS as $domain) {
            // Use a more specific pattern for case-sensitive matching
            $content = preg_replace(
                '/(?<!href=["|\'])(?<!src=["|\'])(?<!href=)(?<!src=)(' . preg_quote($domain, '/') . ')/',  // No 'i' flag
                $ourBaseUrl,
                $content
            );
        }

        return $content;
    }

    /**
     * Fix problematic URL patterns
     */
    private function fixProblematicUrls($content)
    {
        $replacements = [
            'https://img.https://thuvienloigiai.com' => 'https://img.loigiaihay.com',
            'https://img.thuvienloigiai.com' => 'https://img.loigiaihay.com',
            'https://https://thuvienloigiai.com' => 'https://toanmath.com',
            // Add an explicit replacement for the complex pattern
            'img.https://thuvienloigiai.com' => 'img.loigiaihay.com',
            // Add case-sensitive TOANMATH.com replacement
            'TOANMATH.com' => parse_url(config('app.url'), PHP_URL_HOST),
        ];

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        // Fix image sources with double protocols
        $content = $this->fixImageSources($content);

        // General fix for any double protocol occurrences
        $content = preg_replace('/https?:\/\/https?:\/\//', 'https://', $content);
        
        // Final pass for any remaining broken patterns
        // Handle the specific pattern found in image tags
        $content = preg_replace('/(src=["\'])https?:\/\/img\.https?:\/\/thuvienloigiai\.com/i', '$1https://img.loigiaihay.com', $content);
        $content = preg_replace('/(src=["\'])img\.https?:\/\/thuvienloigiai\.com/i', '$1img.loigiaihay.com', $content);

        return $content;
    }

    /**
     * Fix problematic image src attributes
     */
    private function fixImageSources($content)
    {
        // Skip if content is empty or doesn't contain problematic URLs
        if (empty($content) || 
            (strpos($content, 'thuvienloigiai.com') === false && 
             strpos($content, 'img.https://') === false &&
             strpos($content, 'TOANMATH.com') === false)
             ) 
        {
            return $content;
        }

        // Create a DOM object
        $dom = new \DOMDocument();
        
        // Suppress errors for malformed HTML
        $internalErrors = libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);
        
        // Find all img tags
        $images = $dom->getElementsByTagName('img');
        $changed = false;
        
        foreach ($images as $img) {
            if ($img->hasAttribute('src')) {
                $src = $img->getAttribute('src');
                $originalSrc = $src;
                
                // Fix various problematic URL patterns
                
                // Case 1: Double protocol at the beginning
                if (preg_match('/^https?:\/\/https?:\/\//', $src)) {
                    $src = preg_replace('/^https?:\/\/https?:\/\//', 'https://', $src);
                }
                
                // Case 2: Protocol followed by img. followed by protocol
                // Pattern like: https://img.https://thuvienloigiai.com/...
                if (preg_match('/^https?:\/\/img\.https?:\/\//', $src)) {
                    $src = preg_replace('/^https?:\/\/img\.https?:\/\//', 'https://img.', $src);
                }
                
                // Case 3: img. followed by protocol
                // Pattern like: img.https://thuvienloigiai.com/...
                if (preg_match('/^img\.https?:\/\//', $src)) {
                    $src = preg_replace('/^img\.https?:\/\//', 'img.', $src);
                }
                
                // Special case for thuvienloigiai.com - perform this replacement after fixing protocols
                if (strpos($src, 'thuvienloigiai.com') !== false) {
                    $src = str_replace('thuvienloigiai.com', 'loigiaihay.com', $src);
                }
                
                // Case-sensitive replacement for TOANMATH.com
                if (strpos($src, 'TOANMATH.com') !== false) {
                    $src = str_replace('TOANMATH.com', parse_url(config('app.url'), PHP_URL_HOST), $src);
                }
                
                // Ensure URLs are properly formatted
                if (!empty($src) && !preg_match('/^https?:\/\//', $src) && strpos($src, 'img.') === 0) {
                    $src = 'https://' . $src;
                }
                
                // Update attribute if changed
                if ($src !== $originalSrc) {
                    $img->setAttribute('src', $src);
                    $changed = true;
                    
                    // Log the change for debugging
                    Log::debug('Fixed image URL', [
                        'original' => $originalSrc,
                        'fixed' => $src
                    ]);
                }
            }
        }
        
        // Only re-serialize if we made changes
        if ($changed) {
            // Get the HTML content back
            $content = $dom->saveHTML();
        }
        
        // Additional regex replacement for any URLs not caught by DOM processing
        $content = preg_replace('/(https?:\/\/img\.https?:\/\/thuvienloigiai\.com)/i', 'https://img.loigiaihay.com', $content);
        $content = preg_replace('/(img\.https?:\/\/thuvienloigiai\.com)/i', 'img.loigiaihay.com', $content);
        
        // Case-sensitive replacement for TOANMATH.com
        $ourBaseUrl = parse_url(config('app.url'), PHP_URL_HOST);
        $content = str_replace('https://THCS.TOANMATH.com', $ourBaseUrl, $content);
        $content = str_replace('TOANMATH.com', $ourBaseUrl, $content);
        
        return $content;
    }

    /**
     * Remove unwanted HTML elements
     */
    public function removeUnwantedElements($content)
    {
        $unwantedPatterns = [
            '/<div class="fb-comments[^>]*>.*?<\/div>/is',
            '/<div class="fb-like[^>]*>.*?<\/div>/is',
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/is',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/is', // Also remove iframes
            '/<ins\b[^<]*(?:(?!<\/ins>)<[^<]*)*<\/ins>/is' // Remove ad inserts
        ];

        foreach ($unwantedPatterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }

        return $content;
    }

    /**
     * Display a post
     */
    public function show($post_slug)
    {
        $post = Post::with(['chapter.book.group.category'])
            ->where('slug', $post_slug)
            ->firstOrFail();

        $postContent = $this->getPostContent($post);
        if (!$postContent) {
            abort(404);
        }

        // Always apply URL fixes before displaying content
        // This ensures even cached content with broken URLs gets fixed
        $postContent = $this->fixProblematicUrls($postContent);
        
        // Final catch-all replacement for case-sensitive domains
        $ourBaseUrl = parse_url(config('app.url'), PHP_URL_HOST);
        foreach (self::CASE_SENSITIVE_DOMAINS as $domain) {
            $postContent = str_replace($domain, $ourBaseUrl, $postContent);
        }

        // Get related posts for the footer
        $footerLatestPosts = $this->getRelatedPosts($post);

        return view('posts.show', [
            'post' => $post,
            'content' => $postContent,
            'category' => $post->chapter->book->group->category,
            'footerLatestPosts' => $footerLatestPosts
        ]);
    }

    /**
     * Clear the cache for a post - admin only
     */
    public function clearCache($post_id)
    {
        $post = Post::findOrFail($post_id);
        
        // Clear content cache
        Cache::forget('post_content_' . $post->id);
        
        // Clear related posts cache
        Cache::forget('related_posts_' . $post->id);
        
        // Optionally reprocess content
        if ($post->source_url && request('reprocess', false)) {
            // Clear stored content to force reprocessing
            $post->content = null;
            $post->save();
            
            // Get content again to trigger reprocessing
            $this->getPostContent($post);
        }
        
        return back()->with('success', 'Post cache cleared successfully');
    }

    /**
     * Get related posts for the current post
     */
    private function getRelatedPosts(Post $post)
    {
        return Cache::remember('related_posts_' . $post->id, self::CACHE_DURATION, function () use ($post) {
            return Post::select('posts.*')
                ->join('book_chapters', 'posts.book_chapter_id', '=', 'book_chapters.id')
                ->join('books', 'book_chapters.book_id', '=', 'books.id')
                ->where('books.book_group_id', $post->chapter->book->group->id)
                ->where('posts.id', '!=', $post->id)  // Exclude current post
                ->latest()
                ->limit(10)
                ->get();
        });
    }

    /**
     * Remove links from external domains
     */
    private function removeExternalLinks($content, $sourceBaseUrl)
    {
        // Remove links from source domain
        $pattern = '/<a[^>]*href=["\']([^"\']*' . preg_quote($sourceBaseUrl, '/') . '[^"\']*)["\'][^>]*>(.*?)<\/a>/i';
        $content = preg_replace_callback($pattern, function($matches) {
            return $matches[2]; // Return just the content inside the <a> tag
        }, $content);

        // Remove links from additional domains (case insensitive)
        foreach (self::ADDITIONAL_DOMAINS as $domain) {
            $pattern = '/<a[^>]*href=["\']([^"\']*' . preg_quote($domain, '/') . '[^"\']*)["\'][^>]*>(.*?)<\/a>/i';
            $content = preg_replace_callback($pattern, function($matches) {
                return $matches[2]; // Return just the content inside the <a> tag
            }, $content);
        }
        
        // Remove links from case-sensitive domains (exact match)
        foreach (self::CASE_SENSITIVE_DOMAINS as $domain) {
            // No 'i' flag for case-sensitive matching
            $pattern = '/<a[^>]*href=["\']([^"\']*' . preg_quote($domain, '/') . '[^"\']*)["\'][^>]*>(.*?)<\/a>/';
            $content = preg_replace_callback($pattern, function($matches) {
                return $matches[2]; // Return just the content inside the <a> tag
            }, $content);
        }

        return $content;
    }
}
