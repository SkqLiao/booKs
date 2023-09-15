import type { RouteType } from '~/types/router'

const Layout = () => import('@/layout/index.vue')

export default {
  name: 'Buy',
  path: '/',
  component: Layout,
  redirect: '/buy',
  meta: {
    order: 2
  },
  children: [
    {
      name: 'Buy',
      path: 'buy',
      component: () => import('./index.vue'),
      meta: {
        title: '购书记录',
        icon: 'mdi:shopping'
      }
    }
  ]
} as RouteType
