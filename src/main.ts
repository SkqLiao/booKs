import '@/styles/reset.css'
import '@/styles/index.scss'
import 'uno.css'
import 'virtual:svg-icons-register'
import { createApp } from 'vue'
import App from './App.vue'
import { setupStore } from './store'
import { setupRouter } from './router'
import { setupNaiveDiscreteApi } from './utils'
import { setupCalendar, Calendar } from 'v-calendar'
import 'v-calendar/style.css'
import * as echarts from 'echarts'

async function setupApp() {
  const app = createApp(App)
  app.use(setupCalendar, {})
  app.component('VCalendar', Calendar)
  setupStore(app)
  setupNaiveDiscreteApi()
  app.config.globalProperties.$echarts = echarts
  await setupRouter(app)
  app.mount('#app')
}

setupApp()
