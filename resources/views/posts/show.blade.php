@extends('layouts.app')

@section('pageTitle')
    {{ $post->title }} |
@endsection

@section('content')
    <div class="flex justify-between">
        <div class="sidebar-left-content w-[320px] h-full  flex-shrink-0 flex-grow-0 max-xl:w-[280px] max-md:hidden">
            <div class="sticky top-10 bg-white border shadow-md">
                <div class="p-4 text-md border-b border-b-gray-300">
                    <h2 class="text-xl text-orange-400 font-bold">
                        {{ $post->chapter->book->name }}
                    </h2>
                </div>

                <div class="p-4 text-md border-b border-b-gray-300">
                    <h2 class="text-xl font-bold">Bài học cùng chương</h2>
                    <ul class="list-disc list-inside mt-4">
                        @foreach ($post->chapter->posts as $item)
                            <li class="mb-2">
                                <a title="{{ $item->title }}" href="{{ route('posts.show', $item->slug) }}"
                                    class="text-gray-800 hover:text-orange-400 {{ $item->id == $post->id ? 'text-orange-400' : '' }}">
                                    {{ $item->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="p-4 text-md">
                    <h2 class="text-xl font-bold">Bài học chương khác</h2>
                    <ul class="mt-4">
                        @foreach ($post->chapter->book->chapters as $chapter)
                            @if($chapter->id != $post->chapter_id)
                                <li class="mb-4">
                                    <h3 class="font-bold text-green-700">{{ $chapter->name }}</h3>
                                    <ul class="list-disc list-inside mt-2">
                                        @foreach ($chapter->posts as $item)
                                            <li class="mb-2">
                                                <a title="{{ $item->title }}" href="{{ route('posts.show', $item->slug) }}"
                                                   class="text-gray-800 hover:text-orange-400">
                                                    {{ $item->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="mx-auto p-6 min-w-0 max-xl:p-4 max-md:p2">
            <h1 class="text-2xl font-bold text-orange-400 py-2 border-b-2 border-b-blue-800">
                <a title="{{ $post->title }}" href="{{ route('posts.show', $post->slug) }}">
                    [{{ $post->chapter->book->name }}]
                    {{ $post->title }}
                </a>
            </h1>

            <nav aria-label="breadcrumb" class="my-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a title="{{ __('Home') }}" href="{{ route('home') }}">{{ __('Home') }}</a></li>
                    <li class="breadcrumb-item"><a title="{{ $post->chapter->book->group->category->name }}" href="{{ route('categories.show', $post->chapter->book->group->category->slug) }}">{{ $post->chapter->book->group->category->name }}</a></li>
                    <li class="breadcrumb-item"><a title="{{ $post->chapter->book->name }}" href="{{ route('books.show', $post->chapter->book->slug) }}">{{ $post->chapter->book->name }}</a></li>
                    <li class="breadcrumb-item"><a title="{{ $post->title }}" href="{{ route('posts.show', $post->slug) }}" class="text-orange-400">{{ $post->title }}</a></li>
                </ol>
            </nav>

            <div class="mt-4 bg-white p-4 text-md text-green-700 border shadow-md">
                <h2 class="text-xl">{!! "Hướng dẫn học bài: <b>$post->title</b>.
                    Đây là sách giáo khoa nằm trong bộ sách <b>'{$post->chapter->book->name}'</b> được biên soạn theo chương trình đổi mới của Bộ giáo dục.
                    Hi vọng, với cách hướng dẫn cụ thể và giải chi tiết các bé sẽ nắm bài học tốt hơn."
            !!}</h2>
            </div>

            <div class="mt-4">
                {!! $post->content !!}
            </div>

            <div class="mt-8">
                <h2 class="text-3xl font-medium p-2 border-b-2 border-orange-400 mb-4">
                    Giải bài tập những môn khác
                </h2>

                <div class="p-2 border">
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($post->chapter->book->group->category->bookGroups as $group)
                            <div>
                                <h3 class="text-xl font-medium text-blue-800">{{ $group->name }}</h3>

                                <div class="py-4 flex flex-col gap-2 border-dashed border-blue-600">
                                    @foreach($group->books as $book)
                                        <li class="flex items-center gap-2">
                                            <span class="iconify text-xl" data-icon="mdi-chevron-right"></span>

                                            <a href="{{ route('books.show', $book->slug) }}" title="{{ $book->name }}" class="text-md font-medium text-gray-800 hover:underline hover:text-orange-400">
                                                {{ $book->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @include('layouts.sidebar-right')
    </div>
@endsection
