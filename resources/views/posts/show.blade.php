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
                <li class="breadcrumb-item"><a title="{{ $post->chapter->name }}" href="{{ route('bookChapters.show', $post->chapter->slug) }}">{{ $post->chapter->name }}</a></li>
            </ol>
        </nav>

        <div class="mt-4">
            {!! $post->content !!}
        </div>
    </div>
@endsection
