@extends('admin_layouts.admin')

@section('title', 'Quản Lý Tags')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header Section with Gradient Background -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h2 class="text-3xl font-bold text-white">Quản Lý Tags</h2>
                    <p class="mt-1 text-white/90">Tạo và quản lý các tags cho bài viết tin tức</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('admin.article-tags.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white py-2.5 px-4 text-center font-medium text-primary hover:bg-gray-100 transition-all duration-200 shadow-sm">
                        <span class="iconify" data-icon="mdi-plus"></span>
                        Thêm Tag Mới
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white/20 py-2.5 px-4 text-center font-medium text-white hover:bg-white/30 transition-all duration-200">
                        <span class="iconify" data-icon="mdi-arrow-left"></span>
                        Quay Lại Dashboard
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

        <!-- Search and Filters Section -->
        <div class="rounded-xl border border-stroke bg-white p-4 shadow-md mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h3 class="text-lg font-semibold text-black flex items-center">
                    <span class="iconify mr-2 text-primary" data-icon="mdi-magnify"></span>
                    Tìm Kiếm Tags
                </h3>
                <div class="w-full sm:max-w-md">
                    <x-search-bar
                        model="ArticleTag"
                        route-name="admin.article-tags"
                        :search-fields="['name']"
                        placeholder="Nhập từ khoá tìm kiếm..."
                        :is-admin="true"
                    />
                </div>
            </div>
        </div>

        <!-- Tags Table Card -->
        <div class="rounded-xl border border-stroke bg-white shadow-md overflow-hidden">
            <!-- Table with responsive design -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tên Tag
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Đường Dẫn
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
                    @forelse($tags as $tag)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-purple-100 text-purple-600">
                                        <span class="iconify" data-icon="mdi-tag"></span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $tag->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 font-mono">{{ $tag->slug }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                                    {{ $tag->articles_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.article-tags.edit', $tag->id) }}" class="inline-flex items-center px-2.5 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-colors">
                                        <span class="iconify mr-1" data-icon="mdi-pencil"></span>
                                        Sửa
                                    </a>
                                    <form action="{{ route('admin.article-tags.destroy', $tag->id) }}" method="POST" class="inline">
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
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="iconify text-4xl text-gray-400 mb-3" data-icon="mdi-tag-outline"></span>
                                    <h4 class="text-lg font-medium text-gray-600 mb-1">Chưa Có Tag Nào</h4>
                                    <p class="text-gray-500 mb-4">Hãy tạo tag đầu tiên của bạn</p>
                                    <a href="{{ route('admin.article-tags.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary py-2 px-4 text-center font-medium text-white hover:bg-primary/90 transition-all duration-200 shadow-sm">
                                        <span class="iconify" data-icon="mdi-plus"></span>
                                        Thêm Tag Mới
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if(count($tags) > 0)
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $tags->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="fixed bottom-6 right-6 z-50">
        <a href="{{ route('admin.article-tags.create') }}" class="flex items-center justify-center w-14 h-14 rounded-full bg-primary text-white shadow-lg hover:bg-primary/90 transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2">
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
