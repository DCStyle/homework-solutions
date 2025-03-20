@extends('layouts.app')

@section('seo')
    {!! seo($group->getDynamicSEOData()) !!}
@endsection

@section('content')
    <div class="flex justify-between">
        <div class="sidebar-left-content w-[320px] h-auto flex-shrink-0 flex-grow-0 max-xl:w-[280px] max-md:hidden">
            <div class="sticky top-10 bg-white border shadow-md">
                <div class="p-4 text-md border-b border-b-gray-300">
                    <h2 class="text-xl text-orange-400 font-bold">
                        {{ $category->name }}
                    </h2>
                </div>

                <div class="p-4 text-md border-b border-b-gray-300">
                    <h2 class="text-xl font-bold">Cùng chuyên mục</h2>
                    <ul class="list-disc list-inside mt-4 mx-0 px-0">
                        @foreach ($category->bookGroups as $item)
                            <li class="mb-2">
                                <a title="{{ $item->name }}" href="{{ route('bookGroups.show', $item->slug) }}"
                                   class="text-gray-800 hover:text-orange-400 {{ $item->id == $group->id ? 'text-orange-400' : '' }}">
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
                {{ $group->name }}
            </h1>

            <nav aria-label="breadcrumb" class="my-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a title="{{ setting('site_name', 'Home') }}" href="{{ route('home') }}">{{ setting('site_name', 'Home') }}</a></li>
                    <li class="breadcrumb-item"><a title="{{ $category->name }}" href="{{ route('categories.show', $category->slug) }}">{{ $category->name }}</a></li>
                    <li class="breadcrumb-item"><a title="{{ $group->name }}" href="{{ route('bookGroups.show', $group->slug) }}" class="font-bold text-orange-400">{{ $group->name }}</a></li>
                </ol>
            </nav>

            <div class="mt-4 bg-white p-4 text-md text-green-700 border shadow-md">
                @if($group->description && strlen(trim($group->description)) > 0)
                    <div id="post-content">{!! $group->description !!}</div>
                @else
                    <h2 class="text-xl">{!! "Dưới đây là toàn bộ bài giải <b class='text-black'>$group->name</b>.
                           Cách hướng dẫn, trình bày lời giải chi tiết, dễ hiểu.
                           Học sinh muốn xem bài nào thì click vào tên bài để xem.
                           Chúc các em học tốt và nắm vững kiến thức <b>$group->name</b> trên <b class='underline'><a href='" . url('/') . "'>" . setting('site_name', 'Homework Solutions') . "</a></b>."
                    !!}</h2>
                @endif

                <div class="mt-4 max-h-[250px] md:max-h-none overflow-y-auto">
                    <h3 class="text-primary sticky top-0 bg-white">Các bài giải khác có thể bạn quan tâm</h3>
                    <ul class="mx-0 px-0 grid lg:grid-cols-3">
                        @foreach ($category->bookGroups as $otherGroup)
                            @if($otherGroup->id != $group->id)
                                <li class="mb-2">
                                    <a title="{{ $otherGroup->name }}" href="{{ route('bookGroups.show', $otherGroup->slug) }}"
                                       class="font-bold text-green-700">
                                        {{ $otherGroup->name }}
                                    </a>
                                    <ul class="list-disc list-inside mt-2 mx-0 px-0">
                                        @foreach ($otherGroup->books as $book)
                                            <li class="mb-2">
                                                <a title="{{ $book->name }}" href="{{ route('books.show', $book->slug) }}"
                                                   class="text-gray-800 hover:text-orange-400">
                                                    {{ $book->name }}
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

            <div class="mt-8">
                <h2 class="text-center text-3xl font-medium underline mb-4">
                    {{ $group->name }}
                </h2>

                <div class="p-2 border">
                    <div class="grid gap-4 md:grid-cols-2">
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
            </div>

            <div class="mt-8">
                <h2 class="text-3xl font-medium p-2 border-b-2 border-orange-400 mb-4">
                    Môn học khác mới cập nhật
                </h2>

                <div class="p-2 border">
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($category->bookGroups as $otherGroup)
                            @if($otherGroup->id != $group->id)
                                <div>
                                    <h3 class="text-xl font-medium text-orange-400">
                                        <a href="{{ route('bookGroups.show', $otherGroup->slug) }}" title="{{ $otherGroup->name }}" class="hover:underline hover:text-orange-400">
                                            {{ $otherGroup->name }}
                                        </a>
                                    </h3>

                                    <div class="py-4 flex flex-col gap-2 border-dashed border-blue-600">
                                        @foreach($otherGroup->books as $book)
                                            <li class="flex items-center gap-2">
                                                <span class="iconify text-xl" data-icon="mdi-chevron-right"></span>

                                                <a href="{{ route('books.show', $book->slug) }}" title="{{ $book->name }}" class="text-md font-medium text-gray-800 hover:underline hover:text-orange-400">
                                                    {{ $book->name }}
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
