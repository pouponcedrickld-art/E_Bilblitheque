import { defineStore } from 'pinia'

export const useDepositRequestsStore = defineStore('depositRequests', {
  state: () => ({
    requests: [] as any[],
  }),
  actions: {},
})
