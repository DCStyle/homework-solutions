@extends('layouts.app')

@section('title', 'Kết quả tìm kiếm: ' . $query)
@section('description', 'Kết quả tìm kiếm cho từ khóa: ' . $query)

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <!-- Left Sidebar: Categories & Filters -->
            <div class="md:col-span-3">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50">
                        <h2 class="text-base font-medium text-gray-900">Lọc kết quả</h2>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                        <form action="{{ route('wiki.search') }}" method="GET">
                            <input type="hidden" name="q" value="{{ $query }}">
                            
                            <!-- Category Filter -->
                            <div class="mb-4">
                                <label for="category_id" class="block text-sm font-medium text-gray-700">Danh mục</label>
                                <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Tất cả danh mục</option>
                                    @foreach(App\Models\Category::all() as $category)
                                        <option value="{{ $category->id }}" @if(request('category_id') == $category->id) selected @endif>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Book Group Filter -->
                            <div class="mb-4">
                                <label for="book_group_id" class="block text-sm font-medium text-gray-700">Bộ sách</label>
                                <select id="book_group_id" name="book_group_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Tất cả bộ sách</option>
                                    @foreach(App\Models\BookGroup::all() as $bookGroup)
                                        <option value="{{ $bookGroup->id }}" @if(request('book_group_id') == $bookGroup->id) selected @endif>
                                            {{ $bookGroup->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Sort Filter -->
                            <div class="mb-4">
                                <label for="sort" class="block text-sm font-medium text-gray-700">Sắp xếp theo</label>
                                <select id="sort" name="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="relevance" @if(request('sort', 'relevance') == 'relevance') selected @endif>Độ liên quan</option>
                                    <option value="date_desc" @if(request('sort') == 'date_desc') selected @endif>Mới nhất</option>
                                    <option value="date_asc" @if(request('sort') == 'date_asc') selected @endif>Cũ nhất</option>
                                    <option value="views_desc" @if(request('sort') == 'views_desc') selected @endif>Xem nhiều nhất</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Áp dụng bộ lọc
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Ask Question Button -->
                <div class="mt-4">
                    <a href="{{ route('wiki.questions.create') }}" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Đặt câu hỏi mới
                    </a>
                </div>
            </div>
            
            <!-- Main Content: Search Results -->
            <div class="md:col-span-9">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h1 class="text-lg font-medium text-gray-900">
                            Kết quả tìm kiếm cho "{{ $query }}"
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Tìm thấy {{ $questions->total() }} kết quả
                        </p>
                    </div>
                    
                    <div class="divide-y divide-gray-200">
                        @forelse($questions as $question)
                            <div class="px-4 py-5 sm:p-6">
                                <div class="flex flex-col space-y-2">
                                    <div class="flex justify-between items-start">
                                        <h2 class="text-lg font-medium text-gray-900">
                                            <a href="{{ route('wiki.show', [$question->category->slug, $question->slug]) }}" class="hover:underline text-indigo-600">
                                                {{ $question->title }}
                                            </a>
                                        </h2>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 text-indigo-800">
                                            {{ $question->category->name }}
                                        </span>
                                    </div>
                                    
                                    <div class="text-sm text-gray-600">
                                        {!! Str::limit(strip_tags($question->content), 200) !!}
                                    </div>
                                    
                                    <div class="flex items-center text-xs text-gray-500 space-x-4">
                                        <span>Người hỏi: {{ $question->user->name }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $question->created_at->format('d/m/Y H:i') }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $question->views }} lượt xem</span>
                                        <span>&bull;</span>
                                        <span>{{ $question->comments->count() }} bình luận</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-5 sm:p-6 text-center">
                                <p class="text-gray-500">Không tìm thấy kết quả nào phù hợp.</p>
                                <p class="mt-2 text-sm text-gray-600">Hãy thử tìm kiếm với từ khóa khác hoặc <a href="{{ route('wiki.questions.create') }}" class="text-indigo-600 hover:text-indigo-900">đặt câu hỏi mới</a>.</p>
                            </div>
                        @endforelse
                    </div>
                    
                    <!-- Pagination -->
                    @if($questions->hasPages())
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
                            {{ $questions->appends(request()->except('page'))->links() }}
                        </div>
                    @endif
                </div>
                
                <!-- Related Searches -->
                <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h2 class="text-base font-medium text-gray-900">Tìm kiếm liên quan</h2>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('wiki.search', ['q' => $query . ' laravel']) }}" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                                {{ $query }} laravel
                            </a>
                            <a href="{{ route('wiki.search', ['q' => $query . ' php']) }}" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                                {{ $query }} php
                            </a>
                            <a href="{{ route('wiki.search', ['q' => $query . ' tutorial']) }}" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                                {{ $query }} tutorial
                            </a>
                            <a href="{{ route('wiki.search', ['q' => $query . ' example']) }}" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                                {{ $query }} example
                            </a>
                            <a href="{{ route('wiki.search', ['q' => 'how to ' . $query]) }}" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                                how to {{ $query }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 