@extends('admin_layouts.admin')

@section('content')
    <div>
        @if (session('success'))
            @include('layouts.message-success', ['message' => session('success')])
        @endif

        <h2 class="text-3xl font-bold mb-6">Book groups</h2>

        @if(count($groups) > 0)
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
                        <div class="col-span-3">
                            <h5 class="font-medium text-white">{{ __('Slug') }}</h5>
                        </div>
                        <div class="col-span-2">
                            <h5 class="text-right font-medium text-white">{{ __('Edit') }}</h5>
                        </div>
                    </div>
                    <!-- table header end -->

                    <!-- table body start -->
                    <div class="bg-white rounded-b-[10px]">
                        <?php $currentCategory = null ?>

                        @foreach($groups as $group)
                            @if($currentCategory == null || $group->category->id !== $currentCategory->id)
                                <?php $currentCategory = $group->category ?>

                                <div class="grid grid-cols-12 border-t border-[#EEEEEE] px-5 py-4 lg:px-7.5 2xl:px-11">
                                    <div class="col-span-12">
                                        <b class="text-primary">
                                            {{ $currentCategory->name }}
                                        </b>
                                    </div>
                                </div>
                            @endif

                            @include('admin.bookGroups.book-group-row', ['group' => $group])
                        @endforeach
                    </div>
                    <!-- table body end -->
                </div>
            </div>
        @else
            @include('layouts.message-info', ['message' => "Book groups are empty!"])
        @endif
    </div>

    @include('layouts.floating-button-right', ['link' => route('admin.bookGroups.create')])
@endsection
