<div class="space-y-6">
    <!-- Categories -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
        <div class="p-4 bg-gradient-to-r from-gray-50 to-indigo-50 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h2 class="text-gray-800 font-semibold flex items-center">
                    <span class="iconify mr-2 text-indigo-500" data-icon="mdi-shape-outline" data-width="20"></span>
                    Danh mục
                </h2>
                <span class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded-full">
                    {{ count($categories) }}
                </span>
            </div>
        </div>
        <div class="p-3">
            @if(count($categories) > 0)
                <ul class="divide-y divide-gray-50">
                    @foreach($categories as $category)
                        <li>
                            <a href="{{ route('wiki.feed.category', $category->slug) }}" class="flex items-center px-2 py-2.5 rounded-lg hover:bg-gray-50 group transition-colors">
                                <span class="iconify text-gray-400 group-hover:text-indigo-500 mr-2.5 transition-colors" data-icon="mdi-folder-outline" data-width="18"></span>
                                <span class="text-gray-700 group-hover:text-indigo-600 text-sm transition-colors">{{ $category->name }}</span>
                                @if(isset($category->count) && $category->count > 0)
                                    <span class="ml-auto inline-flex items-center justify-center h-5 min-w-[20px] px-1.5 rounded-full bg-gray-100 text-gray-600 text-xs">
                                        {{ $category->count }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="py-4 text-center">
                    <span class="iconify mx-auto block mb-2 text-gray-300" data-icon="mdi-folder-outline" data-width="28"></span>
                    <p class="text-sm text-gray-500">Chưa có danh mục nào.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Book Groups -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
        <div class="p-4 bg-gradient-to-r from-gray-50 to-blue-50 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h2 class="text-gray-800 font-semibold flex items-center">
                    <span class="iconify mr-2 text-blue-500" data-icon="mdi-book-multiple-outline" data-width="20"></span>
                    Bộ sách
                </h2>
                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                    {{ count($bookGroups) }}
                </span>
            </div>
        </div>
        <div class="p-3">
            @if(count($bookGroups) > 0)
                <ul class="divide-y divide-gray-50 max-h-[250px] overflow-y-auto">
                    @foreach($bookGroups as $bookGroup)
                        <li>
                            <a href="{{ route('wiki.feed.bookGroup', [$bookGroup->category->slug, $bookGroup->slug]) }}" class="flex items-center px-2 py-2.5 rounded-lg hover:bg-gray-50 group transition-colors">
                                <span class="iconify text-gray-400 group-hover:text-blue-500 mr-2.5 transition-colors" data-icon="mdi-book-outline" data-width="18"></span>
                                <span class="text-gray-700 group-hover:text-blue-600 text-sm transition-colors">{{ $bookGroup->name }}</span>
                                @if(isset($bookGroup->count) && $bookGroup->count > 0)
                                    <span class="ml-auto inline-flex items-center justify-center h-5 min-w-[20px] px-1.5 rounded-full bg-gray-100 text-gray-600 text-xs">
                                        {{ $bookGroup->count }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="py-4 text-center">
                    <span class="iconify mx-auto block mb-2 text-gray-300" data-icon="mdi-book-multiple-outline" data-width="28"></span>
                    <p class="text-sm text-gray-500">Chưa có bộ sách nào.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Stats Card -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <h2 class="text-gray-800 font-semibold flex items-center">
                <span class="iconify mr-2 text-purple-500" data-icon="mdi-chart-bar" data-width="20"></span>
                Thống kê
            </h2>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <span class="block text-lg font-semibold text-indigo-600">{{ count($categories) }}</span>
                    <span class="text-xs text-gray-500">Danh mục</span>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <span class="block text-lg font-semibold text-blue-600">{{ count($bookGroups) }}</span>
                    <span class="text-xs text-gray-500">Bộ sách</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Ask Question Button -->
    <div>
        <a href="{{ route('wiki.questions.create') }}"
           class="w-full flex items-center justify-center px-4 py-3 rounded-xl text-sm font-medium text-white
                  bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700
                  shadow-sm hover:shadow transition-all duration-200">
            <span class="iconify mr-2" data-icon="mdi-plus-circle" data-width="20"></span>
            Đặt câu hỏi mới
        </a>
    </div>

    <!-- Help Card -->
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl p-4 border border-indigo-100">
        <div class="flex items-start">
            <span class="iconify mr-3 text-indigo-500 flex-shrink-0" data-icon="mdi-help-circle-outline" data-width="24"></span>
            <div>
                <h3 class="text-sm font-medium text-gray-800 mb-1">Cần trợ giúp?</h3>
                <p class="text-xs text-gray-600 mb-2">Không tìm thấy câu trả lời cho thắc mắc của bạn?</p>
                <a href="{{ route('wiki.questions.create') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 inline-flex items-center">
                    Liên hệ với chúng tôi
                    <span class="iconify ml-1" data-icon="mdi-arrow-right" data-width="14"></span>
                </a>
            </div>
        </div>
    </div>
</div>
