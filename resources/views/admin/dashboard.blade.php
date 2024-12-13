@extends('admin_layouts.admin')

@section('content')
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 2xl:gap-7.5">
        @include('admin_layouts.dashboard_card_item', ['cardNumber' => number_format($userCount), 'cardTitle' => 'Tổng số thành viên', 'cardIcon' => 'mdi-account-outline'])

        @include('admin_layouts.dashboard_card_item', ['cardNumber' => number_format($categoryCount), 'cardTitle' => 'Tổng số danh mục', 'cardIcon' => 'mdi-folder-outline'])

        @include('admin_layouts.dashboard_card_item', ['cardNumber' => number_format($groupCount), 'cardTitle' => 'Tổng số môn học', 'cardIcon' => 'mdi-text-long'])

        @include('admin_layouts.dashboard_card_item', ['cardNumber' => number_format($bookCount), 'cardTitle' => 'Tổng số cuốn sách', 'cardIcon' => 'mdi-book-outline'])

        @include('admin_layouts.dashboard_card_item', ['cardNumber' => number_format($chapterCount), 'cardTitle' => 'Tổng số chương', 'cardIcon' => 'mdi-bookmark-multiple-outline'])

        @include('admin_layouts.dashboard_card_item', ['cardNumber' => number_format($postCount), 'cardTitle' => 'Tổng số bài viết', 'cardIcon' => 'mdi-newspaper'])
    </div>
@endsection
