<div class="sidebar-right-content w-[20%] h-screen flex-shrink-0 flex-grow-0 max-xl:hidden">
    <div class="sticky top-10 bg-white p-2">
        @guest
            <a href="{{ route('login') }}">
                <img src="{{ asset('images/avatar-default.jpg')  }}"
                     alt="{{ config('app.name') }}"
                     class="w-full rounded-xl"
                />
            </a>
        @else
            <a href="{{ route('home') }}">
                <img src="{{ asset('images/avatar-default.jpg')  }}"
                     alt="{{ config('app.name') }}"
                     class="w-full rounded-xl"
                />
            </a>
        @endguest
    </div>
</div>
