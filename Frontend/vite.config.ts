import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },

  server: {
  host: true,
  allowedHosts: [
    'sice.up.railway.app',
    'localhost',
    '127.0.0.1'
  ],
  proxy: {                              // ← agregar esto
      '/api': {
        target: 'http://127.0.0.1:8000', // ← puerto de php artisan serve
        changeOrigin: true,
        secure: false,
      }
    }
},
preview: {
  host: true,
  allowedHosts: [
    'sice.up.railway.app',
    'localhost',
    '127.0.0.1'
  ]
}
})