@extends('admin_layouts.admin')

@section('content')
    <div>
        <h1 class="text-3xl font-bold mb-6">Create New Category</h1>

        <form class="rounded-sm border bg-white shadow" method="POST" action="{{ route('admin.categories.store') }}">
            @csrf

            <div class="flex flex-col gap-4 p-6">
                <!-- Category Name -->
                @include('layouts.form-input', ['name' => 'name', 'label' => 'Category Name', 'value' => '', 'required' => true])

                <!-- Category Description -->
                @include('layouts.form-textarea', ['name' => 'description', 'label' => 'Category Description', 'value' => '', 'required' => true])

                <!-- Parent Category Dropdown -->
                <div>
                    <label for="parent_id" class="mb-3 block text-sm font-medium text-[#1c2434]">Parent Category</label>
                    <select name="parent_id" id="parent_id"
                            class="js-select2 relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                    >
                        <option value="">No Parent (Top-Level Category)</option>
                        @foreach ($categories as $category)
                            @include('categories.category-option', ['category' => $category, 'selected' => false, 'level' => 0])
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="flex w-full justify-center rounded bg-primary p-3 font-medium text-white hover:bg-opacity-90">
                    Create Category
                </button>
            </div>
        </form>
    </div>
@endsection
