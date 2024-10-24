@extends('admin_layouts.admin')

@section('content')
    <div>
        @if (session('success'))
            @include('layouts.message-success', ['message' => session('success')])
        @endif

        <h2 class="text-3xl font-bold mb-6">Books</h2>

        @if(count($books) > 0)
            <div class="w-full overflow-x-auto">
                <div class="min-w-[1170px]">
                    <!-- table header start -->
                    <div class="grid grid-cols-12 rounded-t-[10px] bg-primary px-5 py-4 lg:px-7.5 2xl:px-11">
                        <div class="col-span-3">
                            <h5 class="font-medium text-white">{{ __('Name') }}</h5>
                        </div>
                        <div class="col-span-4">
                            <h5 class="font-medium text-white">{{ __('Description') }}</h5>
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
                        <?php $currentGroup = null ?>

                        @foreach($books as $book)
                            @if($currentGroup == null || $book->group->id !== $currentGroup->id)
                                <?php $currentGroup = $book->group ?>

                                <div class="grid grid-cols-12 border-t border-[#EEEEEE] px-5 py-4 lg:px-7.5 2xl:px-11">
                                    <div class="col-span-12">
                                        <b class="text-primary">
                                            [{{ $book->group->category->name  }}]
                                            {{ $currentGroup->name }}
                                        </b>
                                    </div>
                                </div>
                            @endif

                            @include('admin.books.book-row', ['book' => $book])
                        @endforeach
                    </div>
                    <!-- table body end -->
                </div>
            </div>
        @else
            @include('layouts.message-info', ['message' => "Books are empty!"])
        @endif
    </div>

    @include('layouts.floating-button-right', ['link' => route('admin.books.create')])
@endsection
