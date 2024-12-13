<div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-bold text-orange-500 mb-6">
        Những dạng bài quan tâm
        <span class="block h-1 w-48 bg-blue-500 mt-2"></span>
    </h2>

    <div id="articles-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @include('layouts.articles-grid', ['articles' => $articles])
    </div>

    @if($hasMore)
        <div class="text-center mt-8">
            <button
                class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition"
                data-article-load-more
                data-page="1"
            >
                Xem thêm
            </button>
        </div>
    @endif
</div>
