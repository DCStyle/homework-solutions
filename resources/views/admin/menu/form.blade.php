@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-4">
            {{ isset($menuItem) ? 'Cập nhật Menu Item' : 'Tạo Menu Item Mới' }}
        </h2>

        <form class="rounded-sm border bg-white shadow"
              method="POST"
              action="{{ isset($menuItem) ? route('admin.menu.update', $menuItem) : route('admin.menu.store') }}"
        >
            @csrf
            @if(isset($menuItem))
                @method('PUT')
            @endif

            <div class="flex flex-col gap-4 p-6">
                <!-- Name Field -->
                @include('layouts.form-input', [
                    'name' => 'name',
                    'label' => 'Tên hiển thị',
                    'value' => old('name', $menuItem->name ?? ''),
                    'required' => true
                ])

                <!-- Type Field -->
                <div>
                    <label for="type" class="mb-3 block text-sm font-medium text-[#1c2434]">Loại</label>
                    <select name="type" id="type"
                            class="relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary">
                        @foreach(['link' => 'Link', 'dropdown' => 'Dropdown', 'category' => 'Category'] as $value => $label)
                            <option value="{{ $value }}"
                                {{ (old('type', $menuItem->type ?? 'link') == $value) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dynamic Fields Container -->
                <div id="dynamic-fields">
                    <!-- URL Field -->
                    <div id="url-field" class="field-group">
                        @include('layouts.form-input', [
                            'name' => 'url',
                            'label' => 'URL (để trống nếu là dropdown)',
                            'value' => old('url', $menuItem->url ?? ''),
                        ])
                    </div>

                    <!-- Category Field -->
                    <div id="category-field" class="field-group" style="display: none;">
                        <label for="category_id" class="mb-3 block text-sm font-medium text-[#1c2434]">Chọn danh mục</label>
                        <select name="category_id" id="category_id"
                                class="relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary">
                            <option value="">Chọn danh mục</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (old('category_id', $menuItem->category_id ?? '') == $category->id) ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Icon Field -->
                @include('layouts.form-input', [
                    'name' => 'icon',
                    'label' => 'Icon (Iconify icon name)',
                    'value' => old('icon', $menuItem->icon ?? ''),
                ])

                <!-- Parent Menu Field -->
                <div>
                    <label for="parent_id" class="mb-3 block text-sm font-medium text-[#1c2434]">Menu cha</label>
                    <select name="parent_id" id="parent_id"
                            class="relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary">
                        <option value="">Không có</option>
                        @foreach($parentItems as $parent)
                            @if(!isset($menuItem) || $parent->id !== $menuItem->id)
                                <option value="{{ $parent->id }}"
                                    {{ (old('parent_id', $menuItem->parent_id ?? '') == $parent->id) ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                                @foreach($parent->children as $child)
                                    @if(!isset($menuItem) || $child->id !== $menuItem->id)
                                        <option value="{{ $child->id }}"
                                            {{ (old('parent_id', $menuItem->parent_id ?? '') == $child->id) ? 'selected' : '' }}>
                                            -- {{ $child->name }}
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Order Field -->
                @include('layouts.form-input', [
                    'name' => 'order',
                    'label' => 'Thứ tự hiển thị',
                    'type' => 'number',
                    'value' => old('order', $menuItem->order ?? 0),
                ])

                <!-- Active Status -->
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox"
                               name="active"
                               value="1"
                               {{ old('active', $menuItem->active ?? true) ? 'checked' : '' }}
                               class="form-checkbox h-5 w-5 text-indigo-600">
                        <span class="ml-2">Hiện</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ isset($menuItem) ? 'Cập nhật' : 'Thêm' }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const typeSelect = document.getElementById('type');
                const urlField = document.getElementById('url-field');
                const categoryField = document.getElementById('category-field');

                function toggleFields() {
                    const selectedType = typeSelect.value;
                    const fields = document.querySelectorAll('.field-group');

                    // Hide all fields first
                    fields.forEach(field => field.style.display = 'none');

                    // Show relevant fields based on type
                    if (selectedType === 'category') {
                        categoryField.style.display = 'block';
                    } else {
                        urlField.style.display = 'block';
                    }

                    // Clear irrelevant fields
                    if (selectedType === 'category') {
                        document.querySelector('[name="url"]').value = '';
                    } else {
                        document.querySelector('[name="category_id"]').value = '';
                    }
                }

                typeSelect.addEventListener('change', toggleFields);
                // Initialize on page load
                toggleFields();
            });
        </script>
    @endpush
@endsection
