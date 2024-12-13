@extends('admin_layouts.admin')

@section('content')
    <div>
        @if (session('success'))
            @include('layouts.message-success', ['message' => session('success')])
        @endif

        <h1 class="text-3xl font-bold mb-4">Tin tức</h1>

        <div class="mb-6 flex justify-end">
            <div class="max-w-xl w-full">
                <x-search-bar
                    model="Article"
                    route-name="admin.articles"
                    :search-fields="['title', 'content']"
                    placeholder="Nhập tên bài viết..."
                    :is-admin="true"
                />
            </div>
        </div>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Tên bài viết</th>
                <th>Ngày tạo</th>
                <th>Ngày cập nhật cuối</th>
                <th>Hành động</th>
            </tr>
            </thead>
            <tbody>
            @forelse($articles as $article)
                <tr>
                    <td>{{ $article->title }}</td>
                    <td>{{ date('d/m/Y H:i', strtotime($article->created_at)) }}</td>
                    <td>{{ date('d/m/Y H:i', strtotime($article->updated_at)) }}</td>
                    <td>
                        <a href="{{ route('admin.articles.edit', $article->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                        <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xoá?')">Xoá</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Hiện chưa có bài viết nào</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        {{ $articles->links() }}
    </div>

    @include('layouts.floating-button-right', ['link' => route('admin.articles.create')])
@endsection
