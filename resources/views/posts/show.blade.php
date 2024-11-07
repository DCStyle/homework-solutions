@extends('layouts.app')

@section('pageTitle')
    {{ $post->title }} |
@endsection

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold text-orange-400 py-2 border-b-2 border-b-blue-800">
            {{ $post->title }}
        </h1>

        <nav aria-label="breadcrumb" class="my-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a title="{{ __('Home') }}" href="{{ route('home') }}">{{ __('Home') }}</a></li>
                <li class="breadcrumb-item"><a title="{{ $post->chapter->book->group->category->name }}" href="{{ route('categories.show', $post->chapter->book->group->category->slug) }}">{{ $post->chapter->book->group->category->name }}</a></li>
                <li class="breadcrumb-item"><a title="{{ $post->chapter->book->name }}" href="{{ route('books.show', $post->chapter->book->slug) }}">{{ $post->chapter->book->name }}</a></li>
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
    </div>
@endsection
