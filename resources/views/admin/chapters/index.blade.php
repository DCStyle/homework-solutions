@extends('admin_layouts.admin')

@section('title', 'Danh Sách Chương')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">Danh Sách Chương</h2>
                    <p class="mt-1 text-white/90">Quản lý tất cả các chương trong sách "{{ $book->name }}"</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.books.createChapter', $book->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white py-2.5 px-4 text-center font-medium text-primary hover:bg-gray-100 transition-all duration-200 shadow-sm">
                        <span class="iconify" data-icon="mdi-plus"></span>
                        Thêm Chương Mới
                    </a>
                    <a href="{{ route('admin.books.edit', $book->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/20 py-2.5 px-4 text-center font-medium text-white hover:bg-white/30 transition-all duration-200">
                        <span class="iconify" data-icon="mdi-book-outline"></span>
                        Quay Lại Sách
                    </a>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 h-64 w-64 rounded-full bg-white/5"></div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg bg-green-100 p-4 text-green-700 flex items-center shadow-md animate-fadeIn">
                <span class="iconify mr-2 text-xl" data-icon="mdi-check-circle"></span>
                <span>{{ session('success') }}</span>
                <button type="button" class="ml-auto" onclick="this.parentElement.remove()">
                    <span class="iconify" data-icon="mdi-close"></span>
                </button>
            </div>
        @endif

        <!-- Breadcrumb Navigation -->
        <div class="flex flex-wrap items-center gap-2 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
            <div class="flex items-center">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-500 mr-2">
                <span class="iconify" data-icon="mdi-folder-outline"></span>
            </span>
                <div>
                    @include('layouts.badge-primary', ['content' => $book->group->category->name])
                </div>
            </div>

            <span class="text-gray-400">
            <span class="iconify" data-icon="mdi-chevron-right"></span>
        </span>

            <div class="flex items-center">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-500 mr-2">
                <span class="iconify" data-icon="mdi-bookshelf"></span>
            </span>
                <div>
                    @include('layouts.badge-secondary', ['content' => $book->group->name])
                </div>
            </div>

            <span class="text-gray-400">
            <span class="iconify" data-icon="mdi-chevron-right"></span>
        </span>

            <div class="flex items-center">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 mr-2">
                <span class="iconify" data-icon="mdi-book-outline"></span>
            </span>
                <span class="font-medium text-gray-700">{{ $book->name }}</span>
            </div>
        </div>

        <!-- Chapters Table Card -->
        <div class="rounded-xl border border-stroke bg-white shadow-md overflow-hidden">
            <!-- Table with responsive design -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tên Chương
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Bài Viết
                        </th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Hành Động
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @if(count($chapters) > 0)
                        @foreach($chapters as $chapter)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                            <span class="iconify" data-icon="mdi-bookmark-outline"></span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $chapter->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $chapter->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                                        {{ $chapter->posts->count() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.bookChapters.posts', $chapter->id) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition-colors">
                                            <span class="iconify mr-1" data-icon="mdi-text-box-multiple-outline"></span>
                                            Bài Viết
                                        </a>
                                        <a href="{{ route('bookChapters.show', $chapter->slug) }}" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors" target="_blank">
                                            <span class="iconify mr-1" data-icon="mdi-eye"></span>
                                            Xem
                                        </a>
                                        <a href="{{ route('admin.bookChapters.edit', $chapter->id) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
                                            <span class="iconify mr-1" data-icon="mdi-pencil"></span>
                                            Sửa
                                        </a>
                                        <form action="{{ route('admin.bookChapters.destroy', $chapter->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none transition-colors" onclick="return confirm('Bạn có chắc muốn xoá?')">
                                                <span class="iconify mr-1" data-icon="mdi-trash-can-outline"></span>
                                                Xoá
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="iconify text-4xl text-gray-400 mb-3" data-icon="mdi-bookmark-multiple-outline"></span>
                                    <h4 class="text-lg font-medium text-gray-600 mb-1">Chưa Có Chương Nào</h4>
                                    <p class="text-gray-500 mb-4">Hãy thêm chương đầu tiên cho sách này</p>
                                    <a href="{{ route('admin.books.createChapter', $book->id) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary py-2 px-4 text-center font-medium text-white hover:bg-primary/90 transition-all duration-200 shadow-sm">
                                        <span class="iconify" data-icon="mdi-plus"></span>
                                        Thêm Chương Mới
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fixed bottom-6 right-6 z-50">
        <a href="{{ route('admin.books.createChapter', $book->id) }}" class="flex items-center justify-center w-14 h-14 rounded-full bg-primary text-white shadow-lg hover:bg-primary/90 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2">
            <span class="iconify text-2xl" data-icon="mdi-plus"></span>
        </a>
    </div>

@endsection

@push('styles')
    <style>
        .animate-fadeIn {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@endpush
