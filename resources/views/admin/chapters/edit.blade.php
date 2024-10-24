@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-2">
            Edit Book Chapter
        </h2>

        <p class="mb-6">
            @include('layouts.badge-primary', ['content' => $book->group->category->name])
            @include('layouts.badge-secondary', ['content' => $book->group->name])
            <span class="font-medium">{{ $book->name }}</span>
        </p>

        <form class="rounded-sm border bg-white shadow"  method="POST" action="{{ route('admin.bookChapters.update', $chapter->slug) }}">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-4 p-6">
                <!-- Title Field -->
                @include('layouts.form-input', ['name' => 'name', 'label' => __('Book chapter name'), 'value' => $chapter->name, 'required' => true])

                <!-- Slug Field -->
                @include('layouts.form-input', ['name' => 'slug', 'label' => __('Book chapter slug'), 'value' => $chapter->slug, 'required' => true])

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ __('Update book chapter') }}
                </button>
            </div>
        </form>
    </div>
@endsection
