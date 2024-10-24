<div>
    <label for="{{ $name }}" class="mb-3 block text-sm font-medium text-[#1c2434]">{{ $label }}</label>
    <textarea name="{{ $name }}" id="{{ $name }}" rows="4"
              class="w-full rounded-lg border-[1.5px] border-primary bg-transparent px-3 py-3 font-normal text-[#1c2434] outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter"
    >{{ $value  }}</textarea>
</div>
