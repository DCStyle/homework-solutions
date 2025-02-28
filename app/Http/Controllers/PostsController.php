<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class PostsController extends Controller
{
    private function getPostContent(Post $post)
    {
        if ($post->content == null && $post->source_url == null)
        {
            return null;
        }

        if ($post->content !== null)
        {
            return $post->content;
        }

        // Base64 encode the full URL
        $encodedUrl = base64_encode($post->source_url);
        $proxyUrl = 'https://ketqua5s.com/?url=' . $encodedUrl;

        Log::info('Making proxy request', [
            'original_url' => $post->source_url,
            'encoded_url' => $encodedUrl,
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

            $crawler = new Crawler($response->body());
            
            // Get content from ".detail_new #box-content"
            // if empty, then get content from ".box_content .detail_new"
            try {
                $content = $crawler->filter('.detail_new #box-content')->html();
            } catch (\Exception $e) {
                $content = $crawler->filter('.box_content .detail_new')->html();
            }

            // Remove all script tags
            $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $content);

            // Get the base url of source url
            $sourceBaseUrl = parse_url($post->source_url, PHP_URL_HOST);

            // Get our base url
            $ourBaseUrl = parse_url(config('app.url'), PHP_URL_HOST);

            // Replace links in the content
            $content = $this->replaceLinks($content, $sourceBaseUrl, $ourBaseUrl);

            // Remove unwanted elements
            $content = $this->removeUnwantedElements($content);

            // Save the modified content to the post
            $post->content = $content;
            $post->save();

            return $content;
        } catch (\Exception $e) {
            Log::error('Proxy request failed', [
                'url' => $post->source_url,
                'proxy_url' => $proxyUrl,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function replaceLinks($content, $sourceBaseUrl, $ourBaseUrl)
    {
        // Replace the source base url with our base url
        // but only for text, not for images or links

        // Manually replace these URLs also: loigiaihay.com, toanmath.com, thcs.toanmath.com with our base url
        // Define the additional domains to replace
        $additionalDomains = [
            'loigiaihay.com',
            'toanmath.com',
            'thcs.toanmath.com'
        ];
        
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
            
            // Replace the source base URL with our base URL in text
            $newValue = str_replace($sourceBaseUrl, $ourBaseUrl, $textNode->nodeValue);
            
            // Also replace the additional domains
            foreach ($additionalDomains as $domain) {
                $newValue = str_replace($domain, $ourBaseUrl, $newValue);
            }
            
            $textNode->nodeValue = $newValue;
        }

        // Get the modified content
        $content = $dom->saveHTML();

        // Additional cleanup: ensure any remaining references to source domain in text are handled
        // This regex tries to find and replace domain references outside of href and src attributes
        $content = preg_replace(
            '/(?<!href=["|\'])(?<!src=["|\'])(?<!href=)(?<!src=)(' . preg_quote($sourceBaseUrl, '/') . ')/i',
            $ourBaseUrl,
            $content
        );
        
        // Also apply the regex replacements for additional domains
        foreach ($additionalDomains as $domain) {
            $content = preg_replace(
                '/(?<!href=["|\'])(?<!src=["|\'])(?<!href=)(?<!src=)(' . preg_quote($domain, '/') . ')/i',
                $ourBaseUrl,
                $content
            );
        }

        // Remove all links from source URL - using regex approach instead of DOM
        // This pattern matches <a> tags that contain the source domain in their href
        $pattern = '/<a[^>]*href=["\']([^"\']*' . preg_quote($sourceBaseUrl, '/') . '[^"\']*)["\'][^>]*>(.*?)<\/a>/i';

        // Replace the matched <a> tags with just their content (without the surrounding <a></a>)
        $content = preg_replace_callback($pattern, function($matches) {
            // $matches[0] is the entire match
            // $matches[1] is the href value
            // $matches[2] is the content inside the <a> tag
            return $matches[2]; // Return just the content inside the <a> tag
        }, $content);
        
        // Also remove links from additional domains
        foreach ($additionalDomains as $domain) {
            $pattern = '/<a[^>]*href=["\']([^"\']*' . preg_quote($domain, '/') . '[^"\']*)["\'][^>]*>(.*?)<\/a>/i';
            $content = preg_replace_callback($pattern, function($matches) {
                return $matches[2]; // Return just the content inside the <a> tag
            }, $content);
        }

        return $content;
    }

    public function removeUnwantedElements($content)
    {
        // Remove facebook comments
        $content = preg_replace('/<div class="fb-comments[^>]*>.*?<\/div>/i', '', $content);

        // Remove facebook like button
        $content = preg_replace('/<div class="fb-like[^>]*>.*?<\/div>/i', '', $content);
        
        // Remove all script tags
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $content);

        return $content;
    }

    public function show($post_slug)
    {
        $post = Post::with(['chapter.book.group.category'])
            ->where('slug', $post_slug)
            ->firstOrFail();

        $postContent = $this->getPostContent($post);
        if (!$postContent)
        {
            abort(404);
        }

        if ($post->source_url) {
            // Try replace links in the content
            $postContent = $this->replaceLinks($postContent, $post->source_url, config('app.url'));

            // Remove unwanted elements
            $postContent = $this->removeUnwantedElements($postContent);

            // Save the modified content to the post
            $post->content = $postContent;
            $post->save();
        }

        $footerLatestPosts = Post::select('posts.*')
            ->join('book_chapters', 'posts.book_chapter_id', '=', 'book_chapters.id')
            ->join('books', 'book_chapters.book_id', '=', 'books.id')
            ->where('books.book_group_id', $post->chapter->book->group->id)
            ->where('posts.id', '!=', $post->id)  // Exclude current post
            ->latest()
            ->limit(10)
            ->get();

        return view('posts.show', [
            'post' => $post,
            'content' => $postContent,
            'category' => $post->chapter->book->group->category,
            'footerLatestPosts' => $footerLatestPosts
        ]);
    }
}
