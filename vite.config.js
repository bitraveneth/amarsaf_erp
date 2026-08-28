import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/learning-hub.css', 'resources/js/app.js', 'resources/js/auth-login.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: [
                '**/storage/framework/sessions/**',
                '**/storage/framework/cache/**',
                '**/storage/framework/views/**',
                '**/storage/logs/**',
            ],
        },
    },
});
