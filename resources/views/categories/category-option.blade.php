<option value="{{ $category->id }}" {{ $selected ? 'selected' : '' }}>
    {{ str_repeat('--', $level) }} {{ $category->name }}
</option>

@if ($category->children->count())
    @foreach ($category->children as $child)
        @include('partials.category-option', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif
