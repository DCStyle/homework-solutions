<div class="latest-posts">
    <h2 class="text-xl font-bold text-orange-500 mb-4 text-center">{!! $title !!}</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($posts as $post)
            <a href="{{ route('posts.show', $post->slug) }}" class="flex items-center space-x-2 hover:text-blue-600">
                <span class="iconify text-xl" data-icon="mdi-chevron-right"></span>
                <span>{{ $post->title }}</span>
            </a>
        @endforeach
    </div>
</div>
