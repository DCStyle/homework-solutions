<!-- ===== Header Start ===== -->
<header class="sticky top-0 z-10 flex w-full bg-white drop-shadow-xl">
    <div class="flex flex-grow items-center justify-between px-4 py-4 shadow-2 md:px-6 2xl:px-11">
        <div class="flex items-center gap-2 sm:gap-4 lg:hidden">
            <a class="block text-white font-medium flex-shrink-0 lg:hidden" href="{{ route('home') }}">
                {{ __('Admin Dashboard') }}
            </a>
        </div>

        <div class="ml-auto flex items-center gap-3 2xsm:gap-7">
            <!-- User Area -->
            <div class="relative" id="userDropdown">
                <a class="flex items-center gap-4 cursor-pointer" id="userDropdownToggle">
                    <span class="hidden text-right lg:block">
                        <span class="block text-sm font-medium text-black">{{ Auth::user()->name }}</span>
                        <span class="block text-xs font-medium text-gray-400">Administrator</span>
                    </span>

                    <span class="iconify text-xl" data-icon="mdi-chevron-down"></span>
                </a>

                <!-- Dropdown Menu - Hidden by default -->
                <div class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden" id="userDropdownMenu">
                    <div class="py-1">
                        <a class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                           href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
            <!-- User Area End -->
        </div>
    </div>
</header>
<!-- ===== Header End ===== -->

@push('scripts')
    <script>
        $(document).ready(function() {
            // Toggle dropdown menu
            $('#userDropdownToggle').on('click', function(e) {
                e.preventDefault();
                $('#userDropdownMenu').toggleClass('hidden');
            });

            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#userDropdown').length) {
                    $('#userDropdownMenu').addClass('hidden');
                }
            });
        });
    </script>
@endpush
