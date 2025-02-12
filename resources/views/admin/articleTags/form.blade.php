@extends('admin_layouts.admin')

@section('content')
    <div>
        <h2 class="text-3xl font-bold mb-2">
            {{ isset($tag) ? 'Sửa tag' : 'Thêm tag' }}
        </h2>

        <form
            class="rounded-sm border bg-white shadow"
            method="POST"
            action="{{ isset($tag)
                ? route('admin.article-tags.update', $tag->id)
                : route('admin.article-tags.store') }}"
        >
            @csrf
            @if(isset($tag))
                @method('PUT')
            @endif

            <div class="flex flex-col gap-4 p-6">
                <!-- Name Field -->
                @include('layouts.form-input', [
                    'name' => 'name',
                    'label' => 'Tên',
                    'value' => old('name', $tag->name ?? ''),
                    'required' => true
                ])

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md shadow-sm hover:bg-indigo-700 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none focus:ring-2">
                    {{ isset($tag) ? 'Cập nhật' : 'Thêm mới' }}
                </button>
            </div>
        </form>
    </div>
@endsection
