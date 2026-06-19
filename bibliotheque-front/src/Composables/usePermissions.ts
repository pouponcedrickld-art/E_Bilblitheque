import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function usePermissions() {
  const auth = useAuthStore()

  return {
    isAdmin: computed(() => auth.user?.role === 'admin'),
    isRH: computed(() => auth.user?.role === 'responsable_rh'),
    isManager: computed(() => auth.user?.role === 'responsable_demande'),
    isUser: computed(() => auth.user?.role === 'user'),
    canManageUsers: computed(() => ['admin', 'responsable_rh'].includes(auth.user?.role ?? '')),
    canManageCatalog: computed(() => auth.user?.role === 'admin'),
    canReviewRequests: computed(() => ['admin', 'responsable_demande'].includes(auth.user?.role ?? '')),
    canAccessAdmin: computed(() => auth.user?.role === 'admin'),
  }
}
