<div class="modal fade" id="mobile-category-modal" aria-hidden="true" aria-labelledby="mobile-category-modal" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <div class="modal-body">
                <div class="grid grid-cols-2 gap-4">
                    @foreach($categories as $category)
                        @if($loop->index < 12)
                            <a href="{{ route('categories.show', $category->slug) }}" class="bg-white flex flex-col items-center justify-center py-4 px-2 rounded-3xl"
                               style="box-shadow: 0 2px 8px rgba(255, 131, 89, 0.08), 0 20px 32px rgba(255, 131, 89, 0.24)">

                                <img src="{{ asset('images/lop' . $loop->index + 1 . '.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="w-full h-auto mb-4" />

                                <span class="text-sm text-uppercase font-medium bg-gradient-to-r from-cyan-500 to-blue-500 text-white p-2 rounded-xl">
                                {{ __('Study now') }}
                            </span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
