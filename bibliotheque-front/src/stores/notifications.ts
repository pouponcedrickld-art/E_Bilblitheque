import { defineStore } from 'pinia'

export const useNotificationsStore = defineStore('notifications', {
  state: () => ({
    items: [] as any[],
  }),
  actions: {},
})
