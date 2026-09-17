import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `@import "@/assets/styles/_tokens.scss";`
      }
    }
  },
  resolve: {
    alias: {
      '@': '/src'
    }
  }
})