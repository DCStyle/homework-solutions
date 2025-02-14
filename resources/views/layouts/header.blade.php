<nav class="sticky top-0 shadow-md header-container text-white z-10">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ url('/') }}" class="text-lg font-semibold text-decoration-none">
                        @if(setting('site_logo'))
                            <img src="{{ asset(Storage::url(setting('site_logo'))) }}" alt="{{ setting('site_name') }}" class="h-8" />
                        @else
                            {{ setting('site_name', 'Homework Solutions') }}
                        @endif
                    </a>
                </div>

                <div class="hidden ml-6 items-center gap-3 xl:flex">
                    @foreach($categories as $category)
                        @if($loop->index < 12)
                            <a href="{{ route('categories.show', $category->slug) }}" class="text-lg border-b-2 border-transparent hover:border-white">
                                {{ $category->name }}
                            </a>
                        @endif
                    @endforeach

                    <a href="/congcu.html" class="text-lg border-b-2 border-transparent hover:border-white">
                        Công cụ
                    </a>
                </div>
            </div>

            <!-- Right Side Of Navbar -->
            <div class="flex items-center">
                <div class="hidden items-center space-x-4 xl:flex">
                    <div class="max-w-sm">
                        <button type="button"
                                class="flex items-center gap-x-1 text-decoration-none"
                                data-bs-toggle="modal"
                                data-bs-target="#search-modal">
                            <span class="iconify text-2xl" data-icon="mdi-magnify"></span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center xl:hidden">
                    <span class="iconify text-2xl text-white cursor-pointer" data-icon="mdi-view-grid" data-bs-toggle="modal" data-bs-target="#mobile-category-modal"></span>
                </div>
            </div>
        </div>
    </div>
</nav>
