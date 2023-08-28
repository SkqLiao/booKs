import '@/styles/reset.css'
import '@/styles/index.scss'
import 'uno.css'
import 'virtual:svg-icons-register'
import { createApp } from 'vue'
import App from './App.vue'
import { setupStore } from './store'
import { setupRouter } from './router'
import { setupNaiveDiscreteApi } from './utils'
import { setupCalendar, Calendar, DatePicker } from 'v-calendar'
import 'v-calendar/style.css'

async function setupApp() {
  const app = createApp(App)
  app.use(setupCalendar, {})
  app.component('VCalendar', Calendar)
  app.component('VDatePicker', DatePicker)
  setupStore(app)
  setupNaiveDiscreteApi()
  await setupRouter(app)
  app.mount('#app')
}

setupApp()
