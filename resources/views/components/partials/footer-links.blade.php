<div class="bg-white py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @php
            $activeColumns = \App\Models\FooterColumn::with('links')
                ->where('is_active', true)
                ->orderBy('position')
                ->get();

            $columnCount = $activeColumns->count();

            // Determine grid classes based on column count
            $gridClass = 'grid-cols-1 sm:grid-cols-2';

            if ($columnCount <= 1) {
                $gridClass = 'grid-cols-1'; // Always 1 column for 1 active column
            } elseif ($columnCount == 2) {
                $gridClass = 'grid-cols-1 sm:grid-cols-2'; // 1 on small, 2 on medium+
            } elseif ($columnCount == 3) {
                $gridClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3'; // 1, 2, 3
            } elseif ($columnCount == 4) {
                $gridClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4'; // 1, 2, 4
            } elseif ($columnCount <= 6) {
                $gridClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-' . $columnCount; // 1, 2, 3, actual count on xl
            } else {
                $gridClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-' . min(6, $columnCount); // 1, 2, 4, max 6 on xl
            }
        @endphp

        <div class="grid {{ $gridClass }} gap-8 justify-items-center">
            @foreach($activeColumns as $column)
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
