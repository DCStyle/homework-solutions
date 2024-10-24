<div>
    <label for="{{ $name }}" class="mb-3 block text-sm font-medium text-[#1c2434]">{{ $label }}</label>
    <input type="text" name="{{ $name }}" id="{{ $name }}" value="{{ $value  }}" {{ $required ? 'required' : '' }}
           class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent px-3 py-3 font-normal text-[#1c2434] outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
    />
</div>
