@extends('admin_layouts.admin')

@section('title', 'Quản Lý Khóa API')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Quản Lý Khóa API</h1>
                <p class="text-gray-600 mt-1">Quản lý khóa API của nhà cung cấp AI để tạo nội dung</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.ai-dashboard.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-stroke py-2 px-4 text-center font-medium text-black hover:bg-gray-50 sm:px-6">
                    <span class="iconify mr-1" data-icon="mdi-arrow-left"></span>
                    Quay Lại Dashboard
                </a>
                <button class="inline-flex items-center justify-center gap-2.5 rounded-md bg-primary py-2 px-4 text-center font-medium text-white hover:bg-opacity-90 sm:px-6" data-toggle="modal" data-target="#createApiKeyModal">
                    <span class="iconify mr-1" data-icon="mdi-plus"></span>
                    Thêm Khóa API Mới
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert bg-green-100 border-green-500 text-green-700 transition-opacity duration-150 opacity-100 border-l-4 p-4 my-4 rounded" role="alert" data-auto-dismiss="5000">
                <div class="flex items-center justify-between">
                    <p>{{ session('success') }}</p>
                    <button type="button" class="text-gray-500 hover:text-gray-800" data-dismiss="alert">
                        <span class="iconify" data-icon="mdi-close"></span>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="alert bg-red-100 border-red-500 text-red-700 transition-opacity duration-150 opacity-100 border-l-4 p-4 my-4 rounded" role="alert" data-auto-dismiss="5000">
                <div class="flex items-center justify-between">
                    <p>{{ session('error') }}</p>
                    <button type="button" class="text-gray-500 hover:text-gray-800" data-dismiss="alert">
                        <span class="iconify" data-icon="mdi-close"></span>
                    </button>
                </div>
            </div>
        @endif

        <!-- API Keys Table -->
        @if(count($apiKeys) > 0)
            @foreach($apiKeys as $providerCode => $keys)
                <div class="rounded-lg border border-stroke bg-white p-6 shadow-md mb-6">
                    <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
                        @if($providerCode == 'google-gemini')
                            <span class="iconify mr-2 text-blue-500 text-2xl" data-icon="mdi-google"></span>
                        @elseif($providerCode == 'xai-grok')
                            <span class="iconify mr-2 text-green-500 text-2xl" data-icon="mdi-robot"></span>
                        @elseif($providerCode == 'openrouter')
                            <span class="iconify mr-2 text-purple-500 text-2xl" data-icon="mdi-web"></span>
                        @else
                            <span class="iconify mr-2 text-gray-500 text-2xl" data-icon="mdi-key"></span>
                        @endif
                        <h2 class="font-semibold text-gray-700">{{ $providers[$providerCode] ?? $providerCode }} ({{ count($keys) }})</h2>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                            <tr>
                                <th class="px-4 py-3">Khóa API</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Trạng Thái</th>
                                <th class="px-4 py-3">Lần Sử Dụng Cuối</th>
                                <th class="px-4 py-3 text-center">Thao Tác</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($keys as $key)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs relative group">
                                    <span class="api-key-masked">
                                        {{ substr($key->api_key, 0, 5) }}•••••••••••{{ substr($key->api_key, -4) }}
                                    </span>
                                        <span class="api-key-full hidden">{{ $key->api_key }}</span>
                                        <button type="button" class="toggle-key-visibility ml-2 text-gray-500 hover:text-gray-700">
                                            <span class="iconify" data-icon="mdi-eye"></span>
                                        </button>
                                        <button type="button" class="copy-key ml-1 text-gray-500 hover:text-gray-700" data-key="{{ $key->api_key }}">
                                            <span class="iconify" data-icon="mdi-content-copy"></span>
                                        </button>
                                    </td>
                                    <td class="px-4 py-3">{{ $key->email }}</td>
                                    <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-sm {{ $key->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        <span class="iconify mr-1" data-icon="{{ $key->is_active ? 'mdi-check-circle' : 'mdi-circle-outline' }}"></span>
                                        {{ $key->is_active ? 'Hoạt Động' : 'Không Hoạt Động' }}
                                    </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">
                                        @if($key->last_used_date)
                                            {{ $key->last_used_date->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-gray-400">Chưa sử dụng</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button type="button" class="text-blue-500 hover:bg-blue-100 p-2 rounded-full edit-api-key"
                                                    data-id="{{ $key->id }}"
                                                    data-provider="{{ $key->provider }}"
                                                    data-email="{{ $key->email }}"
                                                    data-key="{{ $key->api_key }}"
                                                    data-active="{{ $key->is_active }}">
                                                <span class="iconify" data-icon="mdi-pencil"></span>
                                            </button>

                                            <a href="{{ route('admin.ai_api_keys.test', $key->id) }}" class="text-amber-500 hover:bg-amber-100 p-2 rounded-full test-connection">
                                                <span class="iconify" data-icon="mdi-connection"></span>
                                            </a>

                                            <form action="{{ route('admin.ai_api_keys.toggle_active', $key->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-{{ $key->is_active ? 'gray' : 'green' }}-500 hover:bg-{{ $key->is_active ? 'gray' : 'green' }}-100 p-2 rounded-full">
                                                    <span class="iconify" data-icon="mdi-{{ $key->is_active ? 'ban' : 'check' }}"></span>
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.ai_api_keys.destroy', $key->id) }}" method="POST" class="inline-block delete-key-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:bg-red-100 p-2 rounded-full">
                                                    <span class="iconify" data-icon="mdi-delete"></span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @else
            <div class="rounded-lg border border-stroke bg-white p-6 shadow-md">
                <div class="rounded-lg bg-gray-50 p-8 text-center">
                    <span class="iconify text-4xl text-gray-400 mb-3" data-icon="mdi-key-outline"></span>
                    <h4 class="text-lg font-medium text-gray-600 mb-1">Không Tìm Thấy Khóa API</h4>
                    <p class="text-gray-500">Nhấp vào "Thêm Khóa API Mới" để thêm khóa API đầu tiên của bạn.</p>
                </div>
            </div>
        @endif

        <!-- Provider Information Cards -->
        <div class="mt-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Nhà Cung Cấp AI Được Hỗ Trợ</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Google Gemini Card -->
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-6">
                    <div class="flex items-center mb-4">
                        <span class="iconify text-blue-600 text-2xl mr-3" data-icon="mdi-google"></span>
                        <h3 class="text-lg font-semibold text-gray-800">Google Gemini</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Mô hình ngôn ngữ tiên tiến của Google có khả năng xử lý và tạo ra văn bản, mã nguồn, hình ảnh và nhiều hơn nữa.</p>
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi-check-circle"></span>
                        <span>Tạo văn bản</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-500 mb-4">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi-check-circle"></span>
                        <span>Khả năng xử lý hình ảnh</span>
                    </div>
                    <a href="https://ai.google.dev/" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-700">
                        <span class="iconify mr-1" data-icon="mdi-open-in-new"></span>
                        Lấy Khóa API
                    </a>
                </div>

                <!-- xAI Grok Card -->
                <div class="rounded-lg border border-green-200 bg-green-50 p-6">
                    <div class="flex items-center mb-4">
                        <span class="iconify text-green-600 text-2xl mr-3" data-icon="mdi-robot"></span>
                        <h3 class="text-lg font-semibold text-gray-800">xAI Grok</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Trợ lý AI đàm thoại của xAI với kiến thức và khả năng thời gian thực.</p>
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi-check-circle"></span>
                        <span>AI đàm thoại</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-500 mb-4">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi-check-circle"></span>
                        <span>Phân tích hình ảnh</span>
                    </div>
                    <a href="https://x.ai/" target="_blank" class="inline-flex items-center text-green-600 hover:text-green-700">
                        <span class="iconify mr-1" data-icon="mdi-open-in-new"></span>
                        Lấy Khóa API
                    </a>
                </div>

                <!-- OpenRouter Card -->
                <div class="rounded-lg border border-purple-200 bg-purple-50 p-6">
                    <div class="flex items-center mb-4">
                        <span class="iconify text-purple-600 text-2xl mr-3" data-icon="mdi-web"></span>
                        <h3 class="text-lg font-semibold text-gray-800">OpenRouter</h3>
                    </div>
                    <p class="text-gray-600 mb-4">Một cổng API thống nhất để truy cập nhiều mô hình AI bao gồm Grok, DeepSeek và nhiều hơn nữa.</p>
                    <div class="flex items-center text-sm text-gray-500 mb-2">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi-check-circle"></span>
                        <span>Nhiều mô hình AI</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-500 mb-4">
                        <span class="iconify text-green-500 mr-2" data-icon="mdi-check-circle"></span>
                        <span>Truy cập API đơn giản</span>
                    </div>
                    <a href="https://openrouter.ai/" target="_blank" class="inline-flex items-center text-purple-600 hover:text-purple-700">
                        <span class="iconify mr-1" data-icon="mdi-open-in-new"></span>
                        Lấy Khóa API
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Create API Key Modal -->
    <div id="createApiKeyModal" class="modal hidden fixed inset-0 z-50 overflow-auto flex" aria-hidden="true" role="dialog">
        <div class="modal-content relative p-0 bg-white rounded-lg shadow-xl m-auto max-w-lg w-full opacity-0 scale-95 transform transition-all duration-300">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h5 class="text-lg font-medium text-gray-900 inline-flex items-center">
                    <span class="iconify mr-2" data-icon="mdi-key-plus"></span>
                    Thêm Khóa API Mới
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-500" data-dismiss="modal">
                    <span class="iconify text-xl" data-icon="mdi-close"></span>
                </button>
            </div>
            <form action="{{ route('admin.ai_api_keys.store') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="mb-4">
                        <label for="provider" class="block text-sm font-medium text-gray-700 mb-1">Nhà Cung Cấp <span class="text-red-500">*</span></label>
                        <select name="provider" id="provider" class="w-full rounded-md border border-gray-300 py-2 px-3 text-base focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                            <option value="">-- Chọn Nhà Cung Cấp --</option>
                            @foreach($providers as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="provider-help mt-2 text-sm text-gray-500">
                            <div id="help-google-gemini" class="hidden">
                                <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                <strong>Google Gemini:</strong> Bạn có thể lấy khóa API từ <a href="https://ai.google.dev/" target="_blank" class="text-blue-600 hover:underline">Google AI Studio</a>.
                            </div>
                            <div id="help-xai-grok" class="hidden">
                                <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                <strong>xAI Grok:</strong> Hiện đang trong giai đoạn beta giới hạn. Truy cập <a href="https://x.ai/" target="_blank" class="text-green-600 hover:underline">x.ai</a> để được cấp quyền truy cập.
                            </div>
                            <div id="help-openrouter" class="hidden">
                                <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                <strong>OpenRouter:</strong> Đăng ký tại <a href="https://openrouter.ai/" target="_blank" class="text-purple-600 hover:underline">OpenRouter.ai</a> để lấy khóa API.
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="api_key" class="block text-sm font-medium text-gray-700 mb-1">Khóa API <span class="text-red-500">*</span></label>
                        <input type="text" name="api_key" id="api_key" class="w-full rounded-md border border-gray-300 py-2 px-3 font-mono text-base focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span class="iconify mr-1" data-icon="mdi-lock"></span>
                            Khóa API của bạn sẽ được mã hóa khi lưu trữ.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" class="w-full rounded-md border border-gray-300 py-2 px-3 text-base focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span class="iconify mr-1" data-icon="mdi-email"></span>
                            Email liên kết với khóa API này.
                        </div>
                    </div>

                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                               checked
                               style="position: absolute;top: auto;"
                        />
                        <label for="is_active"
                               class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                    </div>
                    <label for="is_active" class="text-sm font-medium text-gray-700">Kích Hoạt</label>
                    <div class="mt-1 ml-12 text-sm text-gray-500">
                        <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                        Chỉ những khóa API đang kích hoạt mới được sử dụng cho các hoạt động AI.
                    </div>
                </div>
                <div class="flex items-center justify-end p-4 border-t border-gray-200">
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 mr-2" data-dismiss="modal">
                        Hủy
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <span class="iconify mr-1" data-icon="mdi-content-save"></span>
                        Lưu Khóa API
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit API Key Modal -->
    <div id="editApiKeyModal" class="modal hidden fixed inset-0 z-50 overflow-auto flex" aria-hidden="true" role="dialog">
        <div class="modal-content relative p-0 bg-white rounded-lg shadow-xl m-auto max-w-lg w-full opacity-0 scale-95 transform transition-all duration-300">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h5 class="text-lg font-medium text-gray-900 inline-flex items-center">
                    <span class="iconify mr-2" data-icon="mdi-key-change"></span>
                    Chỉnh Sửa Khóa API
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-500" data-dismiss="modal">
                    <span class="iconify text-xl" data-icon="mdi-close"></span>
                </button>
            </div>
            <form id="editApiKeyForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6">
                    <div class="mb-4">
                        <label for="edit_provider" class="block text-sm font-medium text-gray-700 mb-1">Nhà Cung Cấp <span class="text-red-500">*</span></label>
                        <select name="provider" id="edit_provider" class="w-full rounded-md border border-gray-300 py-2 px-3 text-base focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                            <option value="">-- Chọn Nhà Cung Cấp --</option>
                            @foreach($providers as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="provider-help mt-2 text-sm text-gray-500">
                            <div id="edit-help-google-gemini" class="hidden">
                                <span class="iconify inline-block mr-1" data-icon="mdi-information-outline"></span>
                                <strong>Google Gemini:</strong> Bạn có thể lấy khóa API từ <a href="https://ai.google.dev/" target="_blank" class="text-blue-600 hover:underline">Google AI Studio</a>.
                            </div>
                            <div id="edit-help-xai-grok" class="hidden">
                                <span class="iconify inline-block mr-1" data-icon="mdi-information-outline"></span>
                                <strong>xAI Grok:</strong> Hiện đang trong giai đoạn beta giới hạn. Truy cập <a href="https://x.ai/" target="_blank" class="text-green-600 hover:underline">x.ai</a> để được cấp quyền truy cập.
                            </div>
                            <div id="edit-help-openrouter" class="hidden">
                                <span class="iconify inline-block mr-1" data-icon="mdi-information-outline"></span>
                                <strong>OpenRouter:</strong> Đăng ký tại <a href="https://openrouter.ai/" target="_blank" class="text-purple-600 hover:underline">OpenRouter.ai</a> để lấy khóa API.
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="edit_api_key" class="block text-sm font-medium text-gray-700 mb-1">Khóa API <span class="text-red-500">*</span></label>
                        <input type="text" name="api_key" id="edit_api_key" class="w-full rounded-md border border-gray-300 py-2 px-3 font-mono text-base focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span class="iconify mr-1" data-icon="mdi-lock"></span>
                            Khóa API của bạn sẽ được mã hóa khi lưu trữ.
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="edit_email" class="w-full rounded-md border border-gray-300 py-2 px-3 text-base focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" required>
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span class="iconify mr-1" data-icon="mdi-email"></span>
                            Email liên kết với khóa API này.
                        </div>
                    </div>

                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                               class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                               style="position: absolute;top: auto;"
                        />
                        <label for="edit_is_active"
                               class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                    </div>
                    <label for="edit_is_active" class="text-sm font-medium text-gray-700">Kích Hoạt</label>
                    <div class="mt-1 ml-12 text-sm text-gray-500">
                        <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                        Chỉ những khóa API đang kích hoạt mới được sử dụng cho các hoạt động AI.
                    </div>
                </div>
                <div class="flex items-center justify-end p-4 border-t border-gray-200">
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 mr-2" data-dismiss="modal">
                        Hủy
                    </button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <span class="iconify mr-1" data-icon="mdi-content-save"></span>
                        Cập Nhật Khóa API
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Test Connection Modal -->
    <div id="testConnectionModal" class="modal hidden fixed inset-0 z-50 overflow-auto flex" aria-hidden="true" role="dialog">
        <div class="modal-content relative p-0 bg-white rounded-lg shadow-xl m-auto max-w-lg w-full opacity-0 scale-95 transform transition-all duration-300">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h5 class="text-lg font-medium text-gray-900 inline-flex items-center">
                    <span class="iconify mr-2" data-icon="mdi-connection"></span>
                    Kiểm Tra Kết Nối Khóa API
                </h5>
                <button type="button" class="text-gray-400 hover:text-gray-500" data-dismiss="modal">
                    <span class="iconify text-xl" data-icon="mdi-close"></span>
                </button>
            </div>
            <div class="p-6">
                <div id="test-loading">
                    <div class="text-center py-4">
                        <span class="iconify text-indigo-600 text-4xl animate-spin" data-icon="mdi-loading"></span>
                        <p class="mt-2 text-gray-600">Đang kiểm tra kết nối đến nhà cung cấp AI...</p>
                    </div>
                </div>

                <div id="test-success" class="hidden">
                    <div class="text-center mb-4">
                        <span class="iconify text-green-500 text-4xl" data-icon="mdi-check-circle"></span>
                        <h4 class="text-lg font-medium text-gray-800 mt-2">Kết Nối Thành Công!</h4>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 mb-3">
                        <h5 class="font-medium text-gray-700 mb-2">Phản Hồi từ Nhà Cung Cấp AI:</h5>
                        <pre id="test-response" class="bg-white p-3 rounded border text-sm overflow-auto max-h-40"></pre>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <span class="iconify mr-2" data-icon="mdi-information-outline"></span>
                        Khóa API của bạn đang hoạt động tốt và có thể được sử dụng với nhà cung cấp này.
                    </div>
                </div>

                <div id="test-error" class="hidden">
                    <div class="text-center mb-4">
                        <span class="iconify text-red-500 text-4xl" data-icon="mdi-alert-circle"></span>
                        <h4 class="text-lg font-medium text-gray-800 mt-2">Kết Nối Thất Bại</h4>
                    </div>
                    <div class="bg-red-50 text-red-700 rounded-lg p-4 mb-3">
                        <h5 class="font-medium mb-2">Chi Tiết Lỗi:</h5>
                        <div id="test-error-message" class="text-sm"></div>
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <span class="iconify mr-2" data-icon="mdi-information-outline"></span>
                        Vui lòng kiểm tra lại khóa API của bạn và đảm bảo rằng nó có đầy đủ quyền truy cập cần thiết.
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end p-4 border-t border-gray-200">
                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" data-dismiss="modal">
                    <span class="iconify mr-1" data-icon="mdi-close"></span>
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <style>
        /* Toggle Switch Styles */
        .toggle-checkbox:checked {
            right: 0;
            border-color: #4CAF50;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #4CAF50;
        }
        .toggle-label {
            transition: background-color 0.2s ease;
        }
    </style>

@endsection

@push('scripts')
    @vite('resources/js/admin/ai-api-keys/index_main.js')
@endpush
