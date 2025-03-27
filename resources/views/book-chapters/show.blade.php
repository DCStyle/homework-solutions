@extends('layouts.app')

@section('seo')
    {!! seo($chapter->getDynamicSEOData()) !!}
@endsection

@section('content')
    <div class="flex justify-between">
        <div class="sidebar-left-content w-[320px] h-auto flex-shrink-0 flex-grow-0 max-xl:w-[280px] max-md:hidden">
            <div class="sticky top-10 bg-white border shadow-md">
                <div class="p-4 text-md border-b border-b-gray-300">
                    <h2 class="text-xl text-orange-400 font-bold">
                        {{ $chapter->book->name }}
                    </h2>
                </div>

                <div class="p-4 text-md border-b border-b-gray-300">
                    <h2 class="text-xl font-bold">Cùng chuyên mục</h2>
                    <ul class="list-disc list-inside mt-4 mx-0 px-0">
                        @foreach ($chapter->book->chapters as $item)
                            <li class="mb-2">
                                <a title="{{ $item->name }}" href="{{ route('bookChapters.show', $item->slug) }}"
                                   class="text-gray-800 hover:text-orange-400 {{ $item->id == $chapter->id ? 'text-orange-400' : '' }}">
                                    {{ $item->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="p-4 text-md">
                    <h2 class="text-xl font-bold">Mục lục quan tâm</h2>
                    <ul class="list-disc list-inside mt-4 mx-0 px-0">
                        @foreach ($chapter->book->group->books as $book)
                            <li class="mb-2">
                                <a title="{{ $book->name }}" href="{{ route('books.show', $book->slug) }}"
                                   class="text-gray-800 hover:text-orange-400">
                                    {{ $book->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="mx-auto p-6 min-w-0 w-full max-xl:p-4 max-md:p2">
            <h1 class="text-2xl font-bold text-orange-400 py-2 border-b-2 border-b-blue-800">
                {{ $chapter->name . ' - ' . $book->name }}
            </h1>

            <nav aria-label="breadcrumb" class="my-4">
                <ol class="flex flex-wrap items-center text-sm md:text-base px-0">
                    <li class="flex items-center">
                        <a title="{{ setting('site_name', 'Home') }}" href="{{ route('home') }}" class="text-gray-600 hover:text-blue-500">
                            {{ setting('site_name', 'Home') }}
                        </a>
                        <span class="mx-2 text-gray-500">/</span>
                    </li>
                    <li class="flex items-center">
                        <a title="{{ $category->name }}" href="{{ route('categories.show', $category->slug) }}" class="text-gray-600 hover:text-blue-500">
                            {{ $category->name }}
                        </a>
                        <span class="mx-2 text-gray-500">/</span>
                    </li>
                    <li class="flex items-center">
                        <a title="{{ $group->name }}" href="{{ route('bookGroups.show', $group->slug) }}" class="text-gray-600 hover:text-blue-500">
                            {{ $group->name }}
                        </a>
                        <span class="mx-2 text-gray-500">/</span>
                    </li>
                    <li class="flex items-center">
                        <a title="{{ $book->name }}" href="{{ route('books.show', $book->slug) }}" class="text-gray-600 hover:text-blue-500">
                            {{ $book->name }}
                        </a>
                        <span class="mx-2 text-gray-500">/</span>
                    </li>
                    <li class="flex items-center">
                        <a title="{{ $chapter->name }}" href="{{ route('bookChapters.show', $chapter->slug) }}" class="font-bold text-orange-400 hover:text-orange-500">
                            {{ $chapter->name }}
                        </a>
                    </li>
                </ol>
            </nav>

            <div class="mt-4 bg-white p-4 text-md text-green-700 border shadow-md">
                @if($chapter->description && strlen(trim($chapter->description)) > 0)
                    <div id="post-content" class="tiny-mce-content">{!! $chapter->description !!}</div>
                @else
                    <h2 class="text-xl">{!! "Dưới đây là toàn bộ bài giải <b class='text-black'>$chapter->name - $book->name - $group->name</b>.
                           Cách hướng dẫn, trình bày lời giải chi tiết, dễ hiểu.
                           Học sinh muốn xem bài nào thì click vào tên bài để xem.
                           Chúc các em học tốt và nắm vững kiến thức <b>$chapter->name</b> trên <b class='underline'><a href='" . url('/') . "'>" . setting('site_name', 'Homework Solutions') . "</a></b>."
                    !!}</h2>
                @endif
            </div>

            <div class="mt-8">
                <h2 class="text-center text-3xl font-medium underline mb-4">
                    {{ $chapter->name . ' - ' . $group->name  }}
                </h2>

                <div class="p-2 border">
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($chapter->posts as $post)
                            <li class="flex items-center gap-2">
                                <span class="iconify text-xl" data-icon="mdi-chevron-right"></span>

                                <a href="{{ route('posts.show', $post->slug) }}" title="{{ $post->title }}" class="text-md font-medium text-gray-800 hover:underline hover:text-orange-400">
                                    {{ $post->title }}
                                </a>
                            </li>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-8 max-h-[250px] md:max-h-none overflow-y-auto">
                <h3 class="text-primary sticky top-0 bg-white">Các bài giải khác có thể bạn quan tâm</h3>
                <ul class="mx-0 px-0 grid lg:grid-cols-2 gap-4">
                    @foreach ($chapter->book->chapters as $otherChapter)
                        @if($otherChapter->id != $chapter->id)
                            <li class="mb-2">
                                <a title="{{ $otherChapter->name }}" href="{{ route('bookChapters.show', $otherChapter->slug) }}"
                                   class="font-bold text-green-700">
                                    {{ $otherChapter->name }}
                                </a>
                                <ul class="list-disc list-inside mt-2 mx-0 px-0">
                                    @foreach ($otherChapter->posts as $post)
                                        <li class="mb-2">
                                            <a title="{{ $post->title }}" href="{{ route('posts.show', $post->slug) }}"
                                               class="text-gray-800 hover:text-orange-400">
                                                {{ $post->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <div class="mt-8">
                <h2 class="text-3xl font-medium p-2 border-b-2 border-orange-400 mb-4">
                    Chương khác mới cập nhật
                </h2>

                <div class="p-2 border">
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($book->chapters as $otherChapter)
                            @if($otherChapter->id != $chapter->id)
                                <div>
                                    <h3 class="text-xl font-medium text-orange-400">
                                        <a href="{{ route('bookChapters.show', $otherChapter->slug) }}" title="{{ $otherChapter->name }}" class="hover:underline hover:text-orange-400">
                                            {{ $otherChapter->name }}
                                        </a>
                                    </h3>

                                    <div class="py-4 flex flex-col gap-2 border-dashed border-blue-600">
                                        @foreach($otherChapter->posts as $post)
                                            <li class="flex items-center gap-2">
                                                <span class="iconify text-xl" data-icon="mdi-chevron-right"></span>

                                                <a href="{{ route('posts.show', $post->slug) }}" title="{{ $post->title }}" class="text-md font-medium text-gray-800 hover:underline hover:text-orange-400">
                                                    {{ $post->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <x-footer-latest-posts :title="'Lời giải và bài tập ' . $category->name . ' đang được quan tâm'" :posts="$footerLatestPosts" />
            </div>
        </div>

        @include('layouts.sidebar-right')
    </div>
@endsection

@push('styles')
    <!-- Typography -->
    <link rel="stylesheet" href="{{ asset('css/typography.css') }}" />
@endpush
