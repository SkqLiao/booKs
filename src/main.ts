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
import VueTippy from 'vue-tippy'
import 'tippy.js/dist/tippy.css'
import 'default-passive-events'

async function setupApp() {
  const app = createApp(App)
  app.use(setupCalendar, {})
  app.use(
    VueTippy,
    // optional
    {
      directive: 'tippy', // => v-tippy
      component: 'tippy', // => <tippy/>
      componentSingleton: 'tippy-singleton', // => <tippy-singleton/>,
      defaultProps: {
        placement: 'auto-end',
        allowHTML: true
      } // => Global default options * see all props
    }
  )
  app.component('VCalendar', Calendar)
  setupStore(app)
  setupNaiveDiscreteApi()
  app.config.globalProperties.$echarts = echarts
  await setupRouter(app)
  app.mount('#app')
}

setupApp()
