import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    proxy: {
      '/piston': {
        target: 'http://localhost:2000',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/piston/, '')
      }
    }
  }
})