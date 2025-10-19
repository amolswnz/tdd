import { defineConfig } from 'vite';
// import react from '@vitejs/plugin-react'; // Uncomment for React
// import vue from '@vitejs/plugin-vue';    // Uncomment for Vue
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  // plugins: [react()], // Add plugins as needed
  root: 'themes/app',   // Source folder
  build: {
    outDir: 'dist', // Output to public/dist
    manifest: true, // Generate manifest.json for SilverStripe
    assetsDir: '',      // Set to empty string to remove assets folder
    rollupOptions: {
      input: {
        main: 'themes/app/src/js/main.js', // JS entry point
        styles: 'themes/app/src/css/styles.css', // CSS entry point
      },
      output: {
        entryFileNames: '[name].js', // Output JS files directly
        chunkFileNames: '[name].js', // Output chunks directly
        assetFileNames: '[name][extname]', // Output CSS/other assets directly
      },
    },
  },
  plugins: [
    tailwindcss(),
  ],
  server: {
    port: 3000, // Vite dev server port
    host: 'localhost',
    hmr: {
      protocol: 'ws', // WebSocket for Hot Module Replacement
    },
  },
});
