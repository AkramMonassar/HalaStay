import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      manifest: {
        name: 'HalaStay — منصة حجز الفنادق والشقق والقاعات',
        short_name: 'HalaStay',
        description: 'منصة حجز فنادق وشقق وقاعات بتوفر لحظي ودفع مرن',
        theme_color: '#006c35',
        background_color: '#12181f',
        display: 'standalone',
        start_url: '/',
        icons: [{ src: '/logo.svg', sizes: 'any', type: 'image/svg+xml', purpose: 'any' }],
      },
      workbox: {
        globPatterns: ['**/*.{js,css,html,svg,woff2}'],
        runtimeCaching: [
          {
            urlPattern: /^https:\/\/fonts\.googleapis\.com\/.*/i,
            handler: 'CacheFirst',
            options: { cacheName: 'google-fonts', expiration: { maxEntries: 10, maxAgeSeconds: 2592000 } },
          },
        ],
      },
    }),
  ],
  resolve: { alias: { '@': '/src' } },
})