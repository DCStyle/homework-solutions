@extends('admin_layouts.admin')

@section('content')
    <div>
        @if (session('success'))
            @include('layouts.message-success', ['message' => session('success')])
        @endif

        <h1 class="text-3xl font-bold mb-4">Tags</h1>

        <div class="mb-6 flex justify-end">
            <div class="max-w-xl w-full">
                <x-search-bar
                    model="ArticleTag"
                    route-name="admin.article-tags"
                    :search-fields="['name']"
                    placeholder="Nhập từ khoá tìm kiếm..."
                    :is-admin="true"
                />
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Tên</th>
                <th>Đường dẫn</th>
                <th>Bài viết</th>
                <th>Hành động</th>
            </tr>
            </thead>
            <tbody>
            @forelse($tags as $tag)
                <tr>
                    <td>{{ $tag->name }}</td>
                    <td>{{ $tag->slug }}</td>
                    <td>{{ $tag->articles_count }}</td>
                    <td>
                        <a href="{{ route('admin.article-tags.edit', $tag->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form action="{{ route('admin.article-tags.destroy', $tag->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Xoá</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Hiện chưa có tag nào</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $tags->links() }}
    </div>

    @include('layouts.floating-button-right', ['link' => route('admin.article-tags.create')])
@endsection
