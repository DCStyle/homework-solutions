@extends('admin_layouts.admin')

@section('content')
    <div>
        @if (session('success'))
            @include('layouts.message-success', ['message' => session('success')])
        @endif

        <h1 class="text-3xl font-bold mb-6">Danh mục tin tức</h1>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tên</th>
                    <th>Mô tả</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>@include('layouts.string-snippet', ['string' => $category->description, 'snippet' => 100])</td>
                        <td>
                            <a href="{{ route('admin.articleCategories.edit', $category->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                            <form action="{{ route('admin.articleCategories.destroy', $category->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xoá?')">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Hiện chưa có danh mục nào</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('layouts.floating-button-right', ['link' => route('admin.articleCategories.create')])
@endsection
