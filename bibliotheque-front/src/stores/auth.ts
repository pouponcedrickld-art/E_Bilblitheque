import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import http, { fetchCsrfCookie } from '@/services/http'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => user.value !== null)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isResponsableRH = computed(() => user.value?.role === 'responsable_rh')
  const isResponsableDemande = computed(() => user.value?.role === 'responsable_demande')
  const isUser = computed(() => user.value?.role === 'user')

  const roleLabel: Record<string, string> = {
    admin: 'Administrateur',
    responsable_rh: 'Responsable RH',
    responsable_demande: 'Responsable Demande',
    user: 'Utilisateur',
  }

  const roleDashboardMap: Record<string, string> = {
    admin: '/admin/dashboard',
    responsable_rh: '/rh/dashboard',
    responsable_demande: '/demandes/dashboard',
    user: '/user/dashboard',
  }

  async function fetchUser(): Promise<void> {
    try {
      const response = await http.get('/me')
      user.value = response.data?.data ?? response.data
    } catch {
      user.value = null
    }
  }

  async function login(credentials: { email: string; password: string }): Promise<void> {
    await fetchCsrfCookie()
    const response = await http.post('/login', credentials)
    user.value = response.data?.data ?? response.data?.user ?? null
  }

  async function register(data: {
    first_name: string
    last_name: string
    email: string
    phone?: string
    password: string
    password_confirmation: string
  }): Promise<void> {
    await fetchCsrfCookie()
    const response = await http.post('/register', data)
    user.value = response.data?.data ?? response.data?.user ?? null
  }

  async function logout(): Promise<void> {
    try {
      await http.post('/logout')
    } finally {
      user.value = null
    }
  }

  function getDashboardPath(): string {
    if (!user.value) return '/login'
    return roleDashboardMap[user.value.role] || '/'
  }

  return {
    user,
    isAuthenticated,
    isAdmin,
    isResponsableRH,
    isResponsableDemande,
    isUser,
    roleLabel,
    roleDashboardMap,
    fetchUser,
    login,
    register,
    logout,
    getDashboardPath,
  }
})
