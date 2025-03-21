<!-- ===== Header Start ===== -->
<header class="sticky top-0 z-10 flex w-full bg-white drop-shadow-xl">
    <div class="flex flex-grow items-center justify-between px-4 py-4 shadow-2 md:px-6 2xl:px-11">
        <div class="flex items-center gap-2 sm:gap-4 lg:hidden">
            <a class="block  text-white font-medium flex-shrink-0 lg:hidden" href="{{ route('home')  }}">
                {{ __('Admin Dashboard') }}
            </a>
        </div>

        <div class="ml-auto flex items-center gap-3 2xsm:gap-7">
            @include('layouts.notifications')

            <!-- User Area -->
            <div class="relative dropdown">
                <a class="flex items-center gap-4"
                   data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                   role="button"
                   href="#"
                >
                    <span class="hidden text-right lg:block">
                        <span class="block text-sm font-medium text-black">{{ Auth::user()->name }}</span>
                        <span class="block text-xs font-medium text-gray-400">Administrator</span>
                    </span>

                    <span class="iconify text-xl" data-icon="mdi-chevron-down"></span>
                </a>

                <!-- Dropdown Start -->
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
                <!-- Dropdown End -->
            </div>
            <!-- User Area -->
        </div>
    </div>
</header>
<!-- ===== Header End ===== -->
