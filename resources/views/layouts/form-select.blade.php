<div>
    <label for="{{ $name }}" class="mb-3 block text-sm font-medium text-[#1c2434]">{{ $label }}</label>
    <select name="{{ $name }}" id="{{ $name }}"
            class="relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
    >
        @if($allowNoSelection)
            <option value="">{{ $noSelectionLabel }}</option>
        @endif

        @foreach($selections as $selection)
            <option value="{{ $selection->{$selectionValueField} }}" {{ $selection->{$selectionValueField} == $value ? 'selected' : '' }}>
                {{ $selection->{$selectionLabelField} }}
            </option>
        @endforeach
    </select>
</div>
