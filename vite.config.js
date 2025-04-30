import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import DefineOptions from 'unplugin-vue-define-options/vite';
import i18n from 'laravel-vue-i18n/vite';
import manifestSRI from 'vite-plugin-manifest-sri';

export default defineConfig({
  plugins: [
    DefineOptions(),
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    manifestSRI(),
    i18n(),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  build: {
    manifest: true,
  },
});
