@if($prompts->isEmpty())
    <div class="flex flex-col items-center justify-center py-12">
        <div class="mb-4 rounded-full bg-gray-100 p-4">
            <svg class="w-10 h-10 text-gray-500" viewBox="0 0 24 24"><path fill="currentColor" d="M19,20H5V4H7V7H17V4H19M12,2A1,1 0 0,1 13,3A1,1 0 0,1 12,4A1,1 0 0,1 11,3A1,1 0 0,1 12,2M19,2H14.82C14.4,0.84 13.3,0 12,0C10.7,0 9.6,0.84 9.18,2H5A2,2 0 0,0 3,4V20A2,2 0 0,0 5,22H19A2,2 0 0,0 21,20V4A2,2 0 0,0 19,2Z" /></svg>
        </div>
        <p class="text-gray-500 mb-4">Chưa có mẫu prompt nào được tạo.</p>
        <button
            type="button"
            data-bs-toggle="modal"
            data-bs-target="#createPromptModal"
            class="inline-flex items-center justify-center rounded-lg border border-primary py-2 px-5 text-center font-medium text-primary hover:bg-primary hover:text-white transition-all duration-200 focus:ring-2 focus:ring-primary/30"
        >
            Tạo mẫu đầu tiên của bạn
        </button>
    </div>
@else
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3">
        @foreach($prompts as $prompt)
            <div class="group rounded-xl border border-stroke bg-white p-5 shadow-sm hover:shadow-md hover:border-primary/50 transition-all duration-200 transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                        {{ $prompt->content_type_label }}
                    </span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.ai-dashboard.playground', ['prompt_id' => $prompt->id]) }}" class="text-gray-500 hover:text-primary transition-colors">
                            <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M8,5.14V19.14L19,12.14L8,5.14Z" /></svg>
                        </a>
                        <button
                            type="button"
                            class="delete-prompt text-gray-500 hover:text-red-500 transition-colors"
                            data-prompt-id="{{ $prompt->id }}"
                        >
                            <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="currentColor" d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" /></svg>
                        </button>
                    </div>
                </div>
                <h4 class="mt-3 mb-2 text-lg font-semibold text-black group-hover:text-primary transition-colors">{{ $prompt->name }}</h4>
                <p class="text-sm text-gray-500 line-clamp-2 h-10">{{ $prompt->prompt_excerpt }}</p>
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-xs text-gray-500 flex items-center">
                        <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24"><path fill="currentColor" d="M12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22C6.47,22 2,17.5 2,12A10,10 0 0,1 12,2M12.5,7V12.25L17,14.92L16.25,16.15L11,13V7H12.5Z" /></svg>
                        {{ $prompt->formatted_created_at }}
                    </span>
                    <span class="text-xs font-medium text-black flex items-center">
                        <svg class="w-4 h-4 mr-1" viewBox="0 0 24 24"><path fill="currentColor" d="M12,2A2,2 0 0,1 14,4C14,4.74 13.6,5.39 13,5.73V7H14A7,7 0 0,1 21,14H22A1,1 0 0,1 23,15V18A1,1 0 0,1 22,19H21V20A2,2 0 0,1 19,22H5A2,2 0 0,1 3,20V19H2A1,1 0 0,1 1,18V15A1,1 0 0,1 2,14H3A7,7 0 0,1 10,7H11V5.73C10.4,5.39 10,4.74 10,4A2,2 0 0,1 12,2M7.5,13A2.5,2.5 0 0,0 5,15.5A2.5,2.5 0 0,0 7.5,18A2.5,2.5 0 0,0 10,15.5A2.5,2.5 0 0,0 7.5,13M16.5,13A2.5,2.5 0 0,0 14,15.5A2.5,2.5 0 0,0 16.5,18A2.5,2.5 0 0,0 19,15.5A2.5,2.5 0 0,0 16.5,13Z" /></svg>
                        {{ $prompt->ai_model ?? 'Any model' }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('admin.ai-dashboard.playground') }}" class="inline-flex items-center text-sm font-medium text-primary hover:underline">
            Xem tất cả các mẫu trong "Khu vực Thử Nghiệm"
            <svg class="w-4 h-4 ml-1" viewBox="0 0 24 24"><path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z" /></svg>
        </a>
    </div>
@endif
