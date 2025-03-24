import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // CSS - main
                'resources/css/app.css',

                // CSS - admin - AI dashboard
                'resources/css/admin/ai-dashboard/index_main.css',
                'resources/css/admin/ai-dashboard/playground.css',
                'resources/css/admin/ai-dashboard/stats_main.css',
                'resources/css/admin/ai-dashboard/jobs_main.css',

                // JS - main
                'resources/js/app.js',

                // JS - admin - AI dashboard
                'resources/js/admin/ai-dashboard/index_main.js',
                'resources/js/admin/ai-dashboard/playground_main.js',
                'resources/js/admin/ai-dashboard/playground_prompt_selector.js',
                'resources/js/admin/ai-dashboard/stats_main.js',
                'resources/js/admin/ai-dashboard/jobs_main.js',
                'resources/js/admin/ai-dashboard/vision_analysis_main.js',
                'resources/js/admin/ai-dashboard/vision_results_main.js'
            ],
            refresh: true,
        }),
    ],
});
