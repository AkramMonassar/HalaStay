import { createApp } from 'vue'
import { createPinia } from 'pinia'
import VueApexCharts from 'vue3-apexcharts'
import App from './App.vue'
import router from './router'
import { i18n } from './i18n'
import { useLangStore } from './stores/lang'
import './assets/styles/main.scss'

const pinia = createPinia()
const app = createApp(App)

app.use(pinia)
app.use(router)
app.use(i18n)
app.use(VueApexCharts)

useLangStore(pinia).apply()

app.mount('#app')