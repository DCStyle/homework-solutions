@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Thông Báo</h1>
            
            @if($notifications->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($notifications as $notification)
                        <div class="notification-item py-4 {{ $notification->read_at ? '' : 'bg-indigo-50' }}" data-id="{{ $notification->id }}">
                            <div class="flex justify-between">
                                <h3 class="text-lg font-medium text-gray-900">{{ $notification->data['title'] ?? 'Thông báo' }}</h3>
                                <span class="text-sm text-gray-500">{{ $notification->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            
                            <p class="mt-2 text-gray-600">{{ $notification->data['message'] ?? '' }}</p>
                            
                            @if(isset($notification->data['processed']))
                                <div class="mt-3 text-sm">
                                    <span class="text-green-600">Đã xử lý thành công: {{ $notification->data['processed'] }}</span> |
                                    <span class="text-red-600">Lỗi: {{ $notification->data['failed'] }}</span> |
                                    <span class="text-gray-600">Tổng: {{ $notification->data['total'] }}</span>
                                </div>
                            @endif
                            
                            @if(!empty($notification->data['errors']))
                                <div class="mt-3 p-3 bg-red-50 text-red-800 text-sm rounded">
                                    <strong>Lỗi:</strong> 
                                    <pre class="whitespace-pre-wrap mt-1">{{ $notification->data['errors'] }}</pre>
                                </div>
                            @endif
                            
                            @if(isset($notification->data['link']))
                                <div class="mt-3">
                                    <a href="{{ $notification->data['link'] }}" class="text-indigo-600 hover:text-indigo-800">
                                        Xem chi tiết
                                    </a>
                                </div>
                            @endif
                            
                            @if(!$notification->read_at)
                                <button class="mark-as-read-btn mt-3 text-sm text-gray-500 hover:text-indigo-600" data-id="{{ $notification->id }}">
                                    Đánh dấu đã đọc
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <span class="iconify text-6xl text-gray-300 mb-4" data-icon="mdi-bell-off-outline"></span>
                    <p class="text-gray-500">Bạn không có thông báo nào</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mark as read buttons
        document.querySelectorAll('.mark-as-read-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const item = this.closest('.notification-item');
                
                fetch(`/notifications/${id}/mark-as-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                }).then(response => {
                    if (response.ok) {
                        item.classList.remove('bg-indigo-50');
                        this.remove();
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection 