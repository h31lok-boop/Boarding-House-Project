import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                // Laravel Herd serves .mjs files as application/octet-stream.
                // Emit module worker assets as .js so Chrome will execute the
                // MapLibre GeoJSON worker used by route and radius layers.
                assetFileNames: (assetInfo) => {
                    const originalName = assetInfo.names?.[0] ?? assetInfo.name ?? '';

                    return originalName.endsWith('.mjs')
                        ? 'assets/[name]-[hash].js'
                        : 'assets/[name]-[hash][extname]';
                },
            },
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
