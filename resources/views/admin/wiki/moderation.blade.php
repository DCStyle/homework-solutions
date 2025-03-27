@extends('admin_layouts.admin')

@section('title', 'Kiểm Duyệt Wiki Q&A')

@section('content')
    <div class="container-fluid px-4 py-5">
        <!-- Header với gradient background -->
        <div class="rounded-xl bg-gradient-to-r from-amber-50 to-yellow-50 p-6 shadow-sm mb-6 border border-amber-100">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <span class="iconify text-amber-500 text-3xl mt-1" data-icon="mdi-shield-check"></span>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Kiểm Duyệt Wiki Q&A</h1>
                        <p class="text-gray-600 mt-1">Xem xét và kiểm duyệt các câu hỏi được gửi bởi người dùng</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.wiki.settings') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-amber-200 bg-white py-2 px-4 text-sm font-medium text-amber-600 hover:bg-amber-50 shadow-sm">
                        <span class="iconify" data-icon="mdi-cog"></span>
                        Cài đặt Wiki
                    </a>
                    <a href="{{ route('admin.wiki.questions') }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-amber-200 bg-white py-2 px-4 text-sm font-medium text-amber-600 hover:bg-amber-50 shadow-sm">
                        <span class="iconify" data-icon="mdi-format-list-text"></span>
                        Quản lý câu hỏi
                    </a>
                </div>
            </div>
        </div>

        <!-- Thông báo Ajax -->
        <div id="ajax-alert" class="alert alert-dismissible fade show rounded-lg border-l-4 p-4 mb-6 hidden" role="alert">
            <div class="flex items-center">
                <span id="ajax-alert-icon" class="text-xl mr-2"></span>
                <span id="ajax-alert-message"></span>
            </div>
            <button type="button" class="btn-close" onclick="document.getElementById('ajax-alert').classList.add('hidden')" aria-label="Close"></button>
        </div>

        <!-- Danh sách câu hỏi đang chờ -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-gray-100 bg-gray-50 px-5 py-4 flex justify-between items-center">
                <div class="flex items-center">
                    <span class="iconify text-amber-500 mr-2" data-icon="mdi-clock-outline"></span>
                    <h2 class="text-lg font-semibold text-gray-800">Câu hỏi đang chờ duyệt</h2>
                </div>
                <div class="bg-amber-100 text-amber-800 font-medium py-1 px-3 rounded-full text-sm flex items-center">
                    <span class="iconify mr-1" data-icon="mdi-clock-alert"></span>
                    <span id="pending-count">{{ $pendingQuestions->total() }}</span> câu hỏi chờ duyệt
                </div>
            </div>

            <!-- Bulk actions bar -->
            <div class="bg-gray-50 border-b border-gray-100 px-5 py-3 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="form-check">
                        <input type="checkbox" id="select-all" class="form-check-input rounded border-gray-300 cursor-pointer">
                        <label for="select-all" class="ml-2 text-sm text-gray-700 cursor-pointer">Chọn tất cả</label>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span id="selected-count" class="text-sm text-gray-600 mr-2 hidden">Đã chọn: <span class="font-medium">0</span></span>
                    <button id="bulk-approve" class="inline-flex items-center justify-center gap-2 rounded-md bg-green-600 py-2 px-3 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <span class="iconify" data-icon="mdi-check-circle"></span>
                        Phê duyệt đã chọn
                    </button>
                    <button id="bulk-reject" class="inline-flex items-center justify-center gap-2 rounded-md bg-red-100 py-2 px-3 text-sm font-medium text-red-700 hover:bg-red-200 focus:outline-none focus:ring-4 focus:ring-red-100 shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <span class="iconify" data-icon="mdi-close-circle"></span>
                        Từ chối đã chọn
                    </button>
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($pendingQuestions as $question)
                    <div id="question-item-{{ $question->id }}" class="p-5 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start">
                            <div class="mr-3 mt-1">
                                <input type="checkbox" data-id="{{ $question->id }}" class="question-checkbox form-check-input rounded border-gray-300 cursor-pointer">
                            </div>
                            <div class="flex-1 flex flex-col space-y-3">
                                <!-- Tiêu đề và danh mục -->
                                <div class="flex justify-between items-start">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $question->title }}</h3>
                                    <span class="ml-3 inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
                                        {{ $question->category->name }}
                                    </span>
                                </div>

                                <!-- Nội dung ngắn gọn -->
                                <div class="text-sm text-gray-600 question-content">
                                    <div class="question-preview">
                                        {!! Str::limit(strip_tags($question->content), 200) !!}
                                        @if(strlen(strip_tags($question->content)) > 200)
                                            <button type="button" class="text-amber-600 hover:text-amber-800 text-xs font-medium ml-1 focus:outline-none focus:underline"
                                                    onclick="toggleContent('question-{{ $question->id }}')">
                                                Xem thêm
                                            </button>
                                        @endif
                                    </div>

                                    @if(strlen(strip_tags($question->content)) > 200)
                                        <div id="question-{{ $question->id }}" class="question-full-content hidden mt-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                                            <div class="prose prose-sm max-w-none">
                                                {!! $question->content !!}
                                            </div>
                                            <button type="button" class="text-amber-600 hover:text-amber-800 text-xs font-medium mt-2 focus:outline-none focus:underline"
                                                    onclick="toggleContent('question-{{ $question->id }}')">
                                                Thu gọn
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                <!-- Thông tin người gửi -->
                                <div class="flex flex-wrap items-center text-xs text-gray-500 gap-3">
                                    <div class="flex items-center">
                                        <span class="iconify text-gray-400 mr-1" data-icon="mdi-account"></span>
                                        <span>{{ $question->user->name }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="iconify text-gray-400 mr-1" data-icon="mdi-calendar"></span>
                                        <span>{{ $question->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if($question->bookGroup)
                                        <div class="flex items-center">
                                            <span class="iconify text-gray-400 mr-1" data-icon="mdi-book-multiple"></span>
                                            <span>{{ $question->bookGroup->name }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Nút thao tác -->
                                <div class="flex flex-wrap gap-3 pt-2">
                                    <button type="button"
                                            class="approve-btn inline-flex items-center justify-center gap-2 rounded-md bg-green-600 py-2 px-3 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-300 shadow-sm transition-colors"
                                            data-id="{{ $question->id }}">
                                        <span class="iconify" data-icon="mdi-check-circle"></span>
                                        Phê duyệt
                                    </button>

                                    <button type="button"
                                            class="reject-btn inline-flex items-center justify-center gap-2 rounded-md bg-red-100 py-2 px-3 text-sm font-medium text-red-700 hover:bg-red-200 focus:outline-none focus:ring-4 focus:ring-red-100 shadow-sm transition-colors"
                                            data-id="{{ $question->id }}">
                                        <span class="iconify" data-icon="mdi-close-circle"></span>
                                        Từ chối
                                    </button>

                                    <a href="{{ route('wiki.show', [$question->category->slug, $question->slug]) }}"
                                       target="_blank"
                                       class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white py-2 px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 shadow-sm transition-colors">
                                        <span class="iconify" data-icon="mdi-eye"></span>
                                        Xem trước
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <div class="flex flex-col items-center justify-center text-gray-500">
                            <div class="mb-4 p-3 rounded-full bg-green-50 text-green-500">
                                <span class="iconify text-5xl" data-icon="mdi-check-circle"></span>
                            </div>
                            <h4 class="text-lg font-medium text-gray-600 mb-1">Không có câu hỏi nào đang chờ duyệt</h4>
                            <p class="text-gray-500">Tất cả câu hỏi đã được kiểm duyệt</p>
                            <a href="{{ route('admin.wiki.questions') }}" class="mt-4 inline-flex items-center justify-center gap-2 rounded-md bg-amber-50 px-3 py-2 text-sm font-medium text-amber-600 hover:bg-amber-100">
                                <span class="iconify" data-icon="mdi-format-list-text"></span>
                                Xem tất cả câu hỏi
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Phân trang -->
            @if($pendingQuestions->hasPages())
                <div class="px-5 py-4 border-t border-gray-200 bg-white">
                    {{ $pendingQuestions->links() }}
                </div>
            @endif
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Toggle content visibility
        function toggleContent(id) {
            const content = document.getElementById(id);
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                // Hiệu ứng hiển thị với animation
                content.style.opacity = '0';
                setTimeout(() => {
                    content.style.opacity = '1';
                    content.style.transition = 'opacity 0.3s ease-in-out';
                }, 10);
            } else {
                // Hiệu ứng ẩn với animation
                content.style.opacity = '0';
                content.style.transition = 'opacity 0.2s ease-in-out';
                setTimeout(() => {
                    content.classList.add('hidden');
                }, 200);
            }
        }

        // Show alert function
        function showAlert(message, type) {
            const alertElement = document.getElementById('ajax-alert');
            const alertIcon = document.getElementById('ajax-alert-icon');
            const alertMessage = document.getElementById('ajax-alert-message');

            // Set alert type
            alertElement.classList.remove('border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50');
            alertIcon.classList.remove('text-green-500', 'text-red-500');

            if (type === 'success') {
                alertElement.classList.add('border-green-500', 'bg-green-50');
                alertIcon.classList.add('text-green-500');
                alertIcon.setAttribute('data-icon', 'mdi-check-circle');
            } else {
                alertElement.classList.add('border-red-500', 'bg-red-50');
                alertIcon.classList.add('text-red-500');
                alertIcon.setAttribute('data-icon', 'mdi-alert-circle');
            }

            // Set message
            alertMessage.textContent = message;

            // Show alert
            alertElement.classList.remove('hidden');

            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertElement.classList.add('hidden');
            }, 5000);
        }

        // Update selected count
        function updateSelectedCount() {
            const selectedBoxes = document.querySelectorAll('.question-checkbox:checked');
            const selectedCount = selectedBoxes.length;
            const countElement = document.getElementById('selected-count');
            const countDisplay = countElement.querySelector('span.font-medium');
            const bulkApproveBtn = document.getElementById('bulk-approve');
            const bulkRejectBtn = document.getElementById('bulk-reject');

            if (selectedCount > 0) {
                countElement.classList.remove('hidden');
                countDisplay.textContent = selectedCount;
                bulkApproveBtn.disabled = false;
                bulkRejectBtn.disabled = false;
            } else {
                countElement.classList.add('hidden');
                bulkApproveBtn.disabled = true;
                bulkRejectBtn.disabled = true;
            }
        }

        // Approve question function
        function approveQuestion(questionId, bulkMode = false) {
            // If not in bulk mode, disable the button
            if (!bulkMode) {
                const btn = document.querySelector(`.approve-btn[data-id="${questionId}"]`);
                btn.disabled = true;
                btn.innerHTML = '<span class="iconify animate-spin" data-icon="mdi-loading"></span> Đang xử lý...';
            }

            fetch(`/admin/wiki/questions/${questionId}/approve/`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (!bulkMode) {
                            // Remove the question item from the list
                            const questionElement = document.getElementById(`question-item-${questionId}`);
                            questionElement.style.opacity = '0';
                            questionElement.style.transition = 'opacity 0.3s ease-in-out';
                            setTimeout(() => {
                                questionElement.remove();

                                // Update pending count
                                const pendingCount = document.getElementById('pending-count');
                                pendingCount.textContent = parseInt(pendingCount.textContent) - 1;

                                // Check if no questions left
                                checkEmptyState();
                            }, 300);

                            // Show success message
                            showAlert('Câu hỏi đã được phê duyệt thành công.', 'success');
                        }
                    } else {
                        // If not bulk mode, restore the button
                        if (!bulkMode) {
                            const btn = document.querySelector(`.approve-btn[data-id="${questionId}"]`);
                            btn.disabled = false;
                            btn.innerHTML = '<span class="iconify" data-icon="mdi-check-circle"></span> Phê duyệt';

                            // Show error message
                            showAlert(data.message || 'Có lỗi xảy ra khi phê duyệt câu hỏi.', 'error');
                        }
                    }

                    return data.success;
                })
                .catch(error => {
                    console.error('Error:', error);

                    // If not bulk mode, restore the button
                    if (!bulkMode) {
                        const btn = document.querySelector(`.approve-btn[data-id="${questionId}"]`);
                        btn.disabled = false;
                        btn.innerHTML = '<span class="iconify" data-icon="mdi-check-circle"></span> Phê duyệt';

                        // Show error message
                        showAlert('Có lỗi xảy ra khi kết nối đến máy chủ.', 'error');
                    }

                    return false;
                });
        }

        // Reject question function
        function rejectQuestion(questionId, bulkMode = false) {
            // If not in bulk mode, disable the button
            if (!bulkMode) {
                const btn = document.querySelector(`.reject-btn[data-id="${questionId}"]`);
                btn.disabled = true;
                btn.innerHTML = '<span class="iconify animate-spin" data-icon="mdi-loading"></span> Đang xử lý...';
            }

            fetch(`/admin/wiki/questions/${questionId}/reject/`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (!bulkMode) {
                            // Remove the question item from the list
                            const questionElement = document.getElementById(`question-item-${questionId}`);
                            questionElement.style.opacity = '0';
                            questionElement.style.transition = 'opacity 0.3s ease-in-out';
                            setTimeout(() => {
                                questionElement.remove();

                                // Update pending count
                                const pendingCount = document.getElementById('pending-count');
                                pendingCount.textContent = parseInt(pendingCount.textContent) - 1;

                                // Check if no questions left
                                checkEmptyState();
                            }, 300);

                            // Show success message
                            showAlert('Câu hỏi đã bị từ chối thành công.', 'success');
                        }
                    } else {
                        // If not bulk mode, restore the button
                        if (!bulkMode) {
                            const btn = document.querySelector(`.reject-btn[data-id="${questionId}"]`);
                            btn.disabled = false;
                            btn.innerHTML = '<span class="iconify" data-icon="mdi-close-circle"></span> Từ chối';

                            // Show error message
                            showAlert(data.message || 'Có lỗi xảy ra khi từ chối câu hỏi.', 'error');
                        }
                    }

                    return data.success;
                })
                .catch(error => {
                    console.error('Error:', error);

                    // If not bulk mode, restore the button
                    if (!bulkMode) {
                        const btn = document.querySelector(`.reject-btn[data-id="${questionId}"]`);
                        btn.disabled = false;
                        btn.innerHTML = '<span class="iconify" data-icon="mdi-close-circle"></span> Từ chối';

                        // Show error message
                        showAlert('Có lỗi xảy ra khi kết nối đến máy chủ.', 'error');
                    }

                    return false;
                });
        }

        // Check if question list is empty and display empty state
        function checkEmptyState() {
            const questionItems = document.querySelectorAll('[id^="question-item-"]');
            if (questionItems.length === 0) {
                const container = document.querySelector('.divide-y.divide-gray-100');

                const emptyState = document.createElement('div');
                emptyState.className = 'text-center py-16';
                emptyState.innerHTML = `
                    <div class="flex flex-col items-center justify-center text-gray-500">
                        <div class="mb-4 p-3 rounded-full bg-green-50 text-green-500">
                            <span class="iconify text-5xl" data-icon="mdi-check-circle"></span>
                        </div>
                        <h4 class="text-lg font-medium text-gray-600 mb-1">Không có câu hỏi nào đang chờ duyệt</h4>
                        <p class="text-gray-500">Tất cả câu hỏi đã được kiểm duyệt</p>
                        <a href="{{ route('admin.wiki.questions') }}" class="mt-4 inline-flex items-center justify-center gap-2 rounded-md bg-amber-50 px-3 py-2 text-sm font-medium text-amber-600 hover:bg-amber-100">
                            <span class="iconify" data-icon="mdi-format-list-text"></span>
                            Xem tất cả câu hỏi
                        </a>
                    </div>
                `;

                container.appendChild(emptyState);

                // Hide bulk actions
                document.querySelector('.bg-gray-50.border-b.border-gray-100.px-5.py-3').style.display = 'none';
            }
        }

        // Bulk approve questions
        async function bulkApprove() {
            const selectedBoxes = document.querySelectorAll('.question-checkbox:checked');
            const questionIds = Array.from(selectedBoxes).map(box => box.getAttribute('data-id'));

            if (questionIds.length === 0) return;

            // Disable bulk buttons
            document.getElementById('bulk-approve').disabled = true;
            document.getElementById('bulk-reject').disabled = true;
            document.getElementById('bulk-approve').innerHTML = '<span class="iconify animate-spin" data-icon="mdi-loading"></span> Đang xử lý...';

            // Process each question
            let successCount = 0;
            let failCount = 0;

            for (const id of questionIds) {
                try {
                    const result = await fetch(`/admin/wiki/questions/approve/${id}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json());

                    if (result.success) {
                        successCount++;

                        // Remove the question item from the list
                        const questionElement = document.getElementById(`question-item-${id}`);
                        questionElement.style.opacity = '0';
                        questionElement.style.transition = 'opacity 0.3s ease-in-out';
                        setTimeout(() => {
                            questionElement.remove();
                        }, 300);
                    } else {
                        failCount++;
                    }
                } catch (error) {
                    console.error('Error processing question', id, error);
                    failCount++;
                }
            }

            // Update pending count
            const pendingCount = document.getElementById('pending-count');
            pendingCount.textContent = parseInt(pendingCount.textContent) - successCount;

            // Show result message
            if (successCount > 0 && failCount === 0) {
                showAlert(`Đã phê duyệt thành công ${successCount} câu hỏi.`, 'success');
            } else if (successCount > 0 && failCount > 0) {
                showAlert(`Đã phê duyệt ${successCount} câu hỏi, ${failCount} câu hỏi không thành công.`, 'success');
            } else {
                showAlert('Không có câu hỏi nào được phê duyệt thành công.', 'error');
            }

            // Reset bulk UI
            document.getElementById('select-all').checked = false;
            document.querySelectorAll('.question-checkbox').forEach(box => box.checked = false);
            updateSelectedCount();

            // Restore bulk buttons
            document.getElementById('bulk-approve').disabled = false;
            document.getElementById('bulk-approve').innerHTML = '<span class="iconify" data-icon="mdi-check-circle"></span> Phê duyệt đã chọn';
            document.getElementById('bulk-reject').disabled = false;

            // Check if no questions left
            checkEmptyState();
        }

        // Bulk reject questions
        async function bulkReject() {
            const selectedBoxes = document.querySelectorAll('.question-checkbox:checked');
            const questionIds = Array.from(selectedBoxes).map(box => box.getAttribute('data-id'));

            if (questionIds.length === 0) return;

            if (!confirm('Bạn có chắc chắn muốn từ chối tất cả câu hỏi đã chọn?')) {
                return;
            }

            // Disable bulk buttons
            document.getElementById('bulk-approve').disabled = true;
            document.getElementById('bulk-reject').disabled = true;
            document.getElementById('bulk-reject').innerHTML = '<span class="iconify animate-spin" data-icon="mdi-loading"></span> Đang xử lý...';

            // Process each question
            let successCount = 0;
            let failCount = 0;

            for (const id of questionIds) {
                try {
                    const result = await fetch(`/admin/wiki/questions/reject/${id}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json());

                    if (result.success) {
                        successCount++;

                        // Remove the question item from the list
                        const questionElement = document.getElementById(`question-item-${id}`);
                        questionElement.style.opacity = '0';
                        questionElement.style.transition = 'opacity 0.3s ease-in-out';
                        setTimeout(() => {
                            questionElement.remove();
                        }, 300);
                    } else {
                        failCount++;
                    }
                } catch (error) {
                    console.error('Error processing question', id, error);
                    failCount++;
                }
            }

            // Update pending count
            const pendingCount = document.getElementById('pending-count');
            pendingCount.textContent = parseInt(pendingCount.textContent) - successCount;

            // Show result message
            if (successCount > 0 && failCount === 0) {
                showAlert(`Đã từ chối thành công ${successCount} câu hỏi.`, 'success');
            } else if (successCount > 0 && failCount > 0) {
                showAlert(`Đã từ chối ${successCount} câu hỏi, ${failCount} câu hỏi không thành công.`, 'success');
            } else {
                showAlert('Không có câu hỏi nào được từ chối thành công.', 'error');
            }

            // Reset bulk UI
            document.getElementById('select-all').checked = false;
            document.querySelectorAll('.question-checkbox').forEach(box => box.checked = false);
            updateSelectedCount();

            // Restore bulk buttons
            document.getElementById('bulk-reject').disabled = false;
            document.getElementById('bulk-reject').innerHTML = '<span class="iconify" data-icon="mdi-close-circle"></span> Từ chối đã chọn';
            document.getElementById('bulk-approve').disabled = false;

            // Check if no questions left
            checkEmptyState();
        }

        // Document ready
        document.addEventListener('DOMContentLoaded', function() {
            // Approve buttons
            document.querySelectorAll('.approve-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const questionId = this.getAttribute('data-id');
                    approveQuestion(questionId);
                });
            });

            // Reject buttons
            document.querySelectorAll('.reject-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Bạn có chắc chắn muốn từ chối câu hỏi này? Câu hỏi sẽ bị ẩn.')) {
                        const questionId = this.getAttribute('data-id');
                        rejectQuestion(questionId);
                    }
                });
            });

            // Checkbox selection
            document.querySelectorAll('.question-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });

            // Select all checkbox
            document.getElementById('select-all').addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.question-checkbox');
                checkboxes.forEach(box => {
                    box.checked = this.checked;
                });
                updateSelectedCount();
            });

            // Bulk approve button
            document.getElementById('bulk-approve').addEventListener('click', bulkApprove);

            // Bulk reject button
            document.getElementById('bulk-reject').addEventListener('click', bulkReject);
        });
    </script>
@endpush
