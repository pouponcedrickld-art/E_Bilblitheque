// Store Pinia pour les demandes de dépôt
import { defineStore } from 'pinia'
import http from '@/services/http'
import type { DepositRequest } from '@/types'

export const useDepositRequestsStore = defineStore('depositRequests', {
  // État initial du store
  state: () => ({
    requests: [] as DepositRequest[],
    current: null as DepositRequest | null,
    loading: false,
    error: null as string | null,
  }),
  actions: {
    // Récupère les demandes de l'utilisateur connecté
    async fetchMine() {
      this.loading = true
      this.error = null
      try {
        const res = await http.get('/deposit-requests', { params: { per_page: 'all' } })
        this.requests = res.data?.data ?? res.data ?? []
      } catch {
        this.error = 'Impossible de charger vos demandes de dépôt.'
      } finally {
        this.loading = false
      }
    },
    // Récupère toutes les demandes (admin/responsable)
    async fetchAll() {
      this.loading = true
      this.error = null
      try {
        const res = await http.get('/deposit-requests', { params: { per_page: 'all' } })
        this.requests = res.data?.data ?? res.data ?? []
      } catch {
        this.error = 'Impossible de charger les demandes de dépôt.'
      } finally {
        this.loading = false
      }
    },
    // Récupère une demande par son identifiant
    async fetchById(id: number) {
      this.loading = true
      try {
        const res = await http.get(`/deposit-requests/${id}`)
        this.current = res.data?.data ?? res.data
        return this.current
      } finally {
        this.loading = false
      }
    },
    // Crée une nouvelle demande de dépôt
    async create(data: FormData) {
      const res = await http.post('/deposit-requests', data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      this.requests.unshift(res.data?.data ?? res.data)
      return res.data
    },
  },
})
