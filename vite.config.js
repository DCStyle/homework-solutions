import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // CSS - main
                'resources/css/app.css',
                'resources/css/tinymce-display.css',

                // CSS - admin - AI dashboard
                'resources/css/admin/ai-dashboard/index_main.css',
                'resources/css/admin/ai-dashboard/playground.css',
                'resources/css/admin/ai-dashboard/stats_main.css',
                'resources/css/admin/ai-dashboard/jobs_main.css',

                // CSS - public - Wiki
                'resources/css/public/wiki/chat.css',
                'resources/css/public/wiki/live_search.css',

                // JS - main
                'resources/js/app.js',

                // JS - admin - AI dashboard
                'resources/js/admin/ai-dashboard/index_main.js',
                'resources/js/admin/ai-dashboard/playground_main.js',
                'resources/js/admin/ai-dashboard/playground_generate.js',
                'resources/js/admin/ai-dashboard/playground_prompt_selector.js',
                'resources/js/admin/ai-dashboard/stats_main.js',
                'resources/js/admin/ai-dashboard/jobs_main.js',

                // JS - admin - AI API keys
                'resources/js/admin/ai-api-keys/index_main.js',

                // JS - public - Wiki
                'resources/js/public/wiki/chat.js',
                'resources/js/public/wiki/comments.js',
                'resources/js/public/wiki/feed.js',
                'resources/js/public/wiki/live_search.js',
                'resources/js/public/wiki/wiki-utils.js',
                'resources/js/public/wiki/question.js',
            ],
            refresh: true,
        }),
    ],
});
