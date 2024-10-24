@extends('layouts.app')

@section('pageTitle')
    {{ $book->name }} |
@endsection

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold text-orange-400 py-2 border-b-2 border-b-blue-800">
            {{ $book->name }}
        </h1>

        <nav aria-label="breadcrumb" class="my-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a title="{{ __('Home') }}" href="{{ route('home') }}">{{ __('Home') }}</a></li>
                <li class="breadcrumb-item"><a title="{{ $book->group->category->name }}" href="{{ route('categories.show', $book->group->category->slug) }}">{{ $book->group->category->name }}</a></li>
                <li class="breadcrumb-item"><a title="{{ $book->name }}" href="{{ route('books.show', $book->slug) }}">{{ $book->name }}</a></li>
            </ol>
        </nav>

        @if(strlen($book->description))
            <div class="mt-4 bg-white p-4 text-md text-green-700 border shadow-md">
                <h2>{{ $book->description }}</h2>
            </div>
        @endif

        <div class="mt-8">
            <h2 class="text-center text-3xl font-medium underline mb-4">
                {{ $book->group->name }} - {{ $book->name }}
            </h2>

            <div class="p-2 border">
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($book->chapters as $chapter)
                        <div>
                            <h3 class="text-xl font-medium text-blue-800">{{ $chapter->name }}</h3>

                            <div class="py-4 flex flex-col gap-2 border-dashed border-blue-600">
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
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
