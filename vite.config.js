import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel([
            'resources/js/app-secondary.js',
            'resources/js/app.js',
        ]),
    ],
});
