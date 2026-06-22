// Importation d'Axios pour les requêtes HTTP
import axios from 'axios'

/**
 * Instance Axios préconfigurée pour communiquer avec l'API Laravel.
 * - URL de base depuis la variable d'environnement VITE_API_URL
 * - Envoie systématiquement les cookies de session (withCredentials)
 * - Gère automatiquement le jeton CSRF XSRF de Sanctum
 * - Déclare les en-têtes JSON et X-Requested-With pour Laravel
 */
const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:5173/api',
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
})

// Intercepteur gérant les erreurs 401 et 419 (session expirée)
/**
 * Intercepteur de réponse – réinitialise l'authentification en cas
 * d'erreur 401 (non authentifié) ou 419 (CSRF expiré), sauf pour
 * la route /me (pour éviter une boucle infinie).
 */
http.interceptors.response.use(
  (response) => response,
  async (error) => {
    const status = error.response?.status

    if ((status === 401 || status === 419) && error.config.url !== '/me') {
      const { useAuthStore } = await import('@/stores/auth')
      const authStore = useAuthStore()
      authStore.user = null
      authStore.authChecked = true
    }

    return Promise.reject(error)
  },
)

/**
 * Récupère le cookie CSRF auprès de Sanctum.
 * Nécessaire avant chaque requête POST /login ou /register.
 */
export async function fetchCsrfCookie(): Promise<void> {
  await axios.get(
    import.meta.env.VITE_API_SANCTUM_URL || 'http://localhost:5173/sanctum/csrf-cookie',
    { withCredentials: true, withXSRFToken: true },
  )
}

export default http
