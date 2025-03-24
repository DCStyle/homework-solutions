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
            <button class="inline-flex items-center justify-center gap-2.5 rounded-md bg-primary py-2 px-4 text-center font-medium text-white hover:bg-opacity-90 sm:px-6" data-bs-toggle="modal" data-bs-target="#createApiKeyModal">
                <span class="iconify mr-1" data-icon="mdi-plus"></span>
                Thêm Khóa API Mới
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- API Keys Table -->
    <div class="rounded-lg border border-stroke bg-white p-6 shadow-md">
        <div class="p-4 border-b border-gray-200 bg-gray-50">
            <h2 class="font-semibold text-gray-700">Tất Cả Khóa API</h2>
        </div>
        
        @if(count($apiKeys) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th class="px-4 py-3">Nhà Cung Cấp</th>
                            <th class="px-4 py-3">Khóa API</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Trạng Thái</th>
                            <th class="px-4 py-3">Ngày Thêm</th>
                            <th class="px-4 py-3 text-center">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($apiKeys as $key)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center">
                                    @if($key->provider == 'google-gemini')
                                        <span class="iconify mr-2 text-blue-500" data-icon="mdi-google"></span>
                                    @elseif($key->provider == 'xai-grok')
                                        <span class="iconify mr-2 text-green-500" data-icon="mdi-robot"></span>
                                    @elseif($key->provider == 'openrouter')
                                        <span class="iconify mr-2 text-purple-500" data-icon="mdi-web"></span>
                                    @else
                                        <span class="iconify mr-2 text-gray-500" data-icon="mdi-key"></span>
                                    @endif
                                    {{ $providers[$key->provider] ?? $key->provider }}
                                </div>
                            </td>
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
                            <td class="px-4 py-3 text-gray-500">{{ $key->created_at->format('M d, Y') }}</td>
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
                                    
                                    <a href="{{ route('admin.ai_api_keys.test', $key->id) }}" class="text-amber-500 hover:bg-amber-100 p-2 rounded-full">
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
        @else
            <div class="rounded-lg bg-gray-50 p-8 text-center">
                <span class="iconify text-4xl text-gray-400 mb-3" data-icon="mdi-key-outline"></span>
                <h4 class="text-lg font-medium text-gray-600 mb-1">Không Tìm Thấy Khóa API</h4>
                <p class="text-gray-500">Nhấp vào "Thêm Khóa API Mới" để thêm khóa API đầu tiên của bạn.</p>
            </div>
        @endif
    </div>

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
<div class="modal fade" id="createApiKeyModal" tabindex="-1" aria-labelledby="createApiKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createApiKeyModalLabel">
                    <span class="iconify mr-2" data-icon="mdi-key-plus"></span>
                    Thêm Khóa API Mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.ai_api_keys.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="provider" class="form-label font-medium text-gray-700">Nhà Cung Cấp <span class="text-red-500">*</span></label>
                        <select name="provider" id="provider" class="form-select" required>
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
                    
                    <div class="mb-3">
                        <label for="api_key" class="form-label font-medium text-gray-700">Khóa API <span class="text-red-500">*</span></label>
                        <input type="text" name="api_key" id="api_key" class="form-control font-mono" required>
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span class="iconify mr-1" data-icon="mdi-lock"></span>
                            Khóa API của bạn sẽ được mã hóa khi lưu trữ.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" class="form-control" required>
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span class="iconify mr-1" data-icon="mdi-email"></span>
                            Email liên kết với khóa API này.
                        </div>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Kích Hoạt</label>
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                            Chỉ những khóa API đang kích hoạt mới được sử dụng cho các hoạt động AI.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary inline-flex items-center">
                        <span class="iconify mr-1" data-icon="mdi-content-save"></span>
                        Lưu Khóa API
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit API Key Modal -->
<div class="modal fade" id="editApiKeyModal" tabindex="-1" aria-labelledby="editApiKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editApiKeyModalLabel">
                    <span class="iconify mr-2" data-icon="mdi-key-edit"></span>
                    Chỉnh Sửa Khóa API
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editApiKeyForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_provider" class="form-label font-medium text-gray-700">Nhà Cung Cấp <span class="text-red-500">*</span></label>
                        <select name="provider" id="edit_provider" class="form-select" required>
                            <option value="">-- Chọn Nhà Cung Cấp --</option>
                            @foreach($providers as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="provider-help mt-2 text-sm text-gray-500">
                            <div id="edit-help-google-gemini" class="hidden">
                                <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                <strong>Google Gemini:</strong> Bạn có thể lấy khóa API từ <a href="https://ai.google.dev/" target="_blank" class="text-blue-600 hover:underline">Google AI Studio</a>.
                            </div>
                            <div id="edit-help-xai-grok" class="hidden">
                                <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                <strong>xAI Grok:</strong> Hiện đang trong giai đoạn beta giới hạn. Truy cập <a href="https://x.ai/" target="_blank" class="text-green-600 hover:underline">x.ai</a> để được cấp quyền truy cập.
                            </div>
                            <div id="edit-help-openrouter" class="hidden">
                                <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                                <strong>OpenRouter:</strong> Đăng ký tại <a href="https://openrouter.ai/" target="_blank" class="text-purple-600 hover:underline">OpenRouter.ai</a> để lấy khóa API.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_api_key" class="form-label font-medium text-gray-700">Khóa API <span class="text-red-500">*</span></label>
                        <input type="text" name="api_key" id="edit_api_key" class="form-control font-mono" required>
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span class="iconify mr-1" data-icon="mdi-lock"></span>
                            Khóa API của bạn sẽ được mã hóa khi lưu trữ.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span class="iconify mr-1" data-icon="mdi-email"></span>
                            Email liên kết với khóa API này.
                        </div>
                    </div>
                    
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">Kích Hoạt</label>
                        <div class="mt-1 flex items-center text-sm text-gray-500">
                            <span class="iconify mr-1" data-icon="mdi-information-outline"></span>
                            Chỉ những khóa API đang kích hoạt mới được sử dụng cho các hoạt động AI.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary inline-flex items-center">
                        <span class="iconify mr-1" data-icon="mdi-content-save"></span>
                        Cập Nhật Khóa API
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Test Connection Modal -->
<div class="modal fade" id="testConnectionModal" tabindex="-1" aria-labelledby="testConnectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testConnectionModalLabel">
                    <span class="iconify mr-2" data-icon="mdi-connection"></span>
                    Kiểm Tra Kết Nối Khóa API
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="test-loading">
                    <div class="text-center py-4">
                        <span class="iconify text-primary text-4xl animate-spin" data-icon="mdi-loading"></span>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary inline-flex items-center" data-bs-dismiss="modal">
                    <span class="iconify mr-1" data-icon="mdi-close"></span>
                    Đóng
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Toggle API key visibility
    $('.toggle-key-visibility').on('click', function() {
        const maskedSpan = $(this).siblings('.api-key-masked');
        const fullSpan = $(this).siblings('.api-key-full');
        const icon = $(this).find('span.iconify');
        
        if (maskedSpan.hasClass('hidden')) {
            maskedSpan.removeClass('hidden');
            fullSpan.addClass('hidden');
            icon.attr('data-icon', 'mdi-eye');
        } else {
            maskedSpan.addClass('hidden');
            fullSpan.removeClass('hidden');
            icon.attr('data-icon', 'mdi-eye-off');
        }
    });
    
    // Copy API key to clipboard
    $('.copy-key').on('click', function() {
        const key = $(this).data('key');
        navigator.clipboard.writeText(key).then(function() {
            // Show a temporary tooltip or notification
            alert('API key copied to clipboard!');
        });
    });
    
    // Show provider-specific help text in create modal
    $('#provider').on('change', function() {
        // Hide all help divs first
        $('.provider-help > div').addClass('hidden');
        
        // Show the selected provider's help
        const provider = $(this).val();
        if (provider) {
            $(`#help-${provider}`).removeClass('hidden');
        }
    });
    
    // Show provider-specific help text in edit modal
    $('#edit_provider').on('change', function() {
        // Hide all help divs first
        $('.provider-help > div').addClass('hidden');
        
        // Show the selected provider's help
        const provider = $(this).val();
        if (provider) {
            $(`#edit-help-${provider}`).removeClass('hidden');
        }
    });
    
    // Delete confirmation
    $('.delete-key-form').on('submit', function(e) {
        if (!confirm('Are you sure you want to delete this API key? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Open edit modal with data
    $('.edit-api-key').on('click', function() {
        const id = $(this).data('id');
        const provider = $(this).data('provider');
        const email = $(this).data('email');
        const key = $(this).data('key');
        const isActive = $(this).data('active') == 1;
        
        // Set form action URL
        $('#editApiKeyForm').attr('action', `/admin/ai-api-keys/${id}`);
        
        // Populate form fields
        $('#edit_provider').val(provider).trigger('change');
        $('#edit_email').val(email);
        $('#edit_api_key').val(key);
        $('#edit_is_active').prop('checked', isActive);
        
        // Show the modal
        const editModal = new bootstrap.Modal(document.getElementById('editApiKeyModal'));
        editModal.show();
    });

    // Test API key connection
    $('.text-amber-500').on('click', function(e) {
        e.preventDefault();
        
        // Get the API key ID from the URL
        const href = $(this).attr('href');
        const id = href.split('/').pop();
        
        // Show the modal with loading indicator
        $('#test-loading').removeClass('hidden').show();
        $('#test-success, #test-error').addClass('hidden');
        
        const testModal = new bootstrap.Modal(document.getElementById('testConnectionModal'));
        testModal.show();
        
        // Make AJAX request to test the connection
        $.ajax({
            url: href,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Hide loading indicator
                $('#test-loading').hide();
                
                // Show success message
                $('#test-success').removeClass('hidden');
                
                // Format and display the response
                let formattedResponse = '';
                if (typeof response.result === 'object') {
                    formattedResponse = JSON.stringify(response.result, null, 2);
                } else {
                    formattedResponse = response.result || "Request successful";
                }
                
                $('#test-response').text(formattedResponse);
            },
            error: function(xhr) {
                // Hide loading indicator
                $('#test-loading').hide();
                
                // Show error message
                $('#test-error').removeClass('hidden');
                
                // Display error details
                let errorMessage = 'Unknown error';
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || response.error || xhr.statusText;
                } catch (e) {
                    errorMessage = xhr.statusText || 'Connection failed';
                }
                
                $('#test-error-message').text(errorMessage);
            }
        });
    });
});
</script>
@endsection