import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { RouteLocationNormalized } from 'vue-router'

declare module 'vue-router' {
  interface RouteMeta {
    requiresAuth?: boolean
    guestOnly?: boolean
    role?: string | string[]
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('@/views/Home.vue'),
      meta: { title: 'Accueil - Bibliothèque Numérique' },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/Login.vue'),
      meta: { title: 'Connexion', guestOnly: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/Register.vue'),
      meta: { title: 'Inscription', guestOnly: true },
    },
    {
      path: '/catalogue',
      name: 'catalogue',
      component: () => import('@/views/Home.vue'),
      meta: { title: 'Catalogue' },
    },
    {
      path: '/references/:id',
      name: 'reference-detail',
      component: () => import('@/views/ReferenceDetail.vue'),
      meta: { title: 'Détail de la référence' },
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: () => import('@/views/Dashboard.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Dashboard Admin' },
    },
    {
      path: '/rh/dashboard',
      name: 'rh-dashboard',
      component: () => import('@/views/Dashboard.vue'),
      meta: { requiresAuth: true, role: 'responsable_rh', title: 'Dashboard RH' },
    },
    {
      path: '/demandes/dashboard',
      name: 'demandes-dashboard',
      component: () => import('@/views/Dashboard.vue'),
      meta: { requiresAuth: true, role: 'responsable_demande', title: 'Dashboard Demandes' },
    },
    {
      path: '/user/dashboard',
      name: 'user-dashboard',
      component: () => import('@/views/Dashboard.vue'),
      meta: { requiresAuth: true, role: 'user', title: 'Dashboard Utilisateur' },
    },
    {
      path: '/forbidden',
      name: 'forbidden',
      component: () => import('@/views/Forbidden.vue'),
      meta: { title: 'Accès refusé' },
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/NotFound.vue'),
      meta: { title: 'Page introuvable' },
    },
  ],
})

router.beforeEach(async (to: RouteLocationNormalized) => {
  const authStore = useAuthStore()

  if (to.meta.title) {
    document.title = to.meta.title as string
  }

  if (!authStore.isAuthenticated && authStore.user === null) {
    await authStore.fetchUser()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return authStore.getDashboardPath()
  }

  if (to.meta.role) {
    const allowedRoles = Array.isArray(to.meta.role) ? to.meta.role : [to.meta.role]
    if (!allowedRoles.includes(authStore.user?.role ?? '')) {
      return { name: 'forbidden' }
    }
  }
})

export default router
