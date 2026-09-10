import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/print-surat.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // server: {
    //     host: '0.0.0.0', // Mengizinkan akses dari semua IP di jaringan lokal
    //     hmr: {
    //         host: '192.168.1.8', // UBAH ke IP lokal komputer Anda
    //     },
    // },
});