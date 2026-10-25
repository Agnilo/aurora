import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        port: 5300,
        strictPort: true,
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/frontend/app.css',
                'resources/css/frontend/pages/welcome.css',
                'resources/css/frontend/pages/auth.css',
                'resources/css/theme.css',
                'resources/css/admin/admin.css',
                'resources/css/admin/layout.css',
                'resources/css/admin/sidebar.css',
                'resources/css/admin/dashboard.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});


