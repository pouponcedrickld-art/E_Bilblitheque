import { defineStore } from 'pinia'
import http from '@/services/http'
import type { Notification } from '@/types'

export const useNotificationsStore = defineStore('notifications', {
  state: () => ({
    items: [] as Notification[],
    loading: false,
  }),
  actions: {
    async fetch() {
      this.loading = true
      try {
        const res = await http.get('/notifications')
        this.items = res.data?.data ?? res.data ?? []
      } finally {
        this.loading = false
      }
    },
    async markAsRead(id: number) {
      await http.patch(`/notifications/${id}/read`)
      const n = this.items.find(i => i.id === id)
      if (n) n.read_at = new Date().toISOString()
    },
    async markAllAsRead() {
      await http.patch('/notifications/read-all')
      this.items.forEach(n => { n.read_at = new Date().toISOString() })
    },
    get unread() {
      return this.items.filter(n => !n.read_at)
    },
  },
})
