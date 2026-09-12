import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devServerUrl = new URL(env.VITE_DEV_SERVER_URL || 'http://localhost:5173');
    const port = Number(env.VITE_PORT || devServerUrl.port || 5173);

    return {
        plugins: [
            vue(),
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/voting.js',
                    'resources/js/statistics.js',
                ],
                refresh: true,
                fonts: [
                    bunny('Instrument Sans', {
                        weights: [400, 500, 600],
                    }),
                ],
            }),
            tailwindcss(),
        ],
        server: {
            host: env.VITE_HOST || '0.0.0.0',
            port,
            strictPort: true,
            origin: devServerUrl.origin,
            ws: {
                protocol: devServerUrl.protocol === 'https:' ? 'wss' : 'ws',
                host: devServerUrl.hostname,
                clientPort: port,
            },
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
