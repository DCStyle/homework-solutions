@extends('admin_layouts.admin')

@section('content')
    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 animate-fade-in-down">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-semibold text-gray-900">Quản lý Menu</h1>
                <a href="{{ route('admin.menu.create') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Thêm mới
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden border-b border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200" id="menu-items-table">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="sr-only">Drag Handle</span>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tên
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Loại
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                URL
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Trạng thái
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Hành động
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="sortable-menu-items">
                        @if(count($menuItems))
                            @foreach($menuItems as $item)
                                @include('admin.menu.partials.menu-item-row', ['item' => $item, 'level' => 0])
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Chưa có menu item nào
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .sortable-ghost {
            background-color: #e2e8f0 !important;
            opacity: 0.5;
        }

        .sortable-chosen {
            background-color: #f8fafc !important;
        }

        .menu-item {
            transition: all 0.3s ease;
        }

        .menu-item:hover {
            background-color: #f8fafc;
        }

        .nested-indicator {
            position: relative;
        }

        .nested-indicator::before {
            content: "";
            position: absolute;
            left: -1rem;
            top: 50%;
            width: 1rem;
            height: 2px;
            background-color: #e2e8f0;
        }

        .nested-line {
            position: absolute;
            left: -1rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e2e8f0;
        }

        /* Visual feedback for invalid drop targets */
        .invalid-drop-target {
            background-color: #fee2e2 !important;
            border: 2px dashed #ef4444 !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to check if an element is a descendant of another element
            function isDescendant(parent, child) {
                let node = child;
                while (node != null) {
                    if (node.dataset.itemId === parent.dataset.itemId) {
                        return true;
                    }
                    node = node.parentElement.closest('[data-item-id]');
                }
                return false;
            }

            // Function to get all children elements of a menu item
            function getAllChildren(element) {
                const children = [];
                const childRows = element.parentElement.querySelectorAll('[data-parent-id]');
                childRows.forEach(child => {
                    if (isDescendant(element, child)) {
                        children.push(child);
                    }
                });
                return children;
            }

            let lastInvalidTarget = null;

            const sortable = new Sortable(document.getElementById('sortable-menu-items'), {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragoverBubble: true,
                onMove: function(evt) {
                    const draggedItem = evt.dragged;
                    const targetItem = evt.related;

                    // Remove invalid-drop-target class from last invalid target
                    if (lastInvalidTarget) {
                        lastInvalidTarget.classList.remove('invalid-drop-target');
                        lastInvalidTarget = null;
                    }

                    // Check if dropping into own children
                    if (isDescendant(draggedItem, targetItem)) {
                        // Add visual feedback for invalid drop target
                        targetItem.classList.add('invalid-drop-target');
                        lastInvalidTarget = targetItem;
                        return false;
                    }

                    return true;
                },
                onEnd: function(evt) {
                    // Clean up any remaining invalid-drop-target classes
                    if (lastInvalidTarget) {
                        lastInvalidTarget.classList.remove('invalid-drop-target');
                        lastInvalidTarget = null;
                    }

                    const itemId = evt.item.dataset.itemId;
                    const newIndex = evt.newIndex;
                    const parentId = evt.item.dataset.parentId || null;

                    // Get all children of the dragged item
                    const children = getAllChildren(evt.item);

                    // Update parent_id for all children if needed
                    if (children.length > 0) {
                        children.forEach(child => {
                            child.dataset.parentId = parentId;
                        });
                    }

                    // Send the new order to the server
                    fetch('/admin/menu/reorder', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            itemId: itemId,
                            newIndex: newIndex,
                            parentId: parentId,
                            children: children.map(child => ({
                                id: child.dataset.itemId,
                                parentId: child.dataset.parentId
                            }))
                        })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Show success message
                                const successMessage = document.createElement('div');
                                successMessage.className = 'mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 animate-fade-in-down';
                                successMessage.textContent = 'Menu đã được cập nhật thành công';
                                document.querySelector('.bg-white.rounded-lg.shadow').insertAdjacentElement('beforebegin', successMessage);

                                // Remove the message after 3 seconds
                                setTimeout(() => {
                                    successMessage.remove();
                                }, 3000);
                            }
                        })
                        .catch(error => {
                            console.error('Error reordering menu:', error);
                            // Show error message
                            const errorMessage = document.createElement('div');
                            errorMessage.className = 'mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 animate-fade-in-down';
                            errorMessage.textContent = 'Có lỗi xảy ra khi cập nhật menu';
                            document.querySelector('.bg-white.rounded-lg.shadow').insertAdjacentElement('beforebegin', errorMessage);

                            // Remove the message after 3 seconds
                            setTimeout(() => {
                                errorMessage.remove();
                            }, 3000);
                        });
                }
            });
        });
    </script>
@endpush
