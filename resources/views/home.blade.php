@extends('layouts.app')

@section('content')
    <div class="p-2 bg-white md:bg-[#f9f9f8] md:p-6">
        <div class="container mx-auto">
            <div class="flex justify-between mb-4 md:mb-8 lg:mb-12">
                <div class="hidden flex-1 w-full mr-4 md:block">
                    @if(setting('home_hero_banner_url'))
                        <a href="{{ setting('home_hero_banner_url') }}" title="{{ setting('site_name', 'Homework Solutions') }}">
                            <img src="{{ setting('home_hero_banner') ? Storage::url(setting('home_hero_banner')) : asset('images/education-0.svg') }}"
                                alt="{{ setting('site_name', 'Homework Solutions') }}"
                                class="w-full rounded-xl"
                            />
                        </a>
                    @else
                        <img src="{{ setting('home_hero_banner') ? Storage::url(setting('home_hero_banner')) : asset('images/education-0.svg') }}"
                            alt="{{ setting('site_name', 'Homework Solutions') }}"
                            class="w-full rounded-xl"
                        />
                    @endif
                </div>

                @if(setting('home_hero_description') !== null)
                    <div class="w-full flex-shrink-0 flex-grow-0 md:w-[35%]">
                        {!! setting('home_hero_description') !!}
                    </div>
                @endif
            </div>

            <h3 class="text-center text-2xl font-medium text-gray-700 mb-4">
                LỰA CHỌN LỚP ĐỂ XEM BÀI SOẠN VÀ LỜI GIẢI
            </h3>

            <div class="hidden grid-cols-3 gap-2 mb-4 md:grid md:grid-cols-7">
                @foreach ($categories as $category)
                    @if($loop->index < 12)
                        <a href="{{ route('categories.show', $category->slug) }}" class="px-4 py-2 text-xl text-center border rounded-3xl text-gray-800 whitespace-nowrap overflow-hidden text-ellipsis hover:bg-gradient-to-r from-cyan-500 to-blue-500 hover:text-white">{{ $category->name }}</a>
                    @endif
                @endforeach
            </div>

            <div class="md:rounded-lg md:p-4 md:bg-gray-200">
                @foreach ($categories as $category)
                    <div class="flex items-center gap-2 bg-white p-2 mb-2 shadow-md border-b-2 border-orange-400 md:gap-4 md:p-4 collapsed"
                         data-bs-toggle="collapse" href="#category-collapse-{{ $category->id }}" role="button" aria-expanded="false" aria-controls="collapseExample"
                    >
                        <span class="iconify text-2xl" data-icon="mdi-school"></span>

                        <p class="block text-xl font-medium text-gray-800 my-0 md:text-2xl md:font-bold">
                            {{ $category->name }}
                        </p>

                        <a class="ml-auto px-2 py-1 rounded-full bg-blue-500 text-white font-medium text-sm js-category-toggle-button w-[160px] text-center hover:bg-blue-600 md:px-4 md:py-2 md:text-md"
                        >
                            <span class="inactive inline-flex items-center">
                                Xem thêm
                                <span class="iconify text-2xl" data-icon="mdi-chevron-right"></span>
                            </span>

                            <span class="active inline-flex items-center">
                                <span class="iconify text-2xl" data-icon="mdi-chevron-down"></span>
                            </span>
                        </a>
                    </div>

                    <div class="collapse mb-4" id="category-collapse-{{ $category->id }}">
                        <div class="category-container bg-white p-4 border-2 border-blue-600 rounded-md">
                            <div class="category-arrow" style="background-image: url('{{ asset('images/shapen.png') }}')"></div>

                            <div class="grid gap-4 md:grid-cols-3">
                                @foreach($category->bookGroups as $group)
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-700 inline-flex items-center py-2 mb-2 border-b-2 border-orange-400">
                                            <span class="iconify text-2xl text-primary" data-icon="mdi-menu-right"></span>

                                            {{ $group->name }}
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
                @endforeach
            </div>
        </div>
    </div>

    <div class="relative md:p-4">
        <img src="{{ asset('images/bg-section-steps.png') }}"
             alt="{{ setting('site_name', 'Homework Solutions') }}"
             class="absolute top-0 left-0 bottom-0 right-0"
        />

        <div class="container mx-auto py-12 relative z-2">
            <div class="w-full flex flex-wrap items-center justify-between">
                <div class="w-full mb-4 md:w-1/2 md:mb-0">
                    <img src="{{ setting('home_instruction_banner') ? Storage::url(setting('home_instruction_banner')) : asset('images/step-thumb.png') }}"
                         alt="{{ setting('site_name', 'Homework Solutions') }}"
                         class="w-full"
                    />
                </div>

                <div class="w-full md:w-1/2 md:px-4">
                    <div class="mb-4">
                        <h3 class="inline-block font-bold text-2xl text-orange-500 relative py-2 md:text-4xl">
                            Cách tìm bài giải trên mạng
                            <span class="block border-t-2 w-[80%] border-primary absolute bottom-0 left-0"></span>
                        </h3>
                    </div>

                    <div class="flex flex-col gap-y-4 pl-8 border-l-2 border-orange-400 relative">
                        @php $steps = json_decode(setting('home_instruction_steps'), true) ?? []; @endphp

                        @foreach($steps as $index => $step)
                            <div class="flex items-center gap-x-2 relative">
                                <span class="iconify text-3xl text-orange-400 absolute -left-12"
                                      data-icon="mdi-play-circle-outline">
                                </span>

                                <div class="w-[92px] h-[33px] inline-flex items-center justify-center font-bold text-uppercase text-2xl text-white bg-no-repeat" style="background-image: url('{{ asset('images/step-bg.png') }}'); background-position: 0 0;">
                                    {!! $step['title'] !!}
                                </div>

                                <div class="bg-[#2c9ae233] bg-opacity-40 p-2 rounded-2xl text-gray-700 flex-1">
                                    <div class="border !border-dashed border-white p-2 rounded-xl">
                                        {!! $step['description'] !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Fallback if no steps are configured --}}
                        @if(empty($steps))
                            <div class="text-gray-500 italic">
                                Chưa có bước hướng dẫn nào được cấu hình.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @isset($articles)
        @include('articles.latest', ['articles' => $articles, 'hasMore' => true])
    @endisset
@endsection
