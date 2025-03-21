import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/admin/ai-dashboard/index_main.js',
                'resources/js/admin/ai-dashboard/playground_main.js',
                'resources/js/admin/ai-dashboard/playground_prompt_selector.js',
                'resources/js/admin/ai-dashboard/stats_main.js',
                'resources/js/admin/ai-dashboard/vision_analysis_main.js',
                'resources/js/admin/ai-dashboard/vision_results_main.js'
            ],
            refresh: true,
        }),
    ],
});
