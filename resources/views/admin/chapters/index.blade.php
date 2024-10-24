@extends('admin_layouts.admin')

@section('content')
    <div>
        @if (session('success'))
            @include('layouts.message-success', ['message' => session('success')])
        @endif

        <h2 class="text-3xl font-bold mb-2">Chapters</h2>

        <p class="mb-6">
            @include('layouts.badge-primary', ['content' => $book->group->category->name])
            @include('layouts.badge-secondary', ['content' => $book->group->name])
            <span class="font-medium">{{ $book->name }}</span>
        </p>

        @if(count($chapters) > 0)
            <div class="w-full overflow-x-auto">
                <div class="min-w-[1170px]">
                    <!-- table header start -->
                    <div class="grid grid-cols-12 rounded-t-[10px] bg-primary px-5 py-4 lg:px-7.5 2xl:px-11">
                        <div class="col-span-4">
                            <h5 class="font-medium text-white">{{ __('Name') }}</h5>
                        </div>
                        <div class="col-span-3">
                            <h5 class="font-medium text-white">{{ __('Slug') }}</h5>
                        </div>
                        <div class="col-span-2">
                            <h5 class="font-medium text-white">{{ __('Created at') }}</h5>
                        </div>
                        <div class="col-span-2">
                            <h5 class="font-medium text-white">{{ __('Updated at') }}</h5>
                        </div>
                        <div class="col-span-1">
                            <h5 class="text-right font-medium text-white">{{ __('Edit') }}</h5>
                        </div>
                    </div>
                    <!-- table header end -->

                    <!-- table body start -->
                    <div class="bg-white rounded-b-[10px]">
                        @foreach($chapters as $chapter)
                            @include('admin.chapters.chapter-row', ['chapter' => $chapter])
                        @endforeach
                    </div>
                    <!-- table body end -->
                </div>
            </div>
        @else
            @include('layouts.message-info', ['message' => "Chapters are empty!"])
        @endif
    </div>

    @include('layouts.floating-button-right', ['link' => route('admin.books.createChapter', $book->slug)])
@endsection
