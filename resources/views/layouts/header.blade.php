<nav class="sticky top-0 shadow-md header-container text-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ url('/') }}" class="text-lg font-semibold text-decoration-none">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>
            </div>

            <!-- Right Side Of Navbar -->
            <div class="flex items-center">
                <div class="hidden space-x-4 sm:-my-px sm:ml-10 sm:flex sm:items-center">
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

                        <!-- Check if the user is an admin and display the admin button -->
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-x-1 text-decoration-none text-white py-1 px-3 rounded-lg">
                                <span class="iconify" data-icon="mdi-view-dashboard"></span>
                                {{ __('Admin Dashboard') }}
                            </a>
                        @endif
                    @endguest
                </div>
            </div>
        </div>
    </div>
</nav>