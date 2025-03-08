<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($entries as $entry)
        <url>
            <loc>{{ $entry->loc }}</loc>
            <lastmod>{{ \Carbon\Carbon::parse($entry->lastmod)->toIso8601String() }}</lastmod>
            <changefreq>{{ $entry->changefreq }}</changefreq>
            <priority>{{ $entry->priority }}</priority>
        </url>
    @endforeach
</urlset> 