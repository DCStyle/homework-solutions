@extends('layouts.app')

@section('content')
    <div class="bg-[#f9f9f8] p-6">
        <div class="container mx-auto">
            <div class="flex justify-between mb-12">
                <div class="flex-1 w-full mr-4">
                    <img src="{{ asset('images/education-0.svg')  }}"
                         alt="{{ config('app.name') }}"
                         class="w-full rounded-xl"
                    />
                </div>

                <div class="w-[35%] flex-shrink-0 flex-grow-0">
                    <p class="font-bold mb-4">
                        Vì sao <span class="text-primary">{{ config('app.name') }}</span> được hàng triệu học sinh cả nước tin tưởng?
                    </p>

                    <ul class="list-none">
                        <li class="flex items-center gap-x-2 mb-2">
                            <img src="{{ asset('images/lgh-trust-icon1.png')  }}" alt="{{ config('app.name') }}" class="w-8 h-auto" />
                            <p class="font-bold text-sm">
                                Đầy đủ lời giải SGK - SBT - VBT từ lớp 1 - lớp 12
                            </p>
                        </li>

                        <li class="flex items-center gap-x-2 mb-2">
                            <img src="{{ asset('images/lgh-trust-icon2.png')  }}" alt="{{ config('app.name') }}" class="w-8 h-auto" />
                            <p class="font-bold text-sm">
                                Kho bài tập trắc nghiệm bám sát theo dạng bài
                            </p>
                        </li>

                        <li class="flex items-center gap-x-2 mb-2">
                            <img src="{{ asset('images/lgh-trust-icon3.png')  }}" alt="{{ config('app.name') }}" class="w-8 h-auto" />
                            <p class="font-bold text-sm">
                                Hệ thống đề thi phong phú, chất lượng
                            </p>
                        </li>

                        <li class="flex items-center gap-x-2 mb-2">
                            <img src="{{ asset('images/lgh-trust-icon4.png')  }}" alt="{{ config('app.name') }}" class="w-8 h-auto" />
                            <p class="font-bold text-sm">
                                Lý thuyết dạng sơ đồ tư duy dễ hiểu
                            </p>
                        </li>
                    </ul>
                </div>
            </div>

            <h3 class="text-center text-2xl font-medium text-gray-700 mb-4">
                LỰA CHỌN LỚP ĐỂ XEM BÀI SOẠN VÀ LỜI GIẢI
            </h3>

            <div class="bg-gray-200 rounded-lg p-4">
                @foreach ($categories as $category)
                    <div class="flex items-center gap-4 bg-white p-4 mb-2 shadow-md border-b-2 border-orange-400">
                        <span class="iconify text-2xl" data-icon="mdi-school"></span>

                        <p class="block text-2xl font-bold text-gray-800">
                            {{ $category->name }}
                        </p>

                        <a class="ml-auto px-4 py-2 rounded-full bg-blue-500 text-white font-medium text-md hover:bg-blue-600"
                           data-bs-toggle="collapse" href="#category-collapse-{{ $category->id }}" role="button" aria-expanded="false" aria-controls="collapseExample"
                        >
                            {{ __('View more') }}
                        </a>
                    </div>

                    <div class="collapse mb-4" id="category-collapse-{{ $category->id }}">
                        <div class="bg-white p-4 border-2 border-blue-600 rounded-md">
                            <div class="grid gap-4 md:{{ $category->bookGroups->count() >= 3 ? 'grid-cols-3' : 'grid-cols-2' }}">
                                @foreach($category->bookGroups as $group)
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-700 py-2 mb-2 border-b-2 border-orange-400">{{ $group->name }}</h3>

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

    <div class="p-4 relative">
        <img src="{{ asset('images/bg-section-steps.png') }}"
             alt="{{ config('app.name') }}"
             class="absolute top-0 left-0 bottom-0 right-0"
        />

        <div class="container mx-auto py-12 relative z-2">
            <div class="w-full flex items-center justify-between">
                <div class="w-1/2">
                    <img src="{{ asset('images/step-thumb.png')  }}"
                         alt="{{ config('app.name') }}"
                         class="w-full"
                    />
                </div>

                <div class="w-1/2 px-4">
                    <div class="mb-4">
                        <h3 class="inline-block font-bold text-4xl text-orange-500 relative py-2">
                            Cách tìm bài giải trên mạng
                            <span class="block border-t-2 w-[80%] border-primary absolute bottom-0 left-0"></span>
                        </h3>
                    </div>

                    <div class="flex flex-col gap-y-4 pl-8 border-l-2 border-orange-400 relative">
                        <div class="flex items-center gap-x-2 relative">
                            <span class="iconify text-3xl text-primary absolute -left-12"
                                  data-icon="mdi-play-circle-outline">
                            </span>

                            <div class="bg-primary font-bold text-white p-2 rounded">
                                Bước 1
                            </div>

                            <div class="bg-[#59C2FF] bg-opacity-40 p-2 rounded-2xl text-gray-700 flex-1">
                                <div class="border !border-dashed border-white p-2 rounded-xl">
                                    <p>Mở trình duyệt web có sẵn lên</p>
                                    <p class="font-bold">Có thể là: Chrome, Cốc cốc...</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-x-2 relative">
                            <span class="iconify text-3xl text-primary absolute -left-12"
                                  data-icon="mdi-play-circle-outline">
                            </span>

                            <div class="bg-primary font-bold text-white p-2 rounded">
                                Bước 2
                            </div>

                            <div class="bg-[#59C2FF] bg-opacity-40 p-2 rounded-2xl text-gray-700 flex-1">
                                <div class="border !border-dashed border-white p-2 rounded-xl">
                                    <p>Gõ tên bài cần giải + {{ config('app.name') }}</p>
                                    <p class="font-bold">VD: Giải toán 7 bài: cộng phân số {{ config('app.name') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-x-2 relative">
                            <span class="iconify text-3xl text-primary absolute -left-12"
                                  data-icon="mdi-play-circle-outline">
                            </span>

                            <div class="bg-primary font-bold text-white p-2 rounded">
                                Bước 3
                            </div>

                            <div class="bg-[#59C2FF] bg-opacity-40 p-2 rounded-2xl text-gray-700 flex-1">
                                <div class="border !border-dashed border-white p-2 rounded-xl">
                                    <p>Các kết quả tìm kiếm hiện ra</p>
                                    <p class="font-bold">Chọn ngay những kết quả đầu tiên</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
