<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach ($types as $type)
        @for ($i = 1; $i <= $type->page_count; $i++)
            <sitemap>
                <loc>{{ url("sitemap-{$type->type}-{$i}.xml") }}</loc>
                <lastmod>{{ \Carbon\Carbon::parse($type->lastmod)->toIso8601String() }}</lastmod>
            </sitemap>
        @endfor
    @endforeach
</sitemapindex> 