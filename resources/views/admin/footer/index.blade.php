@extends('admin_layouts.admin')

@section('title', 'Footer Management')

@section('content')
<div class="flex flex-col gap-4">
    <div class="rounded-sm border border-stroke bg-white px-5 pt-6 pb-2.5 shadow-default dark:border-strokedark dark:bg-boxdark sm:px-7.5 xl:pb-1">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <h4 class="text-xl font-semibold text-black dark:text-white">
                Footer Management
            </h4>
            <button type="button" data-modal-target="addColumnModal" data-modal-toggle="addColumnModal" 
                class="flex items-center gap-2 rounded bg-primary px-4 py-2 font-medium text-white hover:bg-opacity-80">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Column
            </button>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-gray-800 dark:text-green-400" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="flex-1">
                <div class="mb-4 text-lg font-bold">Preview</div>
                <div class="border p-4 rounded bg-gray-50 dark:bg-gray-700">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        @foreach($columns->where('is_active', true) as $column)
                            <div>
                                <h4 class="text-purple-600 font-semibold mb-4">{{ $column->title }}</h4>
                                <ul class="space-y-2">
                                    @foreach($column->links->where('is_active', true) as $link)
                                        <li>
                                            <a href="{{ $link->url }}" class="hover:text-purple-600">
                                                {{ $link->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <div class="mb-4 text-lg font-bold">Manage Columns</div>
            <div id="footer-columns" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($columns as $column)
                    <div class="footer-column-item border rounded p-4" data-column-id="{{ $column->id }}">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">{{ $column->title }}</h3>
                            <div class="flex items-center space-x-2">
                                <button type="button" 
                                    onclick="editColumn({{ $column->id }}, '{{ $column->title }}', {{ $column->is_active ? 'true' : 'false' }})"
                                    class="p-1 hover:bg-gray-100 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-primary">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21h-9.5A2.25 2.25 0 014 18.75V8.25A2.25 2.25 0 016.25 6H13" />
                                    </svg>
                                </button>
                                <form action="{{ route('admin.footer.columns.destroy', $column) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this column and all its links?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 hover:bg-gray-100 rounded">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-danger">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="mb-2 flex items-center">
                            <span class="mr-2 text-sm font-medium">Status:</span>
                            <span class="px-2 py-1 text-xs {{ $column->is_active ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }} rounded-full">
                                {{ $column->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        
                        <div class="mt-4">
                            <div class="flex justify-between mb-2">
                                <h4 class="font-medium">Links ({{ $column->links->count() }})</h4>
                                <button type="button" 
                                    onclick="showAddLinkModal({{ $column->id }})"
                                    class="text-sm text-primary hover:underline flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Add Link
                                </button>
                            </div>
                            <ul class="space-y-2 links-container">
                                @foreach($column->links as $link)
                                    <li class="footer-link-item flex justify-between items-center p-2 hover:bg-gray-50 rounded" data-link-id="{{ $link->id }}">
                                        <div>
                                            <span class="font-medium">{{ $link->title }}</span>
                                            <br>
                                            <span class="text-xs text-gray-500">{{ $link->url }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 text-xs {{ $link->is_active ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }} rounded-full">
                                                {{ $link->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                            <button type="button" 
                                                onclick="editLink({{ $link->id }}, '{{ $link->title }}', '{{ $link->url }}', {{ $link->is_active ? 'true' : 'false' }})"
                                                class="p-1 hover:bg-gray-100 rounded">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-primary">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21h-9.5A2.25 2.25 0 014 18.75V8.25A2.25 2.25 0 016.25 6H13" />
                                                </svg>
                                            </button>
                                            <form action="{{ route('admin.footer.links.destroy', $link) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this link?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 hover:bg-gray-100 rounded">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-danger">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Add Column Modal -->
<div id="addColumnModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full flex items-center justify-center">
    <div class="relative w-full max-w-lg max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-5 border-b rounded-t">
                <h3 class="text-xl font-medium text-gray-900">
                    Add Footer Column
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="addColumnModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form action="{{ route('admin.footer.columns.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-6 text-gray-800">
                    <div>
                        <label for="column-title" class="block mb-2 text-sm font-medium text-gray-900">Column Title</label>
                        <input type="text" id="column-title" name="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" required>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="submit" class="text-white bg-primary hover:bg-primary-dark focus:ring-4 focus:outline-none focus:ring-primary/30 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Add Column</button>
                    <button data-modal-hide="addColumnModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Column Modal -->
<div id="editColumnModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full flex items-center justify-center">
    <div class="relative w-full max-w-lg max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-5 border-b rounded-t">
                <h3 class="text-xl font-medium text-gray-900">
                    Edit Footer Column
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="editColumnModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form id="editColumnForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-6 text-gray-800">
                    <div>
                        <label for="edit-column-title" class="block mb-2 text-sm font-medium text-gray-900">Column Title</label>
                        <input type="text" id="edit-column-title" name="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" required>
                    </div>
                    <div class="flex items-center">
                        <input id="edit-column-active" type="checkbox" name="is_active" value="1" class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary">
                        <label for="edit-column-active" class="ml-2 text-sm font-medium text-gray-900">Active</label>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="submit" class="text-white bg-primary hover:bg-primary-dark focus:ring-4 focus:outline-none focus:ring-primary/30 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save Changes</button>
                    <button data-modal-hide="editColumnModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Link Modal -->
<div id="addLinkModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full flex items-center justify-center">
    <div class="relative w-full max-w-lg max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-5 border-b rounded-t">
                <h3 class="text-xl font-medium text-gray-900">
                    Add Link
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="addLinkModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form id="addLinkForm" action="" method="POST">
                @csrf
                <div class="p-6 space-y-6 text-gray-800">
                    <div>
                        <label for="link-title" class="block mb-2 text-sm font-medium text-gray-900">Link Title</label>
                        <input type="text" id="link-title" name="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" required>
                    </div>
                    <div>
                        <label for="link-url" class="block mb-2 text-sm font-medium text-gray-900">URL</label>
                        <input type="text" id="link-url" name="url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" required>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="submit" class="text-white bg-primary hover:bg-primary-dark focus:ring-4 focus:outline-none focus:ring-primary/30 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Add Link</button>
                    <button data-modal-hide="addLinkModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Link Modal -->
<div id="editLinkModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full flex items-center justify-center">
    <div class="relative w-full max-w-lg max-h-full">
        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-5 border-b rounded-t">
                <h3 class="text-xl font-medium text-gray-900">
                    Edit Link
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="editLinkModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <form id="editLinkForm" action="" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-6 text-gray-800">
                    <div>
                        <label for="edit-link-title" class="block mb-2 text-sm font-medium text-gray-900">Link Title</label>
                        <input type="text" id="edit-link-title" name="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" required>
                    </div>
                    <div>
                        <label for="edit-link-url" class="block mb-2 text-sm font-medium text-gray-900">URL</label>
                        <input type="text" id="edit-link-url" name="url" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary focus:border-primary block w-full p-2.5" required>
                    </div>
                    <div class="flex items-center">
                        <input id="edit-link-active" type="checkbox" name="is_active" value="1" class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary">
                        <label for="edit-link-active" class="ml-2 text-sm font-medium text-gray-900">Active</label>
                    </div>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="submit" class="text-white bg-primary hover:bg-primary-dark focus:ring-4 focus:outline-none focus:ring-primary/30 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save Changes</button>
                    <button data-modal-hide="editLinkModal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Sortable for columns
        const columnsContainer = document.getElementById('footer-columns');
        if (columnsContainer) {
            new Sortable(columnsContainer, {
                animation: 150,
                handle: '.footer-column-item',
                onEnd: function() {
                    updatePositions();
                }
            });
        }

        // Initialize Sortable for links in each column
        document.querySelectorAll('.links-container').forEach(container => {
            new Sortable(container, {
                animation: 150,
                handle: '.footer-link-item',
                onEnd: function() {
                    updatePositions();
                }
            });
        });

        // Setup modal close buttons
        setupModalCloseButtons();
    });

    // Function to setup close buttons for all modals
    function setupModalCloseButtons() {
        // Get all buttons with data-modal-hide attribute
        const closeButtons = document.querySelectorAll('[data-modal-hide]');
        
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal-hide');
                closeModal(modalId);
            });
        });
    }

    // Function to close modal by ID
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        
        // Hide the modal
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        modal.removeAttribute('aria-modal');
        modal.removeAttribute('role');
        
        // Re-enable scrolling
        document.body.classList.remove('overflow-hidden');
    }

    function editColumn(id, title, isActive) {
        document.getElementById('edit-column-title').value = title;
        document.getElementById('edit-column-active').checked = isActive;
        document.getElementById('editColumnForm').action = "{{ route('admin.footer.columns.update', '') }}/" + id;
        
        // Open the modal using Flowbite's data attributes
        const editColumnModal = document.getElementById('editColumnModal');
        // Check if we're using Flowbite v2.x
        if (typeof window.Flowbite !== 'undefined') {
            const modalOptions = {
                onShow: () => {},
                onHide: () => {},
                onToggle: () => {}
            };
            const modal = new window.Flowbite.Modal(editColumnModal, modalOptions);
            modal.show();
        } 
        // For Flowbite v1.x or manual toggle
        else {
            editColumnModal.classList.remove('hidden');
            editColumnModal.setAttribute('aria-hidden', 'false');
            editColumnModal.setAttribute('aria-modal', 'true');
            editColumnModal.setAttribute('role', 'dialog');
            document.body.classList.add('overflow-hidden');
        }
    }

    function showAddLinkModal(columnId) {
        document.getElementById('addLinkForm').action = "{{ route('admin.footer.links.store', '') }}/" + columnId;
        
        // Open the modal
        const addLinkModal = document.getElementById('addLinkModal');
        // Check if we're using Flowbite v2.x
        if (typeof window.Flowbite !== 'undefined') {
            const modalOptions = {
                onShow: () => {},
                onHide: () => {},
                onToggle: () => {}
            };
            const modal = new window.Flowbite.Modal(addLinkModal, modalOptions);
            modal.show();
        } 
        // For Flowbite v1.x or manual toggle
        else {
            addLinkModal.classList.remove('hidden');
            addLinkModal.setAttribute('aria-hidden', 'false');
            addLinkModal.setAttribute('aria-modal', 'true');
            addLinkModal.setAttribute('role', 'dialog');
            document.body.classList.add('overflow-hidden');
        }
    }

    function editLink(id, title, url, isActive) {
        document.getElementById('edit-link-title').value = title;
        document.getElementById('edit-link-url').value = url;
        document.getElementById('edit-link-active').checked = isActive;
        document.getElementById('editLinkForm').action = "{{ route('admin.footer.links.update', '') }}/" + id;
        
        // Open the modal
        const editLinkModal = document.getElementById('editLinkModal');
        // Check if we're using Flowbite v2.x
        if (typeof window.Flowbite !== 'undefined') {
            const modalOptions = {
                onShow: () => {},
                onHide: () => {},
                onToggle: () => {}
            };
            const modal = new window.Flowbite.Modal(editLinkModal, modalOptions);
            modal.show();
        } 
        // For Flowbite v1.x or manual toggle
        else {
            editLinkModal.classList.remove('hidden');
            editLinkModal.setAttribute('aria-hidden', 'false');
            editLinkModal.setAttribute('aria-modal', 'true');
            editLinkModal.setAttribute('role', 'dialog');
            document.body.classList.add('overflow-hidden');
        }
    }

    function updatePositions() {
        const columns = [];
        
        // Get all columns and their positions
        document.querySelectorAll('.footer-column-item').forEach((column, columnIndex) => {
            const columnId = column.dataset.columnId;
            const links = [];
            
            // Get all links in this column and their positions
            column.querySelectorAll('.footer-link-item').forEach((link, linkIndex) => {
                links.push({
                    id: link.dataset.linkId,
                    position: linkIndex
                });
            });
            
            columns.push({
                id: columnId,
                position: columnIndex,
                links: links
            });
        });
        
        // Send update to server
        fetch("{{ route('admin.footer.positions.update') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ columns: columns })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Positions updated:', data);
        })
        .catch(error => {
            console.error('Error updating positions:', error);
        });
    }
</script>
@endpush
@endsection 