@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-6">Edit Book Group</h2>

        <form class="rounded-sm border bg-white shadow"  method="POST" action="{{ route('admin.bookGroups.update', $group->slug) }}">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'name', 'label' => __('Book group name'), 'value' => $group->name, 'required' => true])

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => __('Book group slug'), 'value' => $group->slug, 'required' => true])

                <!-- Description Field -->
                @include('layouts.form-textarea', ['name' => 'description', 'label' => __('Book group description'), 'value' => $group->description, 'required' => true])

                <!-- Category Dropdown -->
                <div>
                    <label for="category_id" class="mb-3 block text-sm font-medium text-[#1c2434]">Book Group category</label>
                    <select name="category_id" id="category_id"
                            class="js-select2 relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                    >
                        @foreach ($categories as $categoryOption)
                            @include('categories.category-option', ['category' => $categoryOption, 'selected' => $group->category_id == $categoryOption->id, 'level' => 0])
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ __('Update book group') }}
                </button>
            </div>
        </form>
    </div>
@endsection
