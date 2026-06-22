// Store Pinia pour les notifications
import { defineStore } from 'pinia'
import http from '@/services/http'
import type { Notification } from '@/types'

export const useNotificationsStore = defineStore('notifications', {
  // État initial
  state: () => ({
    items: [] as Notification[],
    loading: false,
  }),
  actions: {
    // Récupère toutes les notifications depuis l'API
    async fetch() {
      this.loading = true
      try {
        const res = await http.get('/notifications')
        this.items = res.data?.data ?? res.data ?? []
      } finally {
        this.loading = false
      }
    },
    // Marque une notification comme lue
    async markAsRead(id: number) {
      await http.patch(`/notifications/${id}/read`)
      const n = this.items.find(i => i.id === id)
      if (n) n.is_read = true
    },
    // Marque toutes les notifications comme lues
    async markAllAsRead() {
      await http.patch('/notifications/read-all')
      this.items.forEach(n => { n.is_read = true })
    },
    // Notifications non lues
    get unread() {
      return this.items.filter(n => !n.is_read)
    },
  },
})
