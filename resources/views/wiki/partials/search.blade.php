<div class="bg-white rounded-lg shadow mb-6 relative">
    <div class="px-4 py-5 sm:p-6">
        <form action="{{ route('wiki.search') }}" method="GET" class="search-form">
            <div class="relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input type="text" name="q" id="search" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-12 py-3 sm:text-sm border-gray-300 rounded-md" placeholder="Tìm kiếm câu hỏi..." autocomplete="off">
                <div class="absolute inset-y-0 right-0 flex py-1.5 pr-1.5">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Tìm
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-2 text-xs text-gray-500">
            <p>Gợi ý:
                <b><i>"Viết bài văn trình bày ý kiến về một vấn đề đời sống...",
                        "Cho hcn ABCD vẽ đường cao AH vuông góc với BD (H thuộc BD)..."</i></b>
            </p>
        </div>

        <!-- Live search results will be shown here -->
        <div id="search-results" class="absolute z-10 w-full bg-white rounded-lg shadow-lg mt-1 overflow-hidden hidden">
            <!-- Results will be populated here via JavaScript -->
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/css/public/wiki/live_search.css', 'resources/js/public/wiki/live_search.js'])
@endpush

