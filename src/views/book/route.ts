import type { RouteType } from '~/types/router'

const Layout = () => import('@/layout/index.vue')

export default {
  name: 'book',
  path: '/book',
  component: Layout,
  redirect: '/book/index',
  meta: {
    order: 1
  },
  children: [
    {
      name: '书库',
      path: 'overview',
      component: () => import('./index.vue'),
      meta: {
        title: '书库',
        icon: 'mdi:book-open-page-variant',
        role: ['admin'],
        requireAuth: true
      }
    }
  ]
} as RouteType
