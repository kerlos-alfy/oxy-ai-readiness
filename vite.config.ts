import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

/**
 * Builds `assets/react/main.tsx` into `dist/`, with a manifest so the
 * PHP side (`app/Admin/AdminServiceProvider`) can enqueue hashed
 * filenames without hardcoding them.
 */
export default defineConfig({
    plugins: [react()],
    root: '.',
    base: '',
    build: {
        outDir: 'dist',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: 'assets/react/main.tsx',
        },
    },
});
