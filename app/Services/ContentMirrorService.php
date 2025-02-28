<?php

namespace App\Services;

use App\Exceptions\TooManyRequestsException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\DomCrawler\Crawler;

class ContentMirrorService
{
    private $urlMappings;
    private $sourceDomain;

    private $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0'
    ];

    public function __construct()
    {
        $config = config('url_mappings');
        $this->urlMappings = collect($config['paths']);
        $this->sourceDomain = $config['source_domain'];
    }

    public function makeRequest(string $path, array $params, string $template, string $selector, string $sourceUrl, string $method = 'POST'): ?array
    {
        if (!$this->checkRateLimit()) {
            throw new TooManyRequestsException();
        }

        $cacheKey = $this->generateCacheKey($path, $params);

        // For debugging
        \Log::info('Making request', [
            'path' => $path,
            'sourceUrl' => $sourceUrl,
            'params' => $params,
            'method' => $method
        ]);

        if (!(bool)setting('cache_enabled')) {
            return $this->fetchAndProcessContent($path, $params, $template, $selector, $sourceUrl, $method);
        }

        return Cache::remember($cacheKey, setting('cache_lifetime', 5) * 60, function () use ($path, $params, $template, $selector, $sourceUrl, $method) {
            return $this->fetchAndProcessContent($path, $params, $template, $selector, $sourceUrl, $method);
        });
    }

    private function fetchAndProcessContent(string $path, array $params, string $template, string $selector, string $sourceUrl, string $method): ?array
    {
        try {
            // Construct the full source URL
            $fullSourceUrl = rtrim($sourceUrl, '/') . '/' . ltrim($path, '/');

            \Log::info('Fetching content', [
                'fullSourceUrl' => $fullSourceUrl,
                'params' => $params
            ]);

            $response = $this->sendRequest($sourceUrl . '/' . $path, $params, $method);

            if (!$response->successful()) {
                \Log::error('Failed to fetch content', [
                    'status' => $response->status(),
                    'url' => $fullSourceUrl,
                    'response' => $response->body()
                ]);
                throw new \Exception("Failed to fetch content: {$response->status()}");
            }

            $crawler = new Crawler($response->body());
            $metadata = $this->extractMetadata($crawler);
            $content = $this->extractContent($response->body(), $selector);

            return [
                'content' => $this->processContent($content),
                'template' => $template,
                'metadata' => $metadata
            ];
        } catch (\Exception $e) {
            Log::error("Request failed for {$path}: " . $e->getMessage());
            return null;
        }
    }

    private function extractMetadata(Crawler $crawler): array
    {
        $metadata = [
            'title' => setting('site_name'),
            'description' => setting('site_description'),
            'keywords' => setting('site_keywords'),
            'robots' => 'noindex,nofollow',
            'og_tags' => [
                'og:title' => setting('site_name'),
                'og:description' => setting('site_description'),
                'og:site_name' => setting('site_name'),
                'og:locale' => 'vi_VN'
            ],
            'canonical' => url()->current()
        ];

        // Extract title
        $titleNode = $crawler->filter('title');
        if ($titleNode->count() > 0) {
            $metadata['title'] = trim($titleNode->text());
        }

        // Extract meta tags
        $crawler->filter('meta')->each(function (Crawler $node) use (&$metadata) {
            $name = $node->attr('name') ?? $node->attr('property');
            $content = $node->attr('content');

            if (!$name || !$content) return;

            switch (strtolower($name)) {
                case 'description':
                    $metadata['description'] = $content;
                    break;
                case 'keywords':
                    $metadata['keywords'] = $content;
                    break;
                case 'robots':
                    $metadata['robots'] = $content;
                    break;
                default:
                    if (str_starts_with($name, 'og:')) {
                        // Do not take these tags
                        if (!in_array($name, ['og:image', 'og:url', 'og:image:url'])) {
                            $metadata['og_tags'][$name] = $content;
                        }
                    } elseif (str_starts_with($name, 'twitter:')) {
                        // Do not take these tags
                        if (!in_array($name, ['twitter:image'])) {
                            $metadata['twitter_tags'][$name] = $content;
                        }
                    }
            }
        });

        // Extract canonical URL
        $canonical = $crawler->filter('link[rel="canonical"]');
        if ($canonical->count() > 0) {
            // Replace source domain with our domain
            $canonical->each(function (Crawler $node) {
                $node->getNode(0)->setAttribute('href', str_replace($this->sourceDomain, setting('site_url'), $node->attr('href')));
            });
        }

        return $metadata;
    }

    public function getContent(string $path): ?array
    {
        $customMapping = $this->urlMappings->first(function ($mapping) use ($path) {
            return isset($mapping['our_paths'][$path]);
        });

        if ($customMapping) {
            return [
                'content' => null,
                'template' => $customMapping['our_paths'][$path]
            ];
        }

        $defaultConfig = config('url_mappings.default_scrape');
        return $this->makeRequest(
            $path,
            request()->all(),
            $defaultConfig['template'],
            $defaultConfig['main_selector'],
            $defaultConfig['source_url'],
            request()->method()
        );
    }

    private function checkRateLimit(): bool
    {
        return RateLimiter::attempt('scraping', 60, function() { return true; });
    }

    private function generateCacheKey(string $path, array $params): string
    {
        return 'page_' . md5($path . serialize($params));
    }

    private function sendRequest(string $url, array $params, string $method): \Illuminate\Http\Client\Response
    {
        // Ensure URL has scheme
        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            $url = 'https://' . $url;
        }

        // Base64 encode the full URL
        $encodedUrl = base64_encode($url);
        $proxyUrl = 'https://yopovn.com/proxy/?url=' . $encodedUrl;

        Log::info('Making proxy request', [
            'original_url' => $url,
            'encoded_url' => $encodedUrl,
            'proxy_url' => $proxyUrl
        ]);

        try {
            return Http::timeout(30)
                ->withOptions([
                    'verify' => false,
                    'connect_timeout' => 30,
                    'timeout' => 30
                ])
                ->withHeaders([
                    'User-Agent' => $this->getRandomUserAgent(),
                    'Accept' => '*/*',
                    'Accept-Language' => 'en-US,en;q=0.9,vi;q=0.8',
                    'Cache-Control' => 'no-cache'
                ])
                ->get($proxyUrl);
        } catch (\Exception $e) {
            Log::error('Proxy request failed', [
                'url' => $url,
                'proxy_url' => $proxyUrl,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function extractContent(string $html, string $selector): string
    {
        $crawler = new Crawler($html);
        return $crawler->filter($selector)->html();
    }

    private function removeUnwantedContent(Crawler $crawler): void
    {
        // Remove any script
        $crawler->filter('script')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove the element .adsbygoogle
        $crawler->filter('.adsbygoogle')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove the element div.top-title
        $crawler->filter('div.top-title')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove the element `<h4 style="color:#DF7101; font-size: 18px; text-align: center;text-transform: uppercase;font-weight: 700">Bài viết mới nhất</h4>`
        $crawler->filter('h4[style="color:#DF7101; font-size: 18px; text-align: center;text-transform: uppercase;font-weight: 700"]')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove the element .wiki-latest-article
        $crawler->filter('.wiki-latest-article')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove the element #fb_like_fb_new
        $crawler->filter('#fb_like_fb_new')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove the element .kk-star-ratings
        $crawler->filter('.kk-star-ratings')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove element #readmore-bottom-article
        $crawler->filter('#readmore-bottom-article')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove element .block_gopy
        $crawler->filter('.block_gopy')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove element #lgh-ts247-btad
        $crawler->filter('#lgh-ts247-btad')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Remove element #top_facebook_comment
        $crawler->filter('#top_facebook_comment')->each(function (Crawler $node) {
            $node->getNode(0)->parentNode->removeChild($node->getNode(0));
        });

        // Find the element .list.bottom10.clearfix then add class .space-y-4
        $crawler->filter('.list.bottom10.clearfix')->each(function (Crawler $node) {
            $node->getNode(0)->setAttribute('class', 'list bottom10 clearfix space-y-4');
        });
    }

    private function fixUrls(string $content): string
    {
        $ourDomain = rtrim(env('APP_URL'), '/');
        $ourBaseDomain = parse_url($ourDomain, PHP_URL_HOST);
        $sourceBaseDomain = config('url_mappings.source_domain');

        // Create a crawler with the content
        $crawler = new Crawler($content);

        // Fix relative image URLs to point to source domain
        $crawler->filter('img')->each(function (Crawler $node) use ($sourceBaseDomain, $ourBaseDomain) {
            $src = $node->attr('src');

            if ($src) {
                // Case 1: If URL is relative (doesn't start with http/https or //)
                if (!preg_match('~^(?:f|ht)tps?://~i', $src) && !str_starts_with($src, '//')) {
                    // Remove leading slash if present
                    $src = ltrim($src, '/');

                    // Add source domain to the image URL
                    $newSrc = 'https://' . $sourceBaseDomain . '/' . $src;
                    $node->getNode(0)->setAttribute('src', $newSrc);
                }
                // Case 2: If URL contains our domain, replace with source domain
                else if (str_contains($src, $ourBaseDomain)) {
                    $newSrc = str_replace(
                        ['https://' . $ourBaseDomain, 'http://' . $ourBaseDomain],
                        'https://' . $sourceBaseDomain,
                        $src
                    );
                    $node->getNode(0)->setAttribute('src', $newSrc);
                }
            }
        });

        // Fix links to point to our domain (not changing this part)
        $crawler->filter('a')->each(function (Crawler $node) use ($ourBaseDomain, $sourceBaseDomain) {
            $href = $node->attr('href');

            if ($href) {
                // Replace source domain with our domain in href attributes
                if (str_contains($href, $sourceBaseDomain)) {
                    $newHref = str_replace(
                        ['https://' . $sourceBaseDomain, 'http://' . $sourceBaseDomain],
                        'https://' . $ourBaseDomain,
                        $href
                    );
                    $node->getNode(0)->setAttribute('href', $newHref);
                }
            }
        });

        // Replace domain names in text content (excluding image URLs)
        $html = $crawler->html();

        return $html;
    }

    private function processContent($content): string
    {
        $crawler = new Crawler($content);

        $this->removeUnwantedContent($crawler);

        $content = $crawler->html();
        $content = $this->fixUrls($content);

        return $content;
    }

    private function getRandomUserAgent()
    {
        return $this->userAgents[array_rand($this->userAgents)];
    }

    private function getCustomContent($mapping)
    {
        return view($mapping['template'])->render();
    }

    private function getScrapedContent($mapping, $path)
    {
        $cacheEnabled = setting('cache_enabled');

        if ($cacheEnabled) {
            return Cache::remember('page_' . md5($path), setting('cache_lifetime'), function() use ($mapping, $path) {
                return $this->scrapeContent($mapping, $path);
            });
        } else {
            return $this->scrapeContent($mapping, $path);
        }
    }

    private function scrapeContent($mapping, $path)
    {
        if (!RateLimiter::attempt('scraping', 60, function() { })) {
            throw new TooManyRequestsException();
        }

        try {
            $sourceUrl = $mapping['source_url'] . $path;
            $response = Http::withHeaders([
                'User-Agent' => $this->getRandomUserAgent()
            ])->get($sourceUrl);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch content: {$response->status()}");
            }

            $crawler = new Crawler($response->body());
            $content = $crawler->filter($mapping['main_selector'])->html();
            return $this->processContent($content);
        } catch (\Exception $e) {
            Log::error("Scraping failed for {$path}: " . $e->getMessage());
            return Cache::get('page_' . md5($path));
        }
    }
}
