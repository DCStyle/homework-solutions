@extends('layouts.app')

@seo(['title' => 'Tải xuống tài liệu ' . $attachment->original_filename . ' | ' .  setting('site_name', 'Homework Solutions')])

@section('content')
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="container mx-auto px-4">
            <div class="flex gap-6 justify-between items-start">
                <!-- Left Sidebar - Ad Space -->
                <div class="hidden lg:block" style="width: 300px;">
                    <div class="w-full h-full bg-white rounded-lg shadow-sm border border-gray-100 p-4 sticky top-4">
                        <div class="w-full h-full bg-gray-50 rounded flex items-center justify-center">
                            <span class="text-gray-400">Quảng cáo</span>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="flex-1 max-w-2xl mx-auto">
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                        <!-- Header -->
                        <div class="bg-blue-500 p-6 text-white">
                            <h1 class="text-2xl font-bold text-center">Đang chuẩn bị tải xuống</h1>
                        </div>

                        <!-- Content -->
                        <div class="p-8">
                            <!-- File Info Card -->
                            <div class="bg-blue-50 rounded-xl p-6 mb-8">
                                <div class="flex items-start gap-4">
                                    <!-- File Type Icon -->
                                    @php
                                        $iconColor = match(strtolower($attachment->extension)) {
                                            'pdf' => 'text-red-500',
                                            'doc', 'docx' => 'text-blue-500',
                                            'xls', 'xlsx' => 'text-green-500',
                                            default => 'text-gray-500'
                                        };

                                        function formatFileSize($bytes) {
                                            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                                            $i = 0;
                                            while ($bytes >= 1024 && $i < count($units) - 1) {
                                                $bytes /= 1024;
                                                $i++;
                                            }
                                            return round($bytes, 2) . ' ' . $units[$i];
                                        }
                                    @endphp
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-lg bg-white flex items-center justify-center shadow-sm">
                                            <svg class="w-8 h-8 {{ $iconColor }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- File Details -->
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900 mb-1 break-all">
                                            {{ $attachment->original_filename }}
                                        </h3>
                                        <div class="flex items-center gap-4 text-sm text-gray-600">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                            </svg>
                                            {{ formatFileSize($attachment->file_size) }}
                                        </span>
                                            <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            {{ strtoupper($attachment->extension) }}
                                        </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Countdown Section -->
                            <div class="text-center">
                                <div class="countdown-timer mb-6">
                                    <div class="inline-flex items-center justify-center rounded-full bg-blue-500 p-1 w-24 h-24">
                                        <div class="w-full h-full rounded-full bg-white flex items-center justify-center">
                                            <span id="countdown" class="text-5xl font-bold text-blue-500">5</span>
                                        </div>
                                    </div>
                                    <p class="mt-4 text-gray-600">Đang chuẩn bị file tải xuống cho bạn. Vui lòng đợi giây lát.</p>
                                </div>

                                <!-- Download Button -->
                                <div id="download-button" class="hidden mb-6">
                                    <a href="{{ route('attachments.process-download', $attachment->id) }}"
                                       class="inline-flex items-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors duration-200">
                                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Tải xuống ngay
                                    </a>
                                </div>

                                <!-- Download Status (Hidden initially) -->
                                <div id="download-status" class="hidden">
                                    <div class="flex items-center justify-center gap-3 text-green-600">
                                        <svg class="w-6 h-6 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="font-medium">Đang bắt đầu tải xuống...</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Info -->
                            <div class="mt-8 py-8 border-t border-gray-100">
                                <div class="flex items-center gap-3 text-sm text-gray-600">
                                    <svg class="w-5 h-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p>Vui lòng không đóng trang này trong quá trình tải xuống.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar - Ad Space -->
                <div class="hidden lg:block" style="width: 300px;">
                    <div class="w-full h-full bg-white rounded-lg shadow-sm border border-gray-100 p-4 sticky top-4">
                        <div class="w-full h-full bg-gray-50 rounded flex items-center justify-center">
                            <span class="text-gray-400">Quảng cáo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let countdown = 5;
                const countdownElement = document.getElementById('countdown');
                const countdownParent = countdownElement.closest('.countdown-timer');
                const downloadButton = document.getElementById('download-button');
                const downloadStatus = document.getElementById('download-status');
                const downloadUrl = "{{ route('attachments.process-download', $attachment->id) }}";

                const timer = setInterval(() => {
                    countdown--;
                    countdownElement.textContent = countdown;

                    if (countdown <= 0) {
                        clearInterval(timer);
                        countdownParent.classList.add('hidden');
                        downloadButton.classList.remove('hidden');
                    }
                }, 1000);
            });
        </script>
    @endpush
@endsection
