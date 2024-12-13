<nav class="sticky top-0 shadow-md header-container text-white z-10">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ url('/') }}" class="text-lg font-semibold text-decoration-none">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="hidden ml-6 items-center gap-3 xl:flex">
                    @foreach($categories as $category)
                        @if($loop->index < 12)
                            <a href="{{ route('categories.show', $category->slug) }}" class="text-md border-b-2 border-transparent hover:border-white">
                                {{ $category->name }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Right Side Of Navbar -->
            <div class="flex items-center">
                <div class="hidden items-center space-x-4 xl:flex">
                    @guest
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="flex items-center gap-x-1 text-decoration-none">
                                <span class="iconify" data-icon="mdi-login"></span>
                                {{ __('Login') }}
                            </a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 flex items-center gap-x-1 text-decoration-none">
                                <span class="iconify" data-icon="mdi-account-plus"></span>
                                {{ __('Register') }}
                            </a>
                        @endif
                    @else
                        <div class="relative dropdown">
                            <a id="navbarDropdown"
                               class="flex items-center gap-x-1 text-decoration-none focus:outline-none"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                               role="button" href="#"
                            >
                                <span class="iconify" data-icon="mdi-account"></span>
                                {{ Auth::user()->name }}

                                <span class="iconify" data-icon="mdi-menu-down"></span>
                            </a>

                            <div class="bg-white dropdown-menu dropdown-menu-end z-10" aria-labelledby="navbarDropdown">
                                @if(Auth::user()->isAdmin())
                                    <a class="dropdown-item text-decoration-none" href="{{ route('admin.dashboard') }}">
                                        {{ __('Admin Dashboard') }}
                                    </a>
                                @endif

                                <a class="dropdown-item text-decoration-none" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @endguest

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
