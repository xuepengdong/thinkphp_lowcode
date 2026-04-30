import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', {
  state: () => ({
    userInfo: {},
    user: null as any,
    menus: [] as any[],
    permissions: [] as string[]
  }),
  actions: {
    setUserInfo(info: any) {
      this.userInfo = info
    },
    clearUserInfo() {
      this.userInfo = {}
    },
    setUser(user: any) {
      this.user = user
    },
    setMenus(menus: any[]) {
      this.menus = menus
    }
  }
})