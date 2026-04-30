import { createRouter, createWebHistory } from 'vue-router'
import Layout from '@/layout/index.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/login/index.vue'),
      meta: {
        title: '登录'
      }
    },
    {
      path: '/',
      name: 'Layout',
      component: Layout,
      meta: {
        title: '后台管理'
      },
      children: [
        {
          path: '',
          name: 'Dashboard',
          component: () => import('@/views/dashboard/index.vue'),
          meta: {
            title: '仪表盘'
          }
        },
        {
          path: 'dashboard/overview',
          name: 'DashboardOverview',
          component: () => import('@/views/dashboard/overview.vue'),
          meta: {
            title: '概览'
          }
        },
        {
          path: 'dashboard/analytics',
          name: 'DashboardAnalytics',
          component: () => import('@/views/dashboard/analytics.vue'),
          meta: {
            title: '数据分析'
          }
        },
        {
          path: 'system/user',
          name: 'SystemUser',
          component: () => import('@/views/system/user.vue'),
          meta: {
            title: '用户管理'
          }
        },
        {
          path: 'system/role',
          name: 'SystemRole',
          component: () => import('@/views/system/role.vue'),
          meta: {
            title: '角色管理'
          }
        },
        {
          path: 'system/permission',
          name: 'SystemPermission',
          component: () => import('@/views/system/permission.vue'),
          meta: {
            title: '权限管理'
          }
        },
        {
          path: 'system/object',
          name: 'SystemObject',
          component: () => import('@/views/system/object.vue'),
          meta: {
            title: '对象管理'
          }
        },
        {
          path: 'system/menu',
          name: 'SystemMenu',
          component: () => import('@/views/system/menu.vue'),
          meta: {
            title: '栏目管理'
          }
        },
        {
          path: 'system/page',
          name: 'SystemPage',
          component: () => import('@/views/system/page.vue'),
          meta: {
            title: '页面管理'
          }
        },

        {
          path: 'settings',
          name: 'Settings',
          component: () => import('@/views/settings/index.vue'),
          meta: {
            title: '系统设置'
          }
        },
        {
          path: 'page/list',
          name: 'ObjectList',
          component: () => import('@/views/page/objectList.vue'),
          meta: {
            title: '列表页面'
          }
        }
      ]
    }
  ]
})

export default router