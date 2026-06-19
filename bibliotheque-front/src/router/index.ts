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
      component: () => import('@/Pages/Visitor/Home.vue'),
      meta: { title: 'Accueil - Bibliothèque Numérique' },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/Pages/Auth/Login.vue'),
      meta: { title: 'Connexion', guestOnly: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/Pages/Auth/Register.vue'),
      meta: { title: 'Inscription', guestOnly: true },
    },
    {
      path: '/catalogue',
      name: 'catalogue',
      component: () => import('@/Pages/Visitor/Catalog/Index.vue'),
      meta: { title: 'Catalogue' },
    },
    {
      path: '/references/:id',
      name: 'reference-detail',
      component: () => import('@/Pages/Visitor/Catalog/Show.vue'),
      meta: { title: 'Détail de la référence' },
    },
    {
      path: '/search',
      name: 'search',
      component: () => import('@/Pages/Visitor/Search/Simple.vue'),
      meta: { title: 'Recherche' },
    },
    {
      path: '/search/advanced',
      name: 'search-advanced',
      component: () => import('@/Pages/Visitor/Search/Advanced.vue'),
      meta: { title: 'Recherche avancée' },
    },

    // ---- Admin ----
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: () => import('@/Pages/Admin/Dashboard.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Dashboard Admin' },
    },
    {
      path: '/admin/references',
      name: 'admin-references',
      component: () => import('@/Pages/Admin/References/Index.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Gestion des références' },
    },
    {
      path: '/admin/references/create',
      name: 'admin-references-create',
      component: () => import('@/Pages/Admin/References/Create.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Nouvelle référence' },
    },
    {
      path: '/admin/references/:id/edit',
      name: 'admin-references-edit',
      component: () => import('@/Pages/Admin/References/Edit.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Modifier une référence' },
    },
    {
      path: '/admin/references/:id/publish',
      name: 'admin-references-publish',
      component: () => import('@/Pages/Admin/References/Publish.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Publier une référence' },
    },
    {
      path: '/admin/deposit-requests',
      name: 'admin-deposit-requests',
      component: () => import('@/Pages/Admin/DepositRequests/Index.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Demandes de dépôt' },
    },
    {
      path: '/admin/deposit-requests/history',
      name: 'admin-deposit-requests-history',
      component: () => import('@/Pages/Admin/DepositRequests/History.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Historique des demandes' },
    },
    {
      path: '/admin/deposit-requests/:id/review',
      name: 'admin-deposit-requests-review',
      component: () => import('@/Pages/Admin/DepositRequests/Review.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Examiner une demande' },
    },
    {
      path: '/admin/users',
      name: 'admin-users',
      component: () => import('@/Pages/Admin/Users/Index.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Gestion des utilisateurs' },
    },
    {
      path: '/admin/users/:id/edit',
      name: 'admin-users-edit',
      component: () => import('@/Pages/ManagerHR/Users/Edit.vue'),
      meta: { requiresAuth: true, role: 'admin', title: "Modifier l'utilisateur" },
    },
    {
      path: '/admin/users/roles',
      name: 'admin-users-roles',
      component: () => import('@/Pages/Admin/Users/Roles.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Gestion des rôles' },
    },
    {
      path: '/admin/categories',
      name: 'admin-categories',
      component: () => import('@/Pages/Admin/Catalog/Categories.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Catégories' },
    },
    {
      path: '/admin/authors',
      name: 'admin-authors',
      component: () => import('@/Pages/Admin/Catalog/Authors.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Auteurs' },
    },
    {
      path: '/admin/publishers',
      name: 'admin-publishers',
      component: () => import('@/Pages/Admin/Catalog/Publishers.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Éditeurs' },
    },
    {
      path: '/admin/keywords',
      name: 'admin-keywords',
      component: () => import('@/Pages/Admin/Catalog/Keywords.vue'),
      meta: { requiresAuth: true, role: 'admin', title: 'Mots-clés' },
    },
    {
      path: '/admin/logs',
      name: 'admin-logs',
      component: () => import('@/Pages/Admin/Logs/ActivityLogs.vue'),
      meta: { requiresAuth: true, role: 'admin', title: "Journal d'activité" },
    },

    // ---- Responsable RH ----
    {
      path: '/rh/dashboard',
      name: 'rh-dashboard',
      component: () => import('@/Pages/ManagerHR/Dashboard.vue'),
      meta: { requiresAuth: true, role: 'responsable_rh', title: 'Dashboard RH' },
    },
    {
      path: '/rh/users',
      name: 'rh-users',
      component: () => import('@/Pages/ManagerHR/Users/Index.vue'),
      meta: { requiresAuth: true, role: 'responsable_rh', title: 'Gestion des utilisateurs' },
    },
    {
      path: '/rh/users/create',
      name: 'rh-users-create',
      component: () => import('@/Pages/ManagerHR/Users/Create.vue'),
      meta: { requiresAuth: true, role: 'responsable_rh', title: 'Nouvel utilisateur' },
    },
    {
      path: '/rh/users/:id/edit',
      name: 'rh-users-edit',
      component: () => import('@/Pages/ManagerHR/Users/Edit.vue'),
      meta: { requiresAuth: true, role: 'responsable_rh', title: "Modifier l'utilisateur" },
    },
    {
      path: '/rh/activity-logs',
      name: 'rh-activity-logs',
      component: () => import('@/Pages/ManagerHR/Users/ActivityLog.vue'),
      meta: { requiresAuth: true, role: 'responsable_rh', title: "Journal d'activité" },
    },

    // ---- Responsable Demande ----
    {
      path: '/demandes/dashboard',
      name: 'demandes-dashboard',
      component: () => import('@/Pages/ManagerRequests/Dashboard.vue'),
      meta: { requiresAuth: true, role: 'responsable_demande', title: 'Dashboard Demandes' },
    },
    {
      path: '/manager/requests',
      name: 'manager-requests',
      component: () => import('@/Pages/ManagerRequests/DepositRequests/Index.vue'),
      meta: { requiresAuth: true, role: 'responsable_demande', title: 'Demandes attribuées' },
    },
    {
      path: '/manager/requests/:id/review',
      name: 'manager-requests-review',
      component: () => import('@/Pages/ManagerRequests/DepositRequests/Review.vue'),
      meta: { requiresAuth: true, role: 'responsable_demande', title: 'Examiner une demande' },
    },
    {
      path: '/manager/requests/:id/second-opinion',
      name: 'manager-requests-second-opinion',
      component: () => import('@/Pages/ManagerRequests/DepositRequests/SecondOpinion.vue'),
      meta: { requiresAuth: true, role: 'responsable_demande', title: 'Second avis' },
    },

    // ---- Utilisateur ----
    {
      path: '/user/dashboard',
      name: 'user-dashboard',
      component: () => import('@/Pages/User/Dashboard.vue'),
      meta: { requiresAuth: true, role: 'user', title: 'Dashboard Utilisateur' },
    },
    {
      path: '/user/deposits',
      name: 'user-deposits',
      component: () => import('@/Pages/User/DepositRequests/Index.vue'),
      meta: { requiresAuth: true, role: 'user', title: 'Mes dépôts' },
    },
    {
      path: '/user/deposits/create',
      name: 'user-deposits-create',
      component: () => import('@/Pages/User/DepositRequests/Create.vue'),
      meta: { requiresAuth: true, role: 'user', title: 'Nouveau dépôt' },
    },
    {
      path: '/user/deposits/:id',
      name: 'user-deposits-show',
      component: () => import('@/Pages/User/DepositRequests/Show.vue'),
      meta: { requiresAuth: true, role: 'user', title: 'Détail du dépôt' },
    },
    {
      path: '/user/profile',
      name: 'user-profile',
      component: () => import('@/Pages/User/Profile/Edit.vue'),
      meta: { requiresAuth: true, title: 'Mon profil' },
    },

    // ---- Pages communes ----
    {
      path: '/help',
      name: 'help',
      component: () => import('@/Pages/Visitor/Help.vue'),
      meta: { title: 'Aide et support' },
    },
    {
      path: '/user/references/:id/read',
      name: 'user-references-read',
      component: () => import('@/Pages/User/References/Read.vue'),
      meta: { requiresAuth: true, title: 'Lecture' },
    },
    {
      path: '/user/references/:id/download',
      name: 'user-references-download',
      component: () => import('@/Pages/User/References/Download.vue'),
      meta: { requiresAuth: true, title: 'Téléchargement' },
    },
    {
      path: '/forbidden',
      name: 'forbidden',
      component: () => import('@/Pages/Visitor/Forbidden.vue'),
      meta: { title: 'Accès refusé' },
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/Pages/Visitor/NotFound.vue'),
      meta: { title: 'Page introuvable' },
    },
  ],
})

router.beforeEach(async (to: RouteLocationNormalized) => {
  const authStore = useAuthStore()

  if (to.meta.title) {
    document.title = to.meta.title as string
  }

  if (!authStore.authChecked) {
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
