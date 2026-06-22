<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useConfirm } from 'primevue/useconfirm'

const authStore = useAuthStore()
const router = useRouter()
const confirm = useConfirm()

const userMenuOpen = ref(false)
const drawerOpen = ref(false)
const searchOpen = ref(false)
const userMenuRef = ref<HTMLElement | null>(null)

function go(path: string) {
  drawerOpen.value = false
  router.push(path)
}

function confirmLogout() {
  confirm.require({
    message: 'Êtes-vous sûr de vouloir vous déconnecter ?',
    header: 'Déconnexion',
    icon: 'pi pi-sign-out',
    rejectLabel: 'Annuler',
    acceptLabel: 'Se déconnecter',
    accept: async () => {
      await authStore.logout()
      router.push('/')
    },
  })
}

onMounted(() => {
  function handleClick(e: MouseEvent) {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target as Node)) {
      userMenuOpen.value = false
    }
  }
  document.addEventListener('mousedown', handleClick)
  onUnmounted(() => document.removeEventListener('mousedown', handleClick))
})

const fullName = computed(() => {
  const u = authStore.user
  if (!u) return ''
  return `${u.first_name} ${u.last_name}`
})

const initials = computed(() => {
  const u = authStore.user
  if (!u) return '?'
  return (u.first_name.charAt(0) + u.last_name.charAt(0)).toUpperCase()
})

interface NavItem {
  id: string
  label: string
  icon: string
  route: string
  badge?: number
}

const navItems = computed<NavItem[]>(() => {
  if (!authStore.user) return []
  const role = authStore.user.role

  if (role === 'admin') {
    return [
      { id: 'dashboard', label: 'Tableau de bord', icon: 'pi pi-home', route: '/admin/dashboard' },
      { id: 'references', label: 'Références', icon: 'pi pi-book', route: '/admin/references' },
      { id: 'deposits', label: 'Demandes', icon: 'pi pi-inbox', route: '/admin/deposit-requests' },
      { id: 'users', label: 'Utilisateurs', icon: 'pi pi-users', route: '/admin/users' },
      { id: 'catalog', label: 'Catalogue', icon: 'pi pi-tags', route: '/admin/categories' },
      { id: 'logs', label: 'Journal', icon: 'pi pi-history', route: '/admin/logs' },
      { id: 'help', label: 'Aide', icon: 'pi pi-question-circle', route: '/help' },
    ]
  }
  if (role === 'responsable_rh') {
    return [
      { id: 'dashboard', label: 'Tableau de bord', icon: 'pi pi-home', route: '/rh/dashboard' },
      { id: 'users', label: 'Utilisateurs', icon: 'pi pi-users', route: '/rh/users' },
      { id: 'catalog', label: 'Catalogue', icon: 'pi pi-book', route: '/catalogue' },
      { id: 'logs', label: 'Activités', icon: 'pi pi-history', route: '/rh/activity-logs' },
      { id: 'help', label: 'Aide', icon: 'pi pi-question-circle', route: '/help' },
    ]
  }
  if (role === 'responsable_demande') {
    return [
      { id: 'dashboard', label: 'Tableau de bord', icon: 'pi pi-home', route: '/demandes/dashboard' },
      { id: 'requests', label: 'Demandes', icon: 'pi pi-inbox', route: '/manager/requests' },
      { id: 'catalog', label: 'Catalogue', icon: 'pi pi-book', route: '/catalogue' },
      { id: 'help', label: 'Aide', icon: 'pi pi-question-circle', route: '/help' },
    ]
  }
  // user
  return [
    { id: 'dashboard', label: 'Tableau de bord', icon: 'pi pi-home', route: '/user/dashboard' },
    { id: 'catalog', label: 'Catalogue', icon: 'pi pi-book', route: '/catalogue' },
    { id: 'deposits', label: 'Mes dépôts', icon: 'pi pi-upload', route: '/user/deposits' },
    { id: 'new-deposit', label: 'Nouveau', icon: 'pi pi-plus', route: '/user/deposits/create' },
    { id: 'help', label: 'Aide', icon: 'pi pi-question-circle', route: '/help' },
  ]
})

const isActive = (route: string) => router.currentRoute.value.path.startsWith(route)

const activeNavId = computed(() => {
  const path = router.currentRoute.value.path
  const found = navItems.value.find(i => path.startsWith(i.route))
  return found?.id ?? 'dashboard'
})

const bottomTabs = computed(() => navItems.value.slice(0, 5))
</script>

<template>
  <div class="app-shell">
    <!-- Mobile Drawer -->
    <teleport to="body">
      <div v-if="drawerOpen" class="drawer-backdrop" @click="drawerOpen = false" />
      <div :class="['drawer', { 'drawer-open': drawerOpen }]">
        <div class="drawer-header">
          <div class="drawer-logo">
            <div class="drawer-logo-icon">
              <i class="pi pi-book" style="font-size: 1rem; color: white"></i>
            </div>
            <div>
              <p class="drawer-title">BibliNum</p>
              <p class="drawer-subtitle">Bibliothèque Nationale</p>
            </div>
          </div>
          <button @click="drawerOpen = false" class="drawer-close">
            <i class="pi pi-times" style="font-size: 1.2rem"></i>
          </button>
        </div>
        <nav class="drawer-nav">
          <button
            v-for="item in navItems"
            :key="item.id"
            @click="go(item.route)"
            :class="['drawer-item', { 'drawer-item-active': isActive(item.route) }]"
          >
            <i :class="item.icon" style="font-size: 1.1rem"></i>
            <span class="flex-1 text-left">{{ item.label }}</span>
          </button>
        </nav>
        <div class="drawer-footer">
          <div class="drawer-user">
            <div class="drawer-avatar">{{ initials }}</div>
            <div class="flex-1 min-w-0">
              <p class="drawer-user-name truncate">{{ fullName }}</p>
              <p class="drawer-user-role truncate">{{ authStore.roleLabel[authStore.user?.role ?? ''] }}</p>
            </div>
            <button @click="confirmLogout" class="drawer-logout">
              <i class="pi pi-sign-out" style="font-size: 1rem"></i>
            </button>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Top Navbar -->
    <header class="topnav">
      <div class="topnav-inner">
        <!-- Hamburger (mobile) -->
        <button @click="drawerOpen = true" class="topnav-hamburger">
          <i class="pi pi-bars" style="font-size: 1.2rem"></i>
        </button>

        <!-- Logo -->
        <router-link to="/" class="topnav-logo">
          <div class="topnav-logo-icon">
            <i class="pi pi-book" style="font-size: 0.85rem; color: white"></i>
          </div>
          <span class="topnav-logo-text">BibliNum</span>
        </router-link>

        <div class="topnav-divider" />

        <!-- Nav links (desktop) -->
        <nav class="topnav-links">
          <button
            v-for="item in navItems"
            :key="item.id"
            @click="go(item.route)"
            :class="['topnav-link', { 'topnav-link-active': isActive(item.route) }]"
          >
            <i :class="item.icon" style="font-size: 0.9rem"></i>
            <span>{{ item.label }}</span>
          </button>
        </nav>

        <!-- Spacer on mobile -->
        <div class="topnav-spacer" />

        <!-- Right actions -->
        <div class="topnav-actions">
          <button @click="searchOpen = !searchOpen" :class="['topnav-action-btn', { 'topnav-action-active': searchOpen }]">
            <i class="pi pi-search" style="font-size: 1.05rem"></i>
          </button>
          <button class="topnav-action-btn" style="position: relative">
            <i class="pi pi-bell" style="font-size: 1.05rem"></i>
            <span class="topnav-bell-dot" />
          </button>

          <div class="topnav-user-divider" />

          <!-- User chip -->
          <div class="topnav-user-chip" ref="userMenuRef">
            <button @click="userMenuOpen = !userMenuOpen" class="topnav-user-btn">
              <div class="topnav-avatar">{{ initials }}</div>
              <span class="topnav-user-name">{{ fullName.split(' ')[0] }}</span>
              <i class="pi pi-chevron-down" style="font-size: 0.8rem; opacity: 0.4"></i>
            </button>

            <div v-if="userMenuOpen" class="topnav-dropdown">
              <div class="topnav-dropdown-header">
                <p class="font-semibold text-sm" style="color: var(--foreground)">{{ fullName }}</p>
                <p class="text-xs" style="color: var(--muted-foreground)">{{ authStore.roleLabel[authStore.user?.role ?? ''] }}</p>
              </div>
              <button @click="go('/user/profile')" class="topnav-dropdown-item">
                <i class="pi pi-user" style="font-size: 0.9rem"></i>
                Mon profil
              </button>
              <button class="topnav-dropdown-item">
                <i class="pi pi-cog" style="font-size: 0.9rem"></i>
                Paramètres
              </button>
              <hr class="topnav-dropdown-divider" />
              <button @click="confirmLogout" class="topnav-dropdown-item" style="color: var(--destructive)">
                <i class="pi pi-sign-out" style="font-size: 0.9rem"></i>
                Se déconnecter
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Expandable search -->
      <div v-if="searchOpen" class="topnav-search-bar">
        <div class="topnav-search-inner">
          <i class="pi pi-search" style="font-size: 0.9rem; opacity: 0.5; flex-shrink: 0"></i>
          <input
            ref="searchInput"
            autofocus
            placeholder="Titre, auteur, catégorie, mots-clés..."
            class="topnav-search-input"
            @keydown.escape="searchOpen = false"
          />
          <button @click="searchOpen = false" class="topnav-search-close">
            <i class="pi pi-times" style="font-size: 0.9rem"></i>
          </button>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <main class="app-main">
      <slot />
    </main>

    <!-- Bottom Tab Bar (mobile only) -->
    <nav class="bottom-tabbar">
      <button
        v-for="item in bottomTabs"
        :key="item.id"
        @click="go(item.route)"
        class="bottom-tab-item"
      >
        <div class="bottom-tab-icon-wrap">
          <i
            :class="item.icon"
            :style="{
              fontSize: '1.3rem',
              color: activeNavId === item.id ? 'var(--primary)' : 'var(--muted-foreground)',
              strokeWidth: activeNavId === item.id ? 2.2 : 1.7,
            }"
          />
          <span v-if="item.badge && item.badge > 0" class="bottom-tab-badge">{{ item.badge }}</span>
        </div>
        <span
          class="bottom-tab-label"
          :style="{ color: activeNavId === item.id ? 'var(--primary)' : 'var(--muted-foreground)' }"
        >
          {{ item.label.split(' ')[0] }}
        </span>
        <span v-if="activeNavId === item.id" class="bottom-tab-indicator" />
      </button>
    </nav>
  </div>
</template>

<style scoped>
.app-shell {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: var(--background);
}

/* ─── Top Navbar ─── */
.topnav {
  position: sticky;
  top: 0;
  z-index: 30;
  flex-shrink: 0;
  background: var(--primary);
}

.topnav-inner {
  display: flex;
  align-items: center;
  height: 56px;
  padding: 0 1rem;
  gap: 0.5rem;
}

@media (min-width: 640px) {
  .topnav-inner {
    padding: 0 1.5rem;
    gap: 0.75rem;
  }
}

.topnav-hamburger {
  display: flex;
  padding: 0.5rem;
  border-radius: var(--radius-xl);
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  transition: color 0.2s, background 0.2s;
}

@media (min-width: 768px) {
  .topnav-hamburger {
    display: none;
  }
}

.topnav-hamburger:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.topnav-logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-shrink: 0;
}

.topnav-logo-icon {
  width: 28px;
  height: 28px;
  border-radius: 0.5rem;
  background: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  display: none;
}

@media (min-width: 768px) {
  .topnav-logo-icon {
    display: flex;
  }
}

.topnav-logo-text {
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  font-family: var(--font-serif);
}

.topnav-divider {
  display: none;
  width: 1px;
  height: 20px;
  background: rgba(255, 255, 255, 0.2);
  margin: 0 0.25rem;
}

@media (min-width: 768px) {
  .topnav-divider {
    display: block;
  }
}

.topnav-links {
  display: none;
  align-items: center;
  gap: 0.125rem;
  flex: 1;
  overflow-x: auto;
}

@media (min-width: 768px) {
  .topnav-links {
    display: flex;
  }
}

.topnav-link {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 0.75rem;
  border-radius: var(--radius-xl);
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.55);
  font-size: 0.8125rem;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
  flex-shrink: 0;
}

.topnav-link:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.topnav-link-active {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  font-weight: 500;
}

.topnav-spacer {
  flex: 1;
}

@media (min-width: 768px) {
  .topnav-spacer {
    display: none;
  }
}

.topnav-actions {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.topnav-action-btn {
  padding: 0.5rem;
  border-radius: var(--radius-xl);
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  transition: color 0.2s, background 0.2s;
  position: relative;
}

.topnav-action-btn:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.topnav-action-active {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

.topnav-bell-dot {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 8px;
  height: 8px;
  background: #fbbf24;
  border-radius: 50%;
  border: 2px solid var(--primary);
}

.topnav-user-divider {
  display: none;
  width: 1px;
  height: 20px;
  background: rgba(255, 255, 255, 0.2);
  margin: 0 0.25rem;
}

@media (min-width: 768px) {
  .topnav-user-divider {
    display: block;
  }
}

.topnav-user-chip {
  position: relative;
}

.topnav-user-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.25rem 0.5rem 0.25rem 0.25rem;
  border-radius: var(--radius-xl);
  border: none;
  background: transparent;
  cursor: pointer;
  transition: background 0.2s;
}

.topnav-user-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.topnav-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.6875rem;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
  background: linear-gradient(135deg, #1B4332 0%, #40916C 100%);
}

.topnav-user-name {
  display: none;
  color: white;
  font-size: 0.8125rem;
  font-weight: 500;
}

@media (min-width: 1024px) {
  .topnav-user-name {
    display: block;
  }
}

.topnav-dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + 0.5rem);
  width: 220px;
  background: white;
  border-radius: var(--radius-xl);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  border: 1px solid var(--border);
  overflow: hidden;
  z-index: 50;
}

.topnav-dropdown-header {
  padding: 0.875rem 1rem;
  border-bottom: 1px solid var(--border);
}

.topnav-dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
  border: none;
  background: transparent;
  color: var(--foreground);
  cursor: pointer;
  transition: background 0.15s;
}

.topnav-dropdown-item:hover {
  background: var(--muted);
}

.topnav-dropdown-divider {
  border: none;
  border-top: 1px solid var(--border);
  margin: 0;
}

/* Expandable search bar */
.topnav-search-bar {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.75rem 1rem;
  background: rgba(10, 28, 18, 0.5);
}

@media (min-width: 640px) {
  .topnav-search-bar {
    padding: 0.75rem 1.5rem;
  }
}

.topnav-search-inner {
  max-width: 560px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: var(--radius-xl);
  padding: 0.6rem 1rem;
  color: white;
}

.topnav-search-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  color: white;
  font-size: 0.875rem;
  font-family: var(--font-sans);
}

.topnav-search-input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.topnav-search-close {
  padding: 0.25rem;
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: color 0.2s;
}

.topnav-search-close:hover {
  color: rgba(255, 255, 255, 0.6);
}

/* ─── Main Content ─── */
.app-main {
  flex: 1;
  overflow: auto;
  padding: 1.5rem 1rem;
  padding-bottom: 80px;
}

@media (min-width: 768px) {
  .app-main {
    padding: 1.5rem 1.5rem;
    padding-bottom: 2rem;
  }
}

@media (min-width: 1024px) {
  .app-main {
    padding: 2rem;
  }
}

/* ─── Drawer (mobile) ─── */
.drawer-backdrop {
  position: fixed;
  inset: 0;
  z-index: 40;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

.drawer {
  position: fixed;
  inset: 0;
  right: auto;
  z-index: 50;
  width: 280px;
  display: flex;
  flex-direction: column;
  background: var(--primary);
  transform: translateX(-100%);
  transition: transform 0.3s ease-out;
}

.drawer-open {
  transform: translateX(0);
}

.drawer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2rem 1.25rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.drawer-logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.drawer-logo-icon {
  width: 36px;
  height: 36px;
  border-radius: 0.5rem;
  background: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
}

.drawer-title {
  color: white;
  font-weight: 600;
  font-size: 1rem;
  font-family: var(--font-serif);
}

.drawer-subtitle {
  color: rgba(255, 255, 255, 0.4);
  font-size: 0.75rem;
}

.drawer-close {
  padding: 0.25rem;
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  transition: color 0.2s;
}

.drawer-close:hover {
  color: white;
}

.drawer-nav {
  flex: 1;
  padding: 0.75rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.drawer-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: var(--radius-xl);
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.drawer-item:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.drawer-item-active {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  font-weight: 500;
}

.drawer-footer {
  padding: 1rem 1.25rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.drawer-user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.drawer-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
  background: linear-gradient(135deg, #1B4332 0%, #40916C 100%);
}

.drawer-user-name {
  color: white;
  font-size: 0.8125rem;
  font-weight: 500;
}

.drawer-user-role {
  color: rgba(255, 255, 255, 0.4);
  font-size: 0.75rem;
}

.drawer-logout {
  padding: 0.5rem;
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: color 0.2s;
}

.drawer-logout:hover {
  color: rgba(255, 255, 255, 0.7);
}

/* ─── Bottom Tab Bar (mobile only) ─── */
.bottom-tabbar {
  display: flex;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 30;
  align-items: center;
  border-top: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  padding-bottom: env(safe-area-inset-bottom, 0px);
}

@media (min-width: 768px) {
  .bottom-tabbar {
    display: none;
  }
}

.bottom-tab-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 0;
  gap: 0.25rem;
  border: none;
  background: transparent;
  cursor: pointer;
  position: relative;
  transition: all 0.15s;
}

.bottom-tab-icon-wrap {
  position: relative;
}

.bottom-tab-badge {
  position: absolute;
  top: -4px;
  right: -6px;
  background: #ef4444;
  color: white;
  font-size: 0.5625rem;
  font-weight: 700;
  border-radius: 50%;
  padding: 0 0.375rem;
  min-width: 16px;
  text-align: center;
  line-height: 16px;
}

.bottom-tab-label {
  font-size: 0.625rem;
  font-weight: 500;
  line-height: 1;
}

.bottom-tab-indicator {
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 32px;
  height: 3px;
  border-radius: 0 0 3px 3px;
  background: var(--primary);
}
</style>
