import { defineStore } from 'pinia'
import http from '@/services/http'
import type { DepositRequest } from '@/types'

export const useDepositRequestsStore = defineStore('depositRequests', {
  state: () => ({
    requests: [] as DepositRequest[],
    current: null as DepositRequest | null,
    loading: false,
  }),
  actions: {
    async fetchMine() {
      this.loading = true
      try {
        const res = await http.get('/deposit-requests')
        this.requests = res.data?.data ?? res.data ?? []
      } finally {
        this.loading = false
      }
    },
    async fetchAll() {
      this.loading = true
      try {
        const res = await http.get('/deposit-requests')
        this.requests = res.data?.data ?? res.data ?? []
      } finally {
        this.loading = false
      }
    },
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
    async create(data: FormData) {
      const res = await http.post('/deposit-requests', data, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      this.requests.unshift(res.data?.data ?? res.data)
      return res.data
    },
  },
})
