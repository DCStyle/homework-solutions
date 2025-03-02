<div class="bg-white py-10 dark:bg-dark-primary">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 justify-items-center">
            @foreach(\App\Models\FooterColumn::with('links')->where('is_active', true)->orderBy('position')->get() as $column)
                <div class="text-center w-full max-w-xs">
                    <h4 class="text-purple-600 font-semibold mb-4">{{ $column->title }}</h4>
                    <ul class="space-y-2">
                        @foreach($column->links->where('is_active', true) as $link)
                            <li>
                                <a href="{{ $link->url }}" class="hover:text-purple-600">
                                    {{ $link->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</div> 