@extends('layouts.app')

@section('pageTitle')
    {{ $category->name }} |
@endsection

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold text-orange-400 py-2 border-b-2 border-b-blue-800">
            {{ $category->name }}
        </h1>

        <nav aria-label="breadcrumb" class="my-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a title="{{ __('Home') }}" href="{{ route('home') }}">{{ __('Home') }}</a></li>
                <li class="breadcrumb-item"><a title="{{ $category->name }}" href="{{ route('categories.show', $category->slug) }}">{{ $category->name }}</a></li>
            </ol>
        </nav>

        @if(strlen($category->description))
            <div class="mt-4 bg-white p-4 text-md text-green-700 border shadow-md">
                <h2>{{ $category->description }}</h2>
            </div>
        @endif

        <div class="mt-8">
            <div class="p-2 border">
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($category->bookGroups as $group)
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
@endsection
