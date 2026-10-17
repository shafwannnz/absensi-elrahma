import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            // Setiap halaman yang butuh JS khusus punya entry sendiri (tidak monolitik).
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pages/attendance.js',
                'resources/js/pages/enroll.js',
                'resources/js/pages/settings-map.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: { ignored: ['**/storage/framework/views/**'] },
    },
});
