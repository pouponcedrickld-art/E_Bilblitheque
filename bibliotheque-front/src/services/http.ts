import axios from 'axios'
import router from '@/router'

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

http.interceptors.response.use(
  (response) => response,
  async (error) => {
    const status = error.response?.status

    if (status === 401 || status === 419) {
      const { useAuthStore } = await import('@/stores/auth')
      const authStore = useAuthStore()
      authStore.user = null

      if (router.currentRoute.value.path !== '/login') {
        router.push('/login')
      }
    }

    return Promise.reject(error)
  },
)

export async function fetchCsrfCookie(): Promise<void> {
  await axios.get(
    import.meta.env.VITE_API_SANCTUM_URL || 'http://localhost:5173/sanctum/csrf-cookie',
    { withCredentials: true, withXSRFToken: true },
  )
}

export default http
