@extends('layouts.app')

@seo(['title' => $category->name])
@seo(['description' => $category->description])

@section('content')
    <div class="flex justify-between">
        <div class="sidebar-left-content w-[320px] h-auto flex-shrink-0 flex-grow-0 max-xl:w-[280px] max-md:hidden">
            <div class="sticky top-10 bg-white border shadow-md">
                <div class="p-4 text-md border-b border-b-gray-300 mb-4">
                    <h2 class="text-xl font-bold">Bài giải mới nhất</h2>
                    <ul class="list-none mt-4 border-t border-gray-400">
                        @foreach ($posts as $item)
                            <li class="py-2 mb-2 flex items-center space-x-4 border-b border-gray-400">
                                <span class="iconify text-2xl text-orange-400" data-icon="mdi-menu-right"></span>

                                <a title="{{ $item->title }}" href="{{ route('posts.show', $item->slug) }}"
                                   class="text-gray-800 hover:text-orange-400">
                                    {{ $item->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="mx-auto p-6 min-w-0 w-full max-xl:p-4 max-md:p2">
            <div class="py-2 border-b-2 border-b-blue-800">
                <h1 class="text-2xl font-bold text-orange-400">
                    <a title="{{ $category->name }}" href="{{ route('article-categories.show', $category->slug) }}">
                        {{ $category->name }}
                    </a>
                </h1>

                @if($category->description)
                    <div class="text-lg text-gray-600">
                        {!! $category->description !!}
                    </div>
                @endif
            </div>

            <div class="mt-4 text-lg">
                {!! $category->content !!}
            </div>

            <!-- Articles Grid -->
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @forelse($articles as $article)
                    <article class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">
                        <a href="{{ route('articles.show', $article->slug) }}">
                            <!-- Article Thumbnail -->
                            <div class="aspect-w-16 aspect-h-9">
                                <img src="{{ $article->getThumbnail() }}"
                                     alt="{{ $article->title }}"
                                     class="object-cover w-full h-48">
                            </div>

                            <div class="p-6">
                                <!-- Article Title -->
                                <h2 class="text-xl font-semibold text-gray-900 mb-2 hover:text-indigo-600">
                                    {{ $article->title }}
                                </h2>

                                <!-- Article Excerpt -->
                                <p class="text-gray-600 mb-4 line-clamp-2">
                                    {{ $article->getContentSnippet() }}
                                </p>

                                <!-- Article Meta -->
                                <div class="flex items-center justify-between mt-4">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($article->tags as $tag)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $tag->name }}
                                        </span>
                                        @endforeach
                                    </div>

                                    <time datetime="{{ $article->created_at->format('Y-m-d') }}"
                                          class="text-sm text-gray-500">
                                        {{ $article->created_at->format('d/m/Y') }}
                                    </time>
                                </div>
                            </div>
                        </a>
                    </article>
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500 text-lg">
                            Chưa có bài viết nào trong danh mục này
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $articles->links() }}
            </div>

            <!-- Category Stats -->
            <div class="mt-12 text-center text-gray-600">
                <p>Hiển thị {{ $articles->count() }} / {{ $articles->total() }} bài viết</p>
            </div>
        </div>

        @include('layouts.sidebar-right')
    </div>
@endsection

@push('styles')
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
@endpush
