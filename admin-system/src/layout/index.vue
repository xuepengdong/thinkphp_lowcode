<template>
  <a-layout style="min-height: 100vh; height: 100%;">
    <a-layout-sider width="260" theme="light" collapsible v-model:collapsed="collapsed">
      <div class="logo">
        <img src="@/assets/vite.svg" v-if="!collapsed" alt="Logo" style="height: 32px; margin: 16px 16px 16px 24px;" />
        <div v-else style="width: 32px; height: 32px; margin: 16px auto; background: #1890ff; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">A</div>
      </div>
      <a-menu
        theme="light"
        mode="inline"
        :items="menuItems"
        @click="handleMenuClick"
        :selected-keys="[currentRoute]"
        :default-open-keys="[]"
        :open-keys="openKeys"
        @open-change="handleOpenChange"
      />
    </a-layout-sider>
    <a-layout>
      <a-layout-header style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #8b5cf6 100%); padding: 0 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 20px rgba(30, 58, 138, 0.4); z-index: 1; height: 64px; line-height: 64px;">
        <div style="display: flex; align-items: center;">
          <MenuUnfoldOutlined v-if="collapsed" @click="toggleCollapsed" style="font-size: 18px; cursor: pointer; margin-right: 16px; color: white;" />
          <MenuFoldOutlined v-else @click="toggleCollapsed" style="font-size: 18px; cursor: pointer; margin-right: 16px; color: white;" />
          
          <!-- 固定栏目标签区域 -->
          <div v-if="fixedTabs.length > 0" class="fixed-tabs">
            <div 
              v-for="tab in fixedTabs" 
              :key="tab.key" 
              class="fixed-tab"
              :class="{ active: currentRoute === tab.key }"
              @click="handleTabClick(tab)"
            >
              <span>{{ tab.label }}</span>
              <span class="tab-close" @click.stop="removeTab(tab.key)">×</span>
            </div>
          </div>
          

        </div>
        
        <div>
          <a-dropdown>
            <a-button type="text" style="padding: 4px 12px;">
              <span style="display: flex; align-items: center; color: white;">
                <UserOutlined style="margin-right: 8px;" />
                {{ userInfo.username || '未登录' }}
                <DownOutlined style="margin-left: 8px;" />
              </span>
            </a-button>
            <template #overlay>
              <a-menu>
                <a-menu-item @click="handleProfile">
                  <UserOutlined />
                  <span>个人资料</span>
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item @click="handleLogout">
                  <LogoutOutlined />
                  <span>退出登录</span>
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </div>
      </a-layout-header>
      
      <a-layout-content style="margin: 0; padding: 0; background: #f5f5f5; min-height: calc(100vh - 64px); overflow: auto;">
        <div style="padding: 16px; width: 100%; min-height: 100%;">
          <router-view />
        </div>
      </a-layout-content>
    </a-layout>
  </a-layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { DownOutlined, MenuUnfoldOutlined, MenuFoldOutlined, UserOutlined, LogoutOutlined } from '@ant-design/icons-vue'
import { useUserStore } from '@/store/user'
import request from '@/utils/request'
import { Breadcrumb, BreadcrumbItem } from 'ant-design-vue'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const collapsed = ref(false)

const toggleCollapsed = () => {
  collapsed.value = !collapsed.value
}

const menuItems = ref([])
const openKeys = ref([])
const currentRoute = computed(() => route.path)
const fixedTabs = ref([])

// 根据当前路由获取完整的面包屑路径
const breadcrumbTitle = computed(() => {
    // 首先尝试从菜单数据中查找路径
    const findMenuPath = (menus, currentPath, path = []) => {
      for (const menu of menus) {
        if (menu.key === currentPath) {
          return [...path, menu.label]
        }
        if (menu.children && menu.children.length > 0) {
          const found = findMenuPath(menu.children, currentPath, [...path, menu.label])
          if (found) return found
        }
      }
      return null
    }
    
    const path = findMenuPath(menuItems.value, currentRoute.value)
    
    if (path && path.length > 0) {
      return path.join(' / ')
    }
    
    // 如果没有找到，使用默认映射
    const routeMap = {
      '/': '仪表盘',
      '/dashboard/overview': '仪表盘 / 概览',
      '/dashboard/analytics': '仪表盘 / 数据分析',
      '/system/user': '系统管理 / 用户管理',
      '/system/role': '系统管理 / 角色管理',
      '/system/permission': '系统管理 / 权限管理',
      '/system/object': '系统管理 / 对象管理',
      '/system/menu': '系统管理 / 栏目管理',
      '/system/page': '系统管理 / 页面管理',
      '/settings': '系统设置',
    }
    return routeMap[currentRoute.value] || '仪表盘'
  })

const userInfo = computed(() => userStore.userInfo)

const handleMenuClick = (e) => {
  // 判断是否是动态栏目（key是数字ID）还是系统菜单（key是路由路径）
  const isDynamicMenu = !isNaN(parseInt(e.key))
  
  let routePath = e.key
  let tabKey = e.key
  
  if (isDynamicMenu) {
    // 动态栏目，跳转到统一的列表页面，菜单ID通过localStorage传递
    localStorage.setItem('currentMenuId', e.key)
    routePath = '/page/list'
    tabKey = '/page/list'
  }
  
  router.push(routePath)
  
  // 查找菜单项的完整路径
  const findMenuItem = (menus, key) => {
    for (const menu of menus) {
      if (menu.key === key) {
        return menu.label
      }
      if (menu.children && menu.children.length > 0) {
        const found = findMenuItem(menu.children, key)
        if (found) return found
      }
    }
    return null
  }
  
  const label = findMenuItem(menuItems.value, e.key) || e.key
  
  // 如果该标签已存在，不重复添加
  const exists = fixedTabs.value.some(tab => tab.key === tabKey)
  if (!exists) {
    fixedTabs.value.push({
      key: tabKey,
      label: label
    })
  }
}

// 点击标签切换路由
const handleTabClick = (tab) => {
  router.push(tab.key)
}

// 删除标签
const removeTab = (key) => {
  fixedTabs.value = fixedTabs.value.filter(tab => tab.key !== key)
}

const handleOpenChange = (keys) => {
  openKeys.value = keys
}

const handleLogout = () => {
  localStorage.removeItem('access_token')
  userStore.setUserInfo({})
  router.push('/login')
}

const handleProfile = () => {
  // 处理个人资料页面跳转
  console.log('跳转到个人资料页面')
}

const fetchMenu = async () => {
  try {
    const response = await request.get('/api/menu/tree')
    if (response.code === 200) {
      // 转换菜单数据格式，适配 Ant Design Vue 的菜单组件
      const convertMenuData = (menus) => {
        return menus.filter(menu => {
          // 过滤掉乱码菜单项
          const isGarbled = /[\x00-\x1F\x7F-\xFF]/.test(menu.name) || menu.name.includes('乱码')
          // 过滤掉根级别的对象管理菜单项，只保留系统管理下的对象管理
          const isRootObjectManagement = menu.parent_id === 0 && menu.name === '对象管理'
          // 过滤掉内容管理、文章管理、分类管理栏目
          const isContentMenu = ['内容管理', '文章管理', '分类管理'].includes(menu.name)
          return !isGarbled && !isRootObjectManagement && !isContentMenu
        }).map(menu => {
          const menuItem = {
            key: menu.path || menu.id.toString(),
            label: menu.name
          }
          if (menu.children && menu.children.length > 0) {
            menuItem.children = convertMenuData(menu.children)
          }
          return menuItem
        })
      }
      console.log('原始菜单数据:', response.data)
      const convertedMenu = convertMenuData(response.data)
      console.log('转换后的菜单数据:', convertedMenu)
      menuItems.value = convertedMenu
      // 重置openKeys，确保菜单默认折叠
      openKeys.value = []
    }
  } catch (error) {
    console.error('获取菜单失败:', error)
    // 如果API调用失败，使用默认菜单
    menuItems.value = [
      {
        key: '/',
        label: '仪表盘',
        children: [
          {
            key: '/dashboard/overview',
            label: '概览'
          },
          {
            key: '/dashboard/analytics',
            label: '数据分析'
          }
        ]
      },
      {
        key: '/system',
        label: '系统管理',
        children: [
          {
            key: '/system/user',
            label: '用户管理'
          },
          {
            key: '/system/role',
            label: '角色管理'
          },
          {
            key: '/system/permission',
            label: '权限管理'
          },
          {
            key: '/system/object',
            label: '对象管理'
          },
          {
            key: '/system/menu',
            label: '栏目管理'
          },
          {
            key: '/system/page',
            label: '页面管理'
          }
        ]
      },
      {
        key: '/settings',
        label: '系统设置'
      }
    ]
  }
}

const fetchUserInfo = async () => {
  try {
    const res = await request.get('/api/user/info')
    userStore.setUserInfo(res.user)
  } catch (error) {
    console.error('获取用户信息失败:', error)
    // 如果API不可用，使用默认用户信息
    userStore.setUserInfo({
      username: 'admin',
      name: '管理员'
    })
  }
}

onMounted(() => {
  fetchMenu()
  fetchUserInfo()
})
</script>

<style scoped>
.logo {
  height: 64px;
  display: flex;
  align-items: center;
  padding: 0 16px;
  border-bottom: 1px solid #f0f0f0;
  transition: all 0.3s;
}

.logo img {
  max-height: 40px;
  transition: all 0.3s;
}

.ant-layout-sider-collapsed .logo {
  justify-content: center;
}

.ant-layout-sider-collapsed .logo img {
  margin: 0;
}

.site-layout-background {
  background: #fff;
}

.page-title {
  font-size: 24px;
  font-weight: 600;
  color: #1f2d3d;
  margin-bottom: 24px;
}

.fixed-tabs {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.fixed-tab {
  display: flex;
  align-items: center;
  padding: 8px 14px;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 16px;
  cursor: pointer;
  font-size: 12px;
  color: rgba(255, 255, 255, 0.9);
  transition: all 0.2s ease;
  backdrop-filter: blur(10px);
  line-height: 1.6;
}

.fixed-tab:hover {
  background: rgba(255, 255, 255, 0.25);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-1px);
}

.fixed-tab.active {
  background: rgba(255, 255, 255, 0.95);
  border-color: rgba(255, 255, 255, 1);
  color: #1e3a8a;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.tab-close {
  margin-left: 6px;
  font-size: 14px;
  line-height: 1;
  opacity: 0.7;
  font-weight: bold;
}

.fixed-tab:hover .tab-close {
  opacity: 0.9;
}

.fixed-tab.active .tab-close {
  opacity: 1;
  color: #a0aec0;
}
</style>