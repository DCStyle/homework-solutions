<div class="search-bar-container relative">
    <div class="relative">
        <input
            type="text"
            class="search-input w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="{{ $placeholder }}"
            data-model="{{ $model }}"
            data-route="{{ route('search') }}"
            data-route-name="{{ $routeName }}"
            data-search-fields="{{ json_encode($searchFields) }}"
            data-title-field="{{ $titleField }}"
            data-subtitle-field="{{ $subtitleField }}"
            data-min-length="{{ $minLength }}"
            data-limit="{{ $limit }}"
            data-is-admin="{{ $isAdmin ? 'true' : 'false' }}"
        >
        <!-- Loading spinner -->
        <div class="loading-spinner absolute right-3 top-2.5 hidden">
            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    <!-- Results dropdown -->
    <div class="search-results absolute z-50 w-full mt-1 bg-white rounded-lg shadow-lg border max-h-96 overflow-y-auto hidden"></div>
</div>
