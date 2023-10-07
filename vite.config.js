import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import Unfonts from 'unplugin-fonts/vite'
import { defineConfig, splitVendorChunkPlugin } from 'vite'

export default defineConfig({
  resolve: {
    alias: [
      { find: '@', replacement: '/resources/js' },
      {
        find: /~\/([a-zA-Z]+)\/(.*)/,
        replacement: '/modules/$1/resources/js/$2',
      },
      { find: 'vue', replacement: 'vue/dist/vue.esm-bundler.js' },
    ],
  },
  server: {
    hmr: {
      host: 'localhost',
    },
  },
  plugins: [
    splitVendorChunkPlugin(),
    laravel(['resources/js/app.js', 'resources/css/contentbuilder/theme.css']),
    Unfonts({
      custom: {
        families: [
          {
            name: 'Dancing Script',
            local: 'Dancing Script',
            src: './public/fonts/DancingScript-Regular.ttf',
          },
        ],
      },
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
    VueI18nPlugin({
      compositionOnly: true,
      runtimeOnly: false,
      globalSFCScope: true,
    }),
  ],
})
