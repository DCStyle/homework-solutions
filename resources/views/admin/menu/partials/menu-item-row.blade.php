<tr class="menu-item" data-item-id="{{ $item->id }}" data-parent-id="{{ $item->parent_id }}">
    <td class="px-6 py-4 whitespace-nowrap w-10">
        <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
            </svg>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center">
            @if($level > 0)
                <div class="nested-line"></div>
                <div class="nested-indicator"></div>
            @endif
            <div class="ml-{{ $level * 8 }} flex items-center">
                @if($item->icon)
                    <span class="mr-2">{!! $item->icon !!}</span>
                @endif
                <span class="text-sm font-medium text-gray-900">{{ $item->name }}</span>
            </div>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
            {{ $item->type }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        {{ $item->url }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-center">
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
            {{ $item->active ? 'Hoạt động' : 'Vô hiệu' }}
        </span>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
        <div class="flex justify-center space-x-2">
            <a href="{{ route('admin.menu.edit', $item->id) }}"
               class="text-indigo-600 hover:text-indigo-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </a>
            <form action="{{ route('admin.menu.destroy', $item->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-900"
                        onclick="return confirm('Bạn có chắc chắn muốn xóa menu item này?')">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </form>
        </div>
    </td>
</tr>

@if($item->children->count() > 0)
    @foreach($item->children as $child)
        @include('admin.menu.partials.menu-item-row', ['item' => $child, 'level' => $level + 1])
    @endforeach
@endif
