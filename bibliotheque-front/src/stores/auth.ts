import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import http, { fetchCsrfCookie } from '@/services/http'
import type { User } from '@/types'

// Store d'authentification – utilisateur connecté, état et rôles
/**
 * Store d'authentification – gère l'utilisateur connecté, son état
 * et fournit des accesseurs pour les vérifications de rôles.
 */
export const useAuthStore = defineStore('auth', () => {
  /** Données de l'utilisateur actuellement connecté (null si déconnecté) */
  const user = ref<User | null>(null)

  /** Indique si la vérification initiale de l'authentification a eu lieu */
  const authChecked = ref(false)

  /** L'utilisateur est connecté (utilisateur non null) */
  const isAuthenticated = computed(() => user.value !== null)

  /** L'utilisateur a le rôle administrateur */
  const isAdmin = computed(() => user.value?.role === 'admin')

  /** L'utilisateur a le rôle responsable RH */
  const isResponsableRH = computed(() => user.value?.role === 'responsable_rh')

  /** L'utilisateur a le rôle responsable demande */
  const isResponsableDemande = computed(() => user.value?.role === 'responsable_demande')

  /** L'utilisateur a le rôle utilisateur standard */
  const isUser = computed(() => user.value?.role === 'user')

  /** Libellé affichable pour chaque rôle */
  const roleLabel: Record<string, string> = {
    admin: 'Administrateur',
    responsable_rh: 'Responsable RH',
    responsable_demande: 'Responsable Demande',
    user: 'Utilisateur',
  }

  /** Association rôle → route du tableau de bord */
  const roleDashboardMap: Record<string, string> = {
    admin: '/admin/dashboard',
    responsable_rh: '/rh/dashboard',
    responsable_demande: '/demandes/dashboard',
    user: '/user/dashboard',
  }

  // Récupère les données de l'utilisateur connecté depuis l'API
  /**
   * Récupère les données de l'utilisateur connecté depuis l'API.
   * Marque authChecked à true une fois terminé (succès ou échec).
   */
  async function fetchUser(): Promise<void> {
    try {
      const response = await http.get('/me')
      user.value = response.data?.data ?? response.data
    } catch {
      user.value = null
    } finally {
      authChecked.value = true
    }
  }

  // Connecte l'utilisateur avec email et mot de passe
  /**
   * Connecte l'utilisateur avec email et mot de passe.
   * Récupère d'abord le cookie CSRF Sanctum, puis envoie les identifiants.
   */
  async function login(credentials: { email: string; password: string }): Promise<void> {
    await fetchCsrfCookie()
    const response = await http.post('/login', credentials)
    user.value = response.data?.data ?? response.data?.user ?? null
  }

  // Inscrit un nouvel utilisateur et connecte automatiquement
  /**
   * Inscrit un nouvel utilisateur.
   * Envoie les données du formulaire d'inscription et connecte automatiquement.
   */
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

  // Déconnecte l'utilisateur via l'API
  /**
   * Déconnecte l'utilisateur (appel API POST /logout).
   * Réinitialise l'utilisateur à null dans tous les cas.
   */
  async function logout(): Promise<void> {
    try {
      await http.post('/logout')
    } finally {
      user.value = null
    }
  }

  // Retourne la route du tableau de bord selon le rôle
  /**
   * Retourne la route du tableau de bord en fonction du rôle de l'utilisateur.
   * Si l'utilisateur n'est pas connecté, retourne '/login'.
   */
  function getDashboardPath(): string {
    if (!user.value) return '/login'
    return roleDashboardMap[user.value.role] || '/'
  }

  return {
    user,
    authChecked,
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
