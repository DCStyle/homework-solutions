@extends('layouts.app')

@section('seo')
    {!! seo($category->getDynamicSEOData()) !!}
@endsection

@section('content')
    <div class="flex justify-between">
        <div class="sidebar-left-content w-[320px] h-auto flex-shrink-0 flex-grow-0 max-xl:w-[280px] max-md:hidden">
            <div class="sticky top-10 bg-white border shadow-md">
                <div class="p-4 text-md">
                    <h2 class="text-xl font-bold">Mục lục quan tâm</h2>
                    <ul class="mt-4 mx-0 px-0">
                        @foreach ($category->bookGroups as $group)
                            <li class="mb-4">
                                <a href="{{ route('bookGroups.show', $group->slug) }}" class="font-bold text-green-700">
                                    {{ $group->name }}
                                </a>
                                <ul class="list-disc list-inside mt-2 mx-0 px-0">
                                    @foreach ($group->books as $item)
                                        <li class="mb-2">
                                            <a title="{{ $item->name }}" href="{{ route('books.show', $item->slug) }}"
                                               class="text-gray-800 hover:text-orange-400">
                                                {{ $item->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="mx-auto p-6 w-full min-w-0 max-xl:p-4 max-md:p2">
            <h1 class="text-2xl font-bold text-orange-400 py-2 border-b-2 border-b-blue-800">
                {{ $category->name }}
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
                        <a title="{{ $category->name }}" href="{{ route('categories.show', $category->slug) }}" class="font-bold text-orange-400 hover:text-orange-500">
                            {{ $category->name }}
                        </a>
                    </li>
                </ol>
            </nav>

            <div class="mt-4 bg-white p-4 text-md text-green-700 border shadow-md">
                @if($category->description && strlen(trim($category->description)) > 0)
                    <h2 class="text-xl">
                        {!! "Soạn bài, giải bài tập tất cả các môn học <b class='text-black'>$category->name</b>.
                               Cách trình bày dễ hiểu, khoa học.
                               Các em học sinh, thầy cô giáo muốn xem môn học nào thì click vào môn học đó để xem.
                               Để tìm các bài soạn, bài giải <b class='text-black'>$category->name</b> trên mạng.
                               Hãy gõ vào ô tìm kiếm google dòng chữ: Soạn <b>$category->name</b> <b class='underline'><a href='" . url('/') . "'>" . config('app.name', 'Laravel') . "</a></b>."
                        !!}
                    </h2>
                @else
                    <div id="post-content">{!! $category->description !!}</div>
                @endif
            </div>

            <div class="mt-8">
                <div class="p-2 border">
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($category->bookGroups as $group)
                            <div>
                                <h3 class="text-xl font-medium text-orange-400">
                                    <a href="{{ route('bookGroups.show', $group->slug) }}" title="{{ $group->name }}" class="hover:text-orange-400">
                                        {{ $group->name }}
                                    </a>
                                </h3>

                                <div class="py-4 flex flex-col gap-2 border-dashed border-blue-600">
                                    @foreach($group->books as $book)
                                        <li class="flex items-center gap-2">
                                            <span class="iconify text-xl" data-icon="mdi-chevron-right"></span>

                                            <a href="{{ route('books.show', $book->slug) }}" title="{{ $book->name }}" class="text-md font-medium text-gray-800 hover:text-orange-400">
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
