<div class="rounded-sm border border-danger bg-danger/10 p-4">
    <div class="flex items-start">
        <span class="mr-3 flex h-8 w-8 items-center justify-center rounded-full bg-danger bg-opacity-20 text-danger">
            <i class="mdi mdi-alert text-lg"></i>
        </span>
        <div>
            <h5 class="mb-1 font-semibold text-danger">
                {{ $title ?? 'API Error' }}
            </h5>
            <p class="text-sm text-danger">
                {{ $message ?? 'An error occurred while communicating with the AI service.' }}
            </p>
            @if(isset($details))
                <details class="mt-2">
                    <summary class="text-sm cursor-pointer">More details</summary>
                    <pre class="mt-2 p-2 bg-danger/5 rounded-sm text-xs overflow-x-auto">{{ $details }}</pre>
                </details>
            @endif
        </div>
    </div>
</div>
