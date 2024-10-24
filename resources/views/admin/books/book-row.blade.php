<div class="grid grid-cols-12 border-t border-[#EEEEEE] px-5 py-4 lg:px-7.5 2xl:px-11">
    <div class="col-span-3">
        <p class="text-[#637381]">
            {{ $book->name }}
        </p>

        <a class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm text-black border mt-2 hover:bg-gray-100"
           href="{{ route('admin.books.chapters', $book->slug) }}"
        >
            {{ __('Chapters') }}
            [{{ $book->chapters->count() }}]
            <span class="iconify text-xl" data-icon="mdi-format-list-bulleted"></span>
        </a>
    </div>
    <div class="col-span-4">
        <p class="text-[#637381]">
            @include('layouts.string-snippet', ['string' => $book->description, 'snippet' => 100])
        </p>
    </div>
    <div class="col-span-2">
        <p class="text-[#637381]">
            {{ date('d/m H:i', strtotime($book->created_at)) }}
        </p>
    </div>
    <div class="col-span-2">
        <p class="text-[#637381]">
            {{ date('d/m H:i', strtotime($book->updated_at)) }}
        </p>
    </div>
    <div class="col-span-1">
        <div class="relative dropdown">
            <button class="inline-flex items-center gap-2 rounded-md px-3 py-2 text-sm text-black border hover:bg-gray-100"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            >
                {{ __('Actions') }}
                <span class="iconify text-xl" data-icon="mdi-chevron-down"></span>
            </button>

            <div class="bg-white dropdown-menu dropdown-menu-end z-10" aria-labelledby="navbarDropdown">
                <a class="dropdown-item text-decoration-none" href="{{ route('admin.books.edit', $book->slug) }}">
                    {{ __('Edit') }}
                </a>

                <a class="dropdown-item text-decoration-none" href="{{ route('admin.books.delete', $book->slug) }}">
                    {{ __('Delete') }}
                </a>
            </div>
        </div>
    </div>
</div>
