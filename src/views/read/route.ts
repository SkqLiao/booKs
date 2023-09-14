import type { RouteType } from '~/types/router'

const Layout = () => import('@/layout/index.vue')

export default {
  name: '阅读记录',
  path: '/read',
  component: Layout,
  redirect: '/read/index',
  meta: {
    icon: 'mdi:book',
    order: 1
  },
  children: [
    {
      name: '阅读日历',
      path: 'calendar',
      component: () => import('./cpns/statusCalendar.vue'),
      meta: {
        title: '阅读日历',
        icon: 'mdi:calendar',
        role: ['admin'],
        requireAuth: true
      }
    },
    {
      name: '阅读总览',
      path: 'panel',
      component: () => import('./panel.vue'),
      meta: {
        title: '阅读总览',
        icon: 'mdi:bookmark-check',
        role: ['admin'],
        requireAuth: true
      }
    },
    {
      name: '数据总览',
      path: 'statistics',
      component: () => import('./cpns/statusOverview.vue'),
      meta: {
        title: '数据总览',
        icon: 'mdi:database',
        role: ['admin'],
        requireAuth: true
      }
    }
  ]
} as RouteType
