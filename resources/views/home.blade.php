@extends('layouts.app')

@section('content')
    <div class="p-2 bg-white md:bg-[#f9f9f8] md:p-6">
        <div class="container mx-auto">
            <div class="flex justify-between mb-4 md:mb-8 lg:mb-12">
                <div class="hidden flex-1 w-full mr-4 md:block">
                    <img src="{{ asset('images/education-0.svg')  }}"
                         alt="{{ config('app.name') }}"
                         class="w-full rounded-xl"
                    />
                </div>

                <div class="w-full flex-shrink-0 flex-grow-0 md:w-[35%]">
                    <p class="font-bold mb-4 max-md:text-center">
                        Vì sao <span class="text-primary">{{ config('app.name') }}</span> được hàng triệu học sinh cả nước tin tưởng?
                    </p>

                    <ul class="list-none grid grid-cols-2 gap-2 md:block">
                        @for($i = 1;$i <= 4;$i++)
                            <li class="flex items-center gap-x-2 mb-2 max-md:p-4 max-md:rounded-xl max-md:drop-shadow-2xl max-md:bg-white">
                                <img src="{{ asset('images/lgh-trust-icon' . $i . '.png')  }}" alt="{{ config('app.name') }}" class="w-8 h-auto" />
                                <p class="font-bold text-sm">
                                    @switch($i)
                                        @case(1)
                                            Đầy đủ lời giải SGK - SBT - VBT từ lớp 1 - lớp 12
                                            @break
                                        @case(2)
                                            Kho bài tập trắc nghiệm bám sát theo dạng bài
                                            @break
                                        @case(3)
                                            Hệ thống đề thi phong phú, chất lượng
                                            @break
                                        @case(4)
                                            Lý thuyết dạng sơ đồ tư duy dễ hiểu
                                            @break
                                    @endswitch
                                </p>
                            </li>
                        @endfor
                    </ul>
                </div>
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
                    <div class="flex items-center gap-2 bg-white p-2 mb-2 shadow-md border-b-2 border-orange-400 md:gap-4 md:p-4">
                        <span class="iconify text-2xl" data-icon="mdi-school"></span>

                        <p class="block text-xl font-medium text-gray-800 md:text-2xl md:font-bold">
                            {{ $category->name }}
                        </p>

                        <a class="ml-auto px-2 py-1 rounded-full bg-blue-500 text-white font-medium text-sm js-category-toggle-button w-[160px] text-center collapsed hover:bg-blue-600 md:px-4 md:py-2 md:text-md"
                           data-bs-toggle="collapse" href="#category-collapse-{{ $category->id }}" role="button" aria-expanded="false" aria-controls="collapseExample"
                        >
                            <span class="inactive inline-flex items-center">
                                {{ __('View more') }}

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
                @endforeach
            </div>
        </div>
    </div>

    <div class="relative md:p-4">
        <img src="{{ asset('images/bg-section-steps.png') }}"
             alt="{{ config('app.name') }}"
             class="absolute top-0 left-0 bottom-0 right-0"
        />

        <div class="container mx-auto py-12 relative z-2">
            <div class="w-full flex flex-wrap items-center justify-between">
                <div class="w-full mb-4 md:w-1/2 md:mb-0">
                    <img src="{{ asset('images/step-thumb.png')  }}"
                         alt="{{ config('app.name') }}"
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
                        @for($i = 1;$i <= 3;$i++)
                            <div class="flex items-center gap-x-2 relative">
                                <span class="iconify text-3xl text-orange-400 absolute -left-12"
                                      data-icon="mdi-play-circle-outline">
                                </span>

                                <div class="w-[92px] h-[33px] inline-flex items-center justify-center font-bold text-uppercase text-2xl text-white bg-no-repeat" style="background-image: url('{{ asset('images/step-bg.png') }}'); background-position: 0 0;">
                                    Bước {{ $i }}
                                </div>

                                <div class="bg-[#2c9ae233] bg-opacity-40 p-2 rounded-2xl text-gray-700 flex-1">
                                    <div class="border !border-dashed border-white p-2 rounded-xl">
                                        @switch($i)
                                            @case(1)
                                                <p>Mở trình duyệt web có sẵn lên</p>
                                                <p class="font-bold">Có thể là: Chrome, Cốc cốc...</p>
                                                @break
                                            @case(2)
                                                <p>Gõ tên bài cần giải + {{ config('app.name') }}</p>
                                                <p class="font-bold">VD: Giải toán 7 bài: cộng phân số {{ config('app.name') }}</p>
                                                @break
                                            @case(3)
                                                <p>Các kết quả tìm kiếm hiện ra</p>
                                                <p class="font-bold">Chọn ngay những kết quả đầu tiên</p>
                                                @break
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
