import type { RouteModule, RouteType, RoutesType } from '~/types/router'

const Layout = () => import('@/layout/index.vue')

export const basicRoutes: RoutesType = [
  {
    name: '404',
    path: '/404',
    component: () => import('@/views/error-page/404.vue'),
    isHidden: true
  },
  {
    name: 'Login',
    path: '/login',
    component: () => import('@/views/login/index.vue'),
    isHidden: true,
    meta: {
      title: '登录页'
    }
  },
  {
    name: 'ExternalLink',
    path: '/external-link',
    component: Layout,
    meta: {
      title: '外部链接',
      icon: 'mdi:link-variant',
      order: 3
    },
    children: [
      {
        name: 'LinkGithubSrc',
        path: 'https://github.com/SkqLiao/booKs',
        component: () => {},
        meta: {
          title: '源代码',
          icon: 'mdi:github'
        }
      }
    ]
  }
]

export const NOT_FOUND_ROUTE: RouteType = {
  name: 'NotFound',
  path: '/:pathMatch(.*)*',
  redirect: '/404',
  isHidden: true
}

export const EMPTY_ROUTE: RouteType = {
  name: 'Empty',
  path: '/:pathMatch(.*)*',
  component: () => {}
}

const modules = import.meta.glob('@/views/**/route.ts', {
  eager: true
}) as RouteModule
const asyncRoutes: RoutesType = []
Object.keys(modules).forEach((key) => {
  asyncRoutes.push(modules[key].default)
})

export { asyncRoutes }
