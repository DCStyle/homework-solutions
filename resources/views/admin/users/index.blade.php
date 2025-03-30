@extends('admin_layouts.admin')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Page Header -->
        <div class="relative overflow-hidden rounded-xl bg-primary p-6 shadow-lg mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative z-10">
                <div>
                    <h1 class="text-3xl font-semibold text-white">Quản Lý Người Dùng</h1>
                    <p class="mt-1 text-sm text-white/90">Quản lý người dùng, vai trò và quyền hạn</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-white py-2.5 px-4 text-center font-medium text-primary hover:bg-gray-100 transition-all duration-200 shadow-sm">
                        <span class="iconify mr-2" data-icon="mdi-account-plus"></span>
                        Thêm Người Dùng
                    </a>
                </div>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 -mt-8 -mr-8 h-40 w-40 rounded-full bg-white/10"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 h-64 w-64 rounded-full bg-white/5"></div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 gap-5 mt-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Users -->
            <div class="p-5 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-primary-light text-primary">
                        <span class="iconify text-2xl" data-icon="mdi-account-group"></span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-2xl font-bold text-gray-800">{{ $users->total() }}</span>
                        <span class="text-sm text-gray-500">Tổng Người Dùng</span>
                    </div>
                </div>
            </div>

            <!-- Admin Users -->
            <div class="p-5 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-green-50 text-green-500">
                        <span class="iconify text-2xl" data-icon="mdi-shield-account"></span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-2xl font-bold text-gray-800">{{ $users->filter(function($user) { return $user->isAdmin(); })->count() }}</span>
                        <span class="text-sm text-gray-500">Quản Trị Viên</span>
                    </div>
                </div>
            </div>

            <!-- New Users -->
            <div class="p-5 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-blue-50 text-blue-500">
                        <span class="iconify text-2xl" data-icon="mdi-account-plus"></span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-2xl font-bold text-gray-800">{{ $users->where('created_at', '>=', now()->subDays(30))->count() }}</span>
                        <span class="text-sm text-gray-500">Mới Trong Tháng</span>
                    </div>
                </div>
            </div>

            <!-- User Types Distribution -->
            <div class="p-5 bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-full bg-purple-50 text-purple-500">
                        <span class="iconify text-2xl" data-icon="mdi-account-details"></span>
                    </div>
                    <div class="flex flex-col items-end">
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center space-x-1">
                                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                <span class="text-xs text-gray-500">Học Sinh</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-3 h-3 rounded-full bg-blue-400"></div>
                                <span class="text-xs text-gray-500">Giáo Viên</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                <span class="text-xs text-gray-500">Phụ Huynh</span>
                            </div>
                        </div>
                        <span class="text-sm text-gray-500 mt-1">Loại Người Dùng</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="my-6">
            <div class="p-6 bg-white rounded-lg shadow-sm">
                <form method="GET" action="{{ route('admin.users.index') }}" class="grid gap-4 md:grid-cols-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="iconify text-gray-400" data-icon="mdi-magnify"></span>
                            </div>
                            <input
                                type="text"
                                id="search"
                                name="search"
                                class="focus:ring-primary focus:border-primary block w-full pl-10 sm:text-sm border-gray-300 rounded-md"
                                placeholder="Tìm người dùng..."
                                value="{{ request('search') }}"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Vai trò</label>
                        <select
                            id="role"
                            name="role"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md"
                        >
                            <option value="">Tất cả vai trò</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="user_type" class="block text-sm font-medium text-gray-700 mb-1">Loại người dùng</label>
                        <select
                            id="user_type"
                            name="user_type"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md"
                        >
                            <option value="">Tất cả loại</option>
                            <option value="student" {{ request('user_type') == 'student' ? 'selected' : '' }}>Học Sinh</option>
                            <option value="teacher" {{ request('user_type') == 'teacher' ? 'selected' : '' }}>Giáo Viên</option>
                            <option value="parent" {{ request('user_type') == 'parent' ? 'selected' : '' }}>Phụ Huynh</option>
                        </select>
                    </div>

                    <div class="self-end">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition duration-150">
                            <span class="iconify mr-2" data-icon="mdi-filter-variant"></span>
                            Áp Dụng Bộ Lọc
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col md:flex-row justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Người Dùng ({{ $users->total() }})</h3>
                    <div class="mt-2 md:mt-0 flex items-center text-sm text-gray-500">
                        <span>Hiển thị {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} của {{ $users->total() }} người dùng</span>
                    </div>
                </div>
            </div>

            <!-- Table Content -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('admin.users.index', ['sort' => 'id', 'direction' => request('sort') == 'id' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction'])) }}" class="flex items-center group">
                                <span>ID</span>
                                <span class="iconify ml-1 text-gray-400 opacity-0 group-hover:opacity-100 {{ request('sort') == 'id' ? 'opacity-100' : '' }}" data-icon="{{ request('direction') == 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}"></span>
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Người Dùng
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('admin.users.index', ['sort' => 'email', 'direction' => request('sort') == 'email' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction'])) }}" class="flex items-center group">
                                <span>Email</span>
                                <span class="iconify ml-1 text-gray-400 opacity-0 group-hover:opacity-100 {{ request('sort') == 'email' ? 'opacity-100' : '' }}" data-icon="{{ request('direction') == 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}"></span>
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vai Trò & Loại
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <a href="{{ route('admin.users.index', ['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'] + request()->except(['sort', 'direction'])) }}" class="flex items-center group">
                                <span>Tham Gia</span>
                                <span class="iconify ml-1 text-gray-400 opacity-0 group-hover:opacity-100 {{ request('sort') == 'created_at' || !request('sort') ? 'opacity-100' : '' }}" data-icon="{{ (request('direction') == 'asc' || !request('direction')) ? 'mdi-arrow-up' : 'mdi-arrow-down' }}"></span>
                            </a>
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Thao Tác
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                #{{ $user->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        @if($user->avatar)
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/avatars/' . $user->avatar) }}" alt="{{ $user->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-primary to-primary-light flex items-center justify-center text-white font-bold text-lg">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-sm font-medium text-gray-900 hover:text-primary">{{ $user->name }}</a>
                                        @if($user->email_verified_at)
                                            <div class="text-xs text-green-500 flex items-center mt-0.5">
                                                <span class="iconify mr-1" data-icon="mdi-check-circle"></span>
                                                Đã xác thực
                                            </div>
                                        @else
                                            <div class="text-xs text-amber-500 flex items-center mt-0.5">
                                                <span class="iconify mr-1" data-icon="mdi-alert-circle-outline"></span>
                                                Chưa xác thực
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a href="mailto:{{ $user->email }}" class="hover:text-primary">{{ $user->email }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-light text-primary">
                                            {{ $role->name }}
                                        </span>
                                        @endforeach

                                        @if($user->roles->isEmpty())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Người Dùng Cơ Bản
                                        </span>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $user->user_type == 'student' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $user->user_type == 'teacher' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $user->user_type == 'parent' ? 'bg-amber-100 text-amber-800' : '' }}
                                ">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1
                                        {{ $user->user_type == 'student' ? 'bg-green-400' : '' }}
                                        {{ $user->user_type == 'teacher' ? 'bg-blue-400' : '' }}
                                        {{ $user->user_type == 'parent' ? 'bg-amber-400' : '' }}
                                    "></span>
                                    {{ $user->user_type == 'student' ? 'Học Sinh' : '' }}
                                        {{ $user->user_type == 'teacher' ? 'Giáo Viên' : '' }}
                                        {{ $user->user_type == 'parent' ? 'Phụ Huynh' : '' }}
                                </span>
                                </div>

                                @if($adminRole = $roles->where('name', 'Administrator')->first())
                                    @if(!($user->id === auth()->id() && $user->isAdmin()))
                                        <form action="{{ route('admin.users.toggle_admin', $user) }}" method="POST" class="mt-2">
                                            @csrf
                                            @method('PATCH')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded {{ $user->isAdmin() ? 'text-rose-700 hover:bg-rose-100' : 'text-emerald-700 hover:bg-emerald-100' }} transition-colors"
                                                title="{{ $user->isAdmin() ? 'Hủy quyền quản trị' : 'Cấp quyền quản trị' }}"
                                            >
                                                <span class="iconify mr-1" data-icon="{{ $user->isAdmin() ? 'mdi-shield-off' : 'mdi-shield' }}"></span>
                                                {{ $user->isAdmin() ? 'Hủy Quyền Admin' : 'Cấp Quyền Admin' }}
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex flex-col">
                                    <span>{{ $user->created_at->format('d/m/Y') }}</span>
                                    <span class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-primary hover:text-primary-dark transition-colors" title="Xem chi tiết">
                                        <span class="iconify text-xl" data-icon="mdi-eye"></span>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-emerald-600 hover:text-emerald-700 transition-colors" title="Chỉnh sửa">
                                        <span class="iconify text-xl" data-icon="mdi-pencil"></span>
                                    </a>
                                    @if(auth()->id() !== $user->id)
                                        <button
                                            type="button"
                                            class="text-rose-600 hover:text-rose-700 transition-colors delete-btn"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            title="Xóa người dùng"
                                        >
                                            <span class="iconify text-xl" data-icon="mdi-delete"></span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <span class="iconify text-4xl text-gray-300 mb-3" data-icon="mdi-account-search"></span>
                                    <p class="text-lg font-medium text-gray-600 mb-1">Không tìm thấy người dùng</p>
                                    <p class="text-gray-400 max-w-md">Không có người dùng phù hợp với tiêu chí tìm kiếm. Hãy điều chỉnh các tham số tìm kiếm hoặc xóa bộ lọc để xem tất cả người dùng.</p>
                                    <a href="{{ route('admin.users.index') }}" class="mt-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                        <span class="iconify mr-2" data-icon="mdi-refresh"></span>
                                        Đặt Lại Bộ Lọc
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Delete Modal - Hidden by default -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <span class="iconify text-red-600 text-xl" data-icon="mdi-alert"></span>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Xóa Người Dùng
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Bạn có chắc chắn muốn xóa <span id="delete-user-name" class="font-medium text-gray-700"></span>? Hành động này không thể hoàn tác và tất cả dữ liệu liên quan đến người dùng này sẽ bị xóa vĩnh viễn.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form id="deleteForm" action="" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Xóa
                        </button>
                    </form>
                    <button type="button" id="cancelDelete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification System -->
    <div id="toast-container" class="fixed bottom-0 right-0 p-4 space-y-3 z-50"></div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Show toast notification
            function showToast(message, type = 'success') {
                const id = 'toast-' + Date.now();
                const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
                const icon = type === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle';

                const toast = `
            <div id="${id}" class="${bgColor} text-white rounded-lg shadow-lg p-4 mb-3 flex items-center transform transition-all duration-300 translate-x-full opacity-0">
                <span class="iconify mr-2 text-xl" data-icon="${icon}"></span>
                <div class="flex-1">${message}</div>
                <button class="ml-4 text-white focus:outline-none hover:text-gray-100 toast-close">
                    <span class="iconify" data-icon="mdi-close"></span>
                </button>
            </div>
        `;

                $('#toast-container').append(toast);

                // Animate in
                setTimeout(() => {
                    $(`#${id}`).removeClass('translate-x-full opacity-0');
                }, 10);

                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    dismissToast(id);
                }, 5000);

                // Close button event
                $(`#${id} .toast-close`).on('click', function() {
                    dismissToast(id);
                });
            }

            function dismissToast(id) {
                $(`#${id}`).addClass('translate-x-full opacity-0');
                setTimeout(() => {
                    $(`#${id}`).remove();
                }, 300);
            }

            // Session flash messages
            @if(session('success'))
            showToast("{{ session('success') }}", 'success');
            @endif

            @if(session('error'))
            showToast("{{ session('error') }}", 'error');
            @endif

            // Delete confirmation modal
            $('.delete-btn').on('click', function() {
                const userId = $(this).data('user-id');
                const userName = $(this).data('user-name');

                $('#delete-user-name').text(userName);
                $('#deleteForm').attr('action', "{{ route('admin.users.destroy', '') }}/" + userId);
                $('#deleteModal').removeClass('hidden');
            });

            // Hide delete confirmation modal
            $('#cancelDelete').on('click', function() {
                $('#deleteModal').addClass('hidden');
            });

            // Close modal when clicking background
            $('#deleteModal').on('click', function(e) {
                if ($(e.target).hasClass('fixed')) {
                    $('#deleteModal').addClass('hidden');
                }
            });

            // Submit filter form when select changes
            $('#role, #user_type').on('change', function() {
                $(this).closest('form').submit();
            });
        });
    </script>
@endpush
