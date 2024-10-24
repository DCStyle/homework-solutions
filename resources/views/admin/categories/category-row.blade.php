<div class="grid grid-cols-12 border-t border-[#EEEEEE] px-5 py-4 dark:border-strokedark lg:px-7.5 2xl:px-11">
    <div class="col-span-3">
        <!-- Indent children categories -->
        <p class="text-[#637381]" style="padding-left: {{ $level * 20 }}px;">
            {{ $category->name }}
        </p>
    </div>
    <div class="col-span-6">
        <p class="text-[#637381]">
            @include('layouts.string-snippet', ['string' => $category->description, 'snippet' => 100])
        </p>
    </div>
    <div class="col-span-2">
        <p class="text-[#637381]">{{ $category->slug }}</p>
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
                <a class="dropdown-item text-decoration-none" href="{{ route('admin.categories.edit', $category->slug) }}">
                    {{ __('Edit') }}
                </a>

                <a class="dropdown-item text-decoration-none" href="{{ route('admin.categories.delete', $category->slug) }}">
                    {{ __('Delete') }}
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Recursively call the partial to display child categories, if any -->
@if ($category->children()->count())
    @foreach ($category->children as $child)
        @include('admin.categories.partials.category-row', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif
