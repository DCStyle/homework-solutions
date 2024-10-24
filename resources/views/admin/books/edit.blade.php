@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-6">Edit Book</h2>

        <form class="rounded-sm border bg-white shadow"  method="POST" action="{{ route('admin.books.update', $book->slug) }}">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'name', 'label' => __('Book name'), 'value' => $book->name, 'required' => true])

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => __('Book slug'), 'value' => $book->slug, 'required' => true])

                <!-- Description Field -->
                @include('layouts.form-textarea', ['name' => 'description', 'label' => __('Book description'), 'value' => $book->description, 'required' => true])

                <!-- Book Group Dropdown -->
                <div>
                    <label for="group_id" class="mb-3 block text-sm font-medium text-[#1c2434]">Book group</label>
                    <select name="group_id" id="group_id"
                            class="js-select2 relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 pl-5 pr-12 outline-none transition focus:border-primary active:border-primary"
                    >
                        <?php $currentCategory = null ?>

                        @foreach($groups as $group)
                            @if($currentCategory == null || $group->category->id !== $currentCategory->id)
                                <?php $currentCategory = $group->category ?>
                                <optgroup label="{{ $currentCategory->name }}">
                            @endif

                            <option value="{{ $group->id }}" {{ $group->id == $book->book_group_id ? 'selected' : '' }}>{{ $group->name }}</option>

                            @if($currentCategory == null || $group->category->id !== $currentCategory->id)
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ __('Update book') }}
                </button>
            </div>
        </form>
    </div>
@endsection
