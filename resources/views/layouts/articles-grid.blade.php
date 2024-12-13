@foreach($articles as $article)
    <div class="article-card bg-white rounded-lg overflow-hidden shadow hover:shadow-lg transition">
        <a href="{{ route('articles.show', $article->slug) }}" class="block">
            <div class="aspect-square overflow-hidden">
                <img src="{{ $article->getThumbnail() }}"
                     alt="{{ $article->title }}"
                     class="w-full h-full object-cover transform hover:scale-105 transition">
            </div>

            <div class="p-4">
                <h4 class="text-sm text-gray-500">
                    {{ $article->category->name }}
                </h4>

                <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    {{ $article->title }}
                </h3>

                <p class="text-gray-600 text-sm line-clamp-2">
                    {{ $article->excerpt }}
                </p>
            </div>
        </a>
    </div>
@endforeach
