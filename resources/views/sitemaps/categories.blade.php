<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@php
    // Process items in smaller batches to reduce memory usage
    $chunks = $items->chunk(250);
@endphp

@foreach ($chunks as $chunk)
    @foreach ($chunk as $category)
        <url>
            <loc>{{ route('categories.show', $category->slug) }}</loc>
            <lastmod>{{ $category->updated_at->toIso8601String() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
    @php
        // Clear previous chunk from memory
        unset($chunk);
    @endphp
@endforeach
</urlset> 