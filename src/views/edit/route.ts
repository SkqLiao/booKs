import type { RouteType } from '~/types/router'

const Layout = () => import('@/layout/index.vue')

export default {
  name: 'add book',
  path: '/addbook',
  component: Layout,
  redirect: '',
  meta: {
    order: 1
  },
  children: [
    {
      name: '增加图书',
      path: '',
      component: () => import('./addBook.vue'),
      meta: {
        title: '增加图书',
        icon: 'mdi:book-plus',
        role: ['admin'],
        requireAuth: true
      }
    }
  ]
} as RouteType
