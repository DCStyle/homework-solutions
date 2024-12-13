<div class="modal fade" id="search-modal" tabindex="-1" aria-labelledby="search-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="search-modal-label">Tìm kiếm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Search Input -->
                <div class="modal-search-container mb-4">
                    <div class="relative">
                        <span class="iconify absolute left-3 !top-1/2 !-translate-y-1/2 text-gray-400"
                              data-icon="mdi-magnify"></span>
                        <input type="text"
                               class="modal-search-input w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Tìm kiếm..."
                               data-min-length="2"
                               data-is-admin="false">
                        <div class="loading-spinner absolute right-3 top-1/2 -translate-y-1/2 hidden">
                            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Search Results -->
                <div class="modal-search-results max-h-96 overflow-y-auto">
                    <div class="modal-results-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>
