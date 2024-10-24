@extends('admin_layouts.admin')

@section('content')
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 2xl:gap-7.5">
        @include('admin_layouts.dashboard_card_item', ['cardNumber' => $userCount, 'cardTitle' => __('Total users'), 'cardIcon' => 'mdi-account-outline'])

        @include('admin_layouts.dashboard_card_item', ['cardNumber' => $categoryCount, 'cardTitle' => __('Total categories'), 'cardIcon' => 'mdi-folder-outline'])

        @include('admin_layouts.dashboard_card_item', ['cardNumber' => $bookCount, 'cardTitle' => __('Total books'), 'cardIcon' => 'mdi-book-outline'])

        @include('admin_layouts.dashboard_card_item', ['cardNumber' => $postCount, 'cardTitle' => __('Total posts'), 'cardIcon' => 'mdi-newspaper'])
    </div>
@endsection
