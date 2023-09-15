import type { RouteType } from '~/types/router'

const Layout = () => import('@/layout/index.vue')

export default {
  name: 'Carousel',
  path: '/',
  component: Layout,
  redirect: '/carousel',
  meta: {
    order: 2
  },
  children: [
    {
      name: 'Carousel',
      path: 'Carousel',
      component: () => import('./index.vue'),
      meta: {
        title: '轮播图',
        icon: 'mdi:picture-in-picture-bottom-right'
      }
    }
  ]
} as RouteType
