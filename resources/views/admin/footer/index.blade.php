@extends('admin_layouts.admin')

@section('title', 'Footer Management')

@section('content')
    <!-- Main Container -->
    <div class="container mx-auto p-4">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 flex flex-wrap items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                    <h1 class="text-2xl font-bold text-gray-800 ">Footer Management</h1>
                </div>
                <button id="addColumnBtn" type="button"
                        class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add New Column
                </button>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mx-6 mt-4 p-4 bg-green-50 border-l-4 border-green-500 dark:border-green-400 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500 " fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 ">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tabs -->
            <div class="border-b border-gray-200 ">
                <nav class="flex px-6" aria-label="Tabs">
                    <button id="previewTab" class="px-3 py-4 text-sm font-medium border-b-2 border-primary text-primary" aria-current="page">
                        Preview
                    </button>
                    <button id="manageTab" class="px-3 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent">
                        Manage Columns
                    </button>
                </nav>
            </div>

            <!-- Content Sections -->
            <div class="p-6">
                <!-- Preview Section -->
                <div id="previewSection" class="mb-8">
                    <div class="flex items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800 ">Footer Preview</h2>
                        <div class="ml-2 bg-gray-100 text-xs px-2 py-1 rounded-full text-gray-500 dark:text-gray-400">
                            Showing active items only
                        </div>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                        @php
                            $activeColumns = $columns->where('is_active', true);
                            $columnCount = $activeColumns->count();

                            // Determine grid classes based on column count
                            $gridClass = 'grid-cols-1 sm:grid-cols-2';

                            if ($columnCount <= 1) {
                                $gridClass = 'grid-cols-1'; // Always 1 column for 1 active column
                            } elseif ($columnCount == 2) {
                                $gridClass = 'grid-cols-1 sm:grid-cols-2'; // 1 on small, 2 on medium+
                            } elseif ($columnCount == 3) {
                                $gridClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3'; // 1, 2, 3
                            } elseif ($columnCount == 4) {
                                $gridClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4'; // 1, 2, 4
                            } elseif ($columnCount <= 6) {
                                $gridClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-' . $columnCount; // 1, 2, 3, actual count on xl
                            } else {
                                $gridClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-' . min(6, $columnCount); // 1, 2, 4, max 6 on xl
                            }
                        @endphp

                        <div class="grid {{ $gridClass }} gap-8">
                            @foreach($activeColumns as $column)
                                <div class="flex flex-col">
                                    <h4 class="text-purple-600 font-semibold mb-4 text-lg">{{ $column->title }}</h4>
                                    <ul class="space-y-3">
                                        @foreach($column->links->where('is_active', true) as $link)
                                            <li>
                                                <a href="{{ $link->url }}" class="text-gray-600 hover:text-purple-600 dark:hover:text-purple-400 transition-colors duration-200">
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

                <!-- Manage Columns Section -->
                <div id="manageSection" class="hidden">
                    <div class="flex items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800 ">Manage Footer Columns</h2>
                        <div class="ml-2 bg-blue-100 text-xs px-2 py-1 rounded-full text-blue-600 dark:text-blue-400">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                </svg>
                                Drag to reorder
                            </div>
                        </div>
                    </div>

                    <div id="footer-columns" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($columns as $column)
                            <div class="footer-column-item bg-white border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200" data-column-id="{{ $column->id }}">
                                <!-- Column Header -->
                                <div class="p-4 border-b border-gray-200 flex justify-between items-center cursor-move bg-gray-50 dark:bg-gray-700/50 rounded-t-lg">
                                    <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-md bg-primary/10 text-primary mr-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                        </svg>
                                    </span>
                                        <h3 class="font-medium text-gray-900 ">{{ $column->title }}</h3>
                                    </div>
                                    <div class="flex space-x-1">
                                        <button type="button" onclick="editColumn({{ $column->id }}, '{{ $column->title }}', {{ $column->is_active ? 'true' : 'false' }})"
                                                class="p-2 text-gray-500 hover:text-primary hover:bg-gray-100 rounded-md transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.footer.columns.destroy', $column) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete(this.parentNode)"
                                                    class="p-2 text-gray-500 hover:text-red-500 hover:bg-gray-100 rounded-md transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <!-- Column Body -->
                                <div class="p-4">
                                    <div class="flex items-center mb-4">
                                        <span class="text-sm mr-2">Status:</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $column->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                        {{ $column->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    </div>

                                    <!-- Links Section -->
                                    <div>
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Links ({{ $column->links->count() }})
                                            </h4>
                                            <button type="button" onclick="showAddLinkModal({{ $column->id }})"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-primary hover:bg-primary/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                Add Link
                                            </button>
                                        </div>

                                        <!-- Empty State -->
                                        @if($column->links->count() === 0)
                                            <div class="flex flex-col items-center justify-center p-4 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                </svg>
                                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No links yet</p>
                                                <button type="button" onclick="showAddLinkModal({{ $column->id }})"
                                                        class="mt-2 inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-primary hover:bg-primary/90">
                                                    Add First Link
                                                </button>
                                            </div>
                                        @else
                                            <ul class="space-y-2 links-container" data-column-id="{{ $column->id }}">
                                                @foreach($column->links as $link)
                                                    <li class="footer-link-item flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800/50 rounded-md border border-gray-200 dark:border-gray-700" data-link-id="{{ $link->id }}">
                                                        <!-- Drag handle -->
                                                        <div class="drag-handle flex items-center mr-2 px-2 py-4 -my-3 -ml-3 rounded-l-md cursor-move bg-gray-100 dark:bg-gray-700/50 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                                            </svg>
                                                        </div>

                                                        <div class="grid grid-cols-1 gap-1 flex-1">
                                                            <div class="flex items-center">
                                                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $link->title }}</span>
                                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $link->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                                                                {{ $link->is_active ? 'Active' : 'Inactive' }}
                                                            </span>
                                                            </div>
                                                            <a href="{{ $link->url }}" target="_blank" class="text-xs text-blue-600 hover:underline flex items-center">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                                </svg>
                                                                {{ $link->url }}
                                                            </a>
                                                        </div>
                                                        <div class="flex space-x-1 ml-2">
                                                            <button type="button" onclick="editLink({{ $link->id }}, '{{ $link->title }}', '{{ $link->url }}', {{ $link->is_active ? 'true' : 'false' }})"
                                                                    class="p-1.5 text-gray-500 hover:text-primary hover:bg-gray-200 dark:hover:bg-gray-700 rounded-md transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                                </svg>
                                                            </button>
                                                            <form action="{{ route('admin.footer.links.destroy', $link) }}" method="POST" class="inline-block">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" onclick="confirmDelete(this.parentNode)"
                                                                        class="p-1.5 text-gray-500 hover:text-red-500 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-md transition-colors">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Empty State for No Columns -->
                    @if($columns->count() === 0)
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-8 text-center">
                            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 text-blue-600 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Footer Columns Yet</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">Get started by creating your first footer column.</p>
                            <button id="emptyStateAddBtn" type="button"
                                    class="inline-flex items-center px-4 py-2 bg-primary text-white text-sm font-medium rounded-md shadow-sm hover:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add First Column
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add Column Modal -->
    <div id="addColumnModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary/10 sm:mx-0 sm:h-10 sm:w-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Add New Footer Column
                            </h3>
                            <div class="mt-4">
                                <form id="addColumnForm" action="{{ route('admin.footer.columns.store') }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="column-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Column Title</label>
                                        <input type="text" id="column-title" name="title" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" placeholder="e.g. Quick Links, Company, Support" required>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="column-active" type="checkbox" name="is_active" value="1" checked class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-600 rounded">
                                        <label for="column-active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                            Active (visible on site)
                                        </label>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="document.getElementById('addColumnForm').submit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                        Add Column
                    </button>
                    <button type="button" onclick="closeModal('addColumnModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 dark:focus:ring-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Column Modal -->
    <div id="editColumnModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Edit Footer Column
                            </h3>
                            <div class="mt-4">
                                <form id="editColumnForm" action="" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-4">
                                        <label for="edit-column-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Column Title</label>
                                        <input type="text" id="edit-column-title" name="title" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" required>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="edit-column-active" type="checkbox" name="is_active" value="1" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-600 rounded">
                                        <label for="edit-column-active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                            Active (visible on site)
                                        </label>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="document.getElementById('editColumnForm').submit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                        Save Changes
                    </button>
                    <button type="button" onclick="closeModal('editColumnModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 dark:focus:ring-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Link Modal -->
    <div id="addLinkModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-primary/10 sm:mx-0 sm:h-10 sm:w-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Add New Link
                            </h3>
                            <div class="mt-4">
                                <form id="addLinkForm" action="" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="link-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Link Text</label>
                                        <input type="text" id="link-title" name="title" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" placeholder="e.g. About Us, Contact" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="link-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                                        <input type="text" id="link-url" name="url" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" placeholder="e.g. /about, https://example.com" required>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="link-active" type="checkbox" name="is_active" value="1" checked class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-600 rounded">
                                        <label for="link-active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                            Active (visible on site)
                                        </label>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="document.getElementById('addLinkForm').submit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                        Add Link
                    </button>
                    <button type="button" onclick="closeModal('addLinkModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 dark:focus:ring-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Link Modal -->
    <div id="editLinkModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Edit Link
                            </h3>
                            <div class="mt-4">
                                <form id="editLinkForm" action="" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="mb-4">
                                        <label for="edit-link-title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Link Text</label>
                                        <input type="text" id="edit-link-title" name="title" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" required>
                                    </div>
                                    <div class="mb-4">
                                        <label for="edit-link-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL</label>
                                        <input type="text" id="edit-link-url" name="url" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" required>
                                    </div>
                                    <div class="flex items-center">
                                        <input id="edit-link-active" type="checkbox" name="is_active" value="1" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-600 rounded">
                                        <label for="edit-link-active" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                            Active (visible on site)
                                        </label>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="document.getElementById('editLinkForm').submit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/80 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm">
                        Save Changes
                    </button>
                    <button type="button" onclick="closeModal('editLinkModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 dark:focus:ring-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Confirm Deletion
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400" id="confirm-message">
                                    Are you sure you want to delete this item? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="confirmDeleteBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button type="button" onclick="closeModal('confirmModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 dark:focus:ring-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
        <script>
            // Store the current form being confirmed for deletion
            let currentDeleteForm = null;

            document.addEventListener('DOMContentLoaded', function() {
                // Initialize tab functionality
                setupTabs();

                // Add event listeners for modal buttons
                setupModalButtons();

                // Initialize Sortable for columns
                initSortable();
            });

            function setupTabs() {
                const previewTab = document.getElementById('previewTab');
                const manageTab = document.getElementById('manageTab');
                const previewSection = document.getElementById('previewSection');
                const manageSection = document.getElementById('manageSection');

                previewTab.addEventListener('click', function() {
                    previewTab.classList.add('border-primary', 'text-primary');
                    previewTab.classList.remove('text-gray-500', 'border-transparent');
                    manageTab.classList.remove('border-primary', 'text-primary');
                    manageTab.classList.add('text-gray-500', 'border-transparent');

                    previewSection.classList.remove('hidden');
                    manageSection.classList.add('hidden');
                });

                manageTab.addEventListener('click', function() {
                    manageTab.classList.add('border-primary', 'text-primary');
                    manageTab.classList.remove('text-gray-500', 'border-transparent');
                    previewTab.classList.remove('border-primary', 'text-primary');
                    previewTab.classList.add('text-gray-500', 'border-transparent');

                    manageSection.classList.remove('hidden');
                    previewSection.classList.add('hidden');
                });

                // Show manage tab by default (since that's where most actions happen)
                manageTab.click();
            }

            function setupModalButtons() {
                // Add Column button
                document.getElementById('addColumnBtn').addEventListener('click', function() {
                    openModal('addColumnModal');
                });

                // Empty state Add Column button (if exists)
                const emptyStateBtn = document.getElementById('emptyStateAddBtn');
                if (emptyStateBtn) {
                    emptyStateBtn.addEventListener('click', function() {
                        openModal('addColumnModal');
                    });
                }

                // Set up confirm delete button
                document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                    if (currentDeleteForm) {
                        currentDeleteForm.submit();
                    }
                });
            }

            function initSortable() {
                // Initialize Sortable for columns
                const columnsContainer = document.getElementById('footer-columns');
                if (columnsContainer) {
                    new Sortable(columnsContainer, {
                        animation: 150,
                        handle: '.footer-column-item',
                        ghostClass: 'sortable-ghost', // Using a single class name
                        onEnd: function() {
                            updatePositions();
                        }
                    });
                }

                // Initialize Sortable for links in each column
                document.querySelectorAll('.links-container').forEach(container => {
                    new Sortable(container, {
                        animation: 150,
                        handle: '.drag-handle', // Use the dedicated drag handle
                        ghostClass: 'sortable-ghost', // Using a single class name
                        onEnd: function() {
                            updatePositions();
                        }
                    });
                });
            }

            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                if (!modal) return;

                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (!modal) return;

                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            function editColumn(id, title, isActive) {
                document.getElementById('edit-column-title').value = title;
                document.getElementById('edit-column-active').checked = isActive;
                const baseUrl = "{{ route('admin.footer.columns.update', ':id') }}";
                document.getElementById('editColumnForm').action = baseUrl.replace(':id', id);

                openModal('editColumnModal');
            }

            function showAddLinkModal(columnId) {
                const baseUrl = "{{ route('admin.footer.links.store', ':columnId') }}";
                document.getElementById('addLinkForm').action = baseUrl.replace(':columnId', columnId);
                openModal('addLinkModal');
            }

            function editLink(id, title, url, isActive) {
                document.getElementById('edit-link-title').value = title;
                document.getElementById('edit-link-url').value = url;
                document.getElementById('edit-link-active').checked = isActive;
                const baseUrl = "{{ route('admin.footer.links.update', ':id') }}";
                document.getElementById('editLinkForm').action = baseUrl.replace(':id', id);

                openModal('editLinkModal');
            }

            function confirmDelete(form) {
                currentDeleteForm = form;
                openModal('confirmModal');
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
                        showToast('Positions updated successfully');
                    })
                    .catch(error => {
                        console.error('Error updating positions:', error);
                        showToast('Error updating positions', true);
                    });
            }

            // Simple toast notification
            function showToast(message, isError = false) {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-4 right-4 px-4 py-2 rounded-md text-white ${isError ? 'bg-red-500' : 'bg-green-500'} shadow-lg z-50 transition-opacity duration-300`;
                toast.textContent = message;
                document.body.appendChild(toast);

                setTimeout(() => {
                    toast.classList.add('opacity-0');
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }
        </script>
    @endpush
@endsection
