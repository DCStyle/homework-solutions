@extends('layouts.app')

@seo(['title' => $article->title . ' | ' . setting('site_name', 'Homework Solutions')])
@seo(['description' => $article->getContentSnippet()])

@section('content')
    <div class="flex justify-between">
        <div class="sidebar-left-content w-[320px] h-auto flex-shrink-0 flex-grow-0 max-xl:w-[280px] max-md:hidden">
            <div class="sticky top-10 bg-white border shadow-md">
                <div class="p-4 text-md border-b border-b-gray-300 mb-4">
                    <h2 class="text-xl font-bold">Chuyên mục</h2>
                    <ul class="list-none mt-4 border-t border-gray-400">
                        @foreach ($articleCategories as $item)
                            <li class="py-2 mb-2 flex items-center space-x-4 border-b border-gray-400">
                                <span class="iconify text-2xl text-orange-400" data-icon="mdi-menu-right"></span>

                                <a title="{{ $item->name }}" href="{{ route('article-categories.show', $item->slug) }}"
                                   class="text-gray-800 hover:text-orange-400">
                                    {{ $item->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="p-4 text-md border-b border-b-gray-300">
                    <h2 class="text-xl font-bold">Giải bài tập tất cả các lớp học</h2>
                    <ul class="list-none mt-4 border-t border-gray-400">
                        @foreach ($categories as $item)
                            <li class="py-2 mb-2 flex items-center space-x-4 border-b border-gray-400">
                                <span class="iconify text-2xl text-orange-400" data-icon="mdi-menu-right"></span>

                                <a title="{{ $item->name }}" href="{{ route('categories.show', $item->slug) }}"
                                   class="text-gray-800 hover:text-orange-400">
                                    {{ $item->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="mx-auto p-6 min-w-0 w-full max-xl:p-4 max-md:p2">
            <h1 class="text-2xl font-bold text-orange-400 py-2 border-b-2 border-b-blue-800">
                <a title="{{ $article->title }}" href="{{ route('articles.show', $article->slug) }}">
                    {{ $article->title }}
                </a>
            </h1>

            <div class="mt-4 text-lg">
                {!! $article->content !!}
            </div>

            <div class="mt-4">
                <div class="flex flex-wrap whitespace-nowrap items center space-x-1">
                    @foreach($article->tags as $tag)
                        <span class="bg-gray-100 text-gray-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded">
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        @include('layouts.sidebar-right')
    </div>

    <script>
        MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']]
            },
            svg: {
                fontCache: 'global'
            }
        };
    </script>
    <script
        type="text/javascript"
        id="MathJax-script"
        async
        src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js">
    </script>
@endsection
