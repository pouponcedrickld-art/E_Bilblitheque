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

function confirmLogout() {
  confirm.require({
    message: 'Êtes-vous sûr de vouloir vous déconnecter ?',
    header: 'Déconnexion',
    icon: 'pi pi-sign-out',
    rejectLabel: 'Annuler',
    acceptLabel: 'Se déconnecter',
    acceptClass: 'p-button-danger',
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
  return [
    { id: 'dashboard', label: 'Tableau de bord', icon: 'pi pi-home', route: '/user/dashboard' },
    { id: 'catalog', label: 'Catalogue', icon: 'pi pi-book', route: '/catalogue' },
    { id: 'deposits', label: 'Mes dépôts', icon: 'pi pi-upload', route: '/user/deposits' },
    { id: 'new-deposit', label: 'Nouveau', icon: 'pi pi-plus', route: '/user/deposits/create' },
    { id: 'help', label: 'Aide', icon: 'pi pi-question-circle', route: '/help' },
  ]
})

const activeNavId = computed(() => {
  const path = router.currentRoute.value.path
  const found = navItems.value.find(i => path.startsWith(i.route))
  return found?.id ?? 'dashboard'
})

const bottomTabs = computed(() => navItems.value.slice(0, 5))
</script>

<template>
  <div class="app-shell">
    <!-- Drawer mobile -->
    <teleport to="body">
      <div v-if="drawerOpen" class="drawer-backdrop" @click="drawerOpen = false" />
      <div :class="['drawer', { 'drawer-open': drawerOpen }]">
        <div class="drawer-header">
          <div class="drawer-logo">
            <div class="drawer-logo-icon">
              <i class="pi pi-book" />
            </div>
            <div>
              <p class="drawer-title">BibliNum</p>
              <p class="drawer-subtitle">Bibliothèque Nationale</p>
            </div>
          </div>
          <button @click="drawerOpen = false" class="drawer-close">
            <i class="pi pi-times" />
          </button>
        </div>
        <nav class="drawer-nav">
          <router-link
            v-for="item in navItems"
            :key="item.id"
            :to="item.route"
            @click="drawerOpen = false"
            class="drawer-item"
            active-class="drawer-item-active"
          >
            <i :class="item.icon" />
            <span class="flex-1 text-left">{{ item.label }}</span>
          </router-link>
        </nav>
        <div class="drawer-footer">
          <div class="drawer-user">
            <div class="drawer-avatar">{{ initials }}</div>
            <div class="flex-1 min-w-0">
              <p class="drawer-user-name truncate">{{ fullName }}</p>
              <p class="drawer-user-role truncate">{{ authStore.roleLabel[authStore.user?.role ?? ''] }}</p>
            </div>
            <button @click="confirmLogout" class="drawer-logout">
              <i class="pi pi-sign-out" />
            </button>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Topnav -->
    <header class="topnav">
      <div class="topnav-inner">
        <button @click="drawerOpen = true" class="topnav-hamburger">
          <i class="pi pi-bars" />
        </button>

        <router-link to="/" class="topnav-logo">
          <div class="topnav-logo-icon">
            <i class="pi pi-book" />
          </div>
          <span class="topnav-logo-text">BibliNum</span>
        </router-link>

        <div class="topnav-spacer" />

        <div class="topnav-actions">
          <button @click="searchOpen = !searchOpen" :class="['topnav-action-btn', { 'topnav-action-active': searchOpen }]">
            <i class="pi pi-search" />
          </button>
          <button class="topnav-action-btn" style="position: relative">
            <i class="pi pi-bell" />
            <span class="topnav-bell-dot" />
          </button>

          <div class="topnav-user-divider" />

          <div class="topnav-user-chip" ref="userMenuRef">
            <button @click="userMenuOpen = !userMenuOpen" class="topnav-user-btn">
              <div class="topnav-avatar">{{ initials }}</div>
              <span class="topnav-user-name">{{ fullName.split(' ')[0] }}</span>
              <i class="pi pi-chevron-down" />
            </button>

            <div v-if="userMenuOpen" class="topnav-dropdown">
              <div class="topnav-dropdown-header">
                <p class="dropdown-user-name">{{ fullName }}</p>
                <p class="dropdown-user-role">{{ authStore.roleLabel[authStore.user?.role ?? ''] }}</p>
              </div>
              <router-link to="/user/profile" @click="userMenuOpen = false" class="topnav-dropdown-item">
                <i class="pi pi-user" /> Mon profil
              </router-link>
              <hr class="topnav-dropdown-divider" />
              <button @click="confirmLogout" class="topnav-dropdown-item dropdown-logout">
                <i class="pi pi-sign-out" /> Se déconnecter
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="searchOpen" class="topnav-search-bar">
        <div class="topnav-search-inner">
          <i class="pi pi-search" />
          <input
            ref="searchInput"
            autofocus
            placeholder="Titre, auteur, catégorie, mots-clés..."
            class="topnav-search-input"
            @keydown.escape="searchOpen = false"
          />
          <button @click="searchOpen = false" class="topnav-search-close">
            <i class="pi pi-times" />
          </button>
        </div>
      </div>
    </header>

    <main class="app-main">
      <slot />
    </main>

    <nav class="bottom-tabbar">
      <router-link
        v-for="item in bottomTabs"
        :key="item.id"
        :to="item.route"
        class="bottom-tab-item"
      >
        <div class="bottom-tab-icon-wrap">
          <i
            :class="item.icon"
            :style="{
              color: activeNavId === item.id ? 'var(--gold)' : 'var(--muted-foreground)',
            }"
          />
          <span v-if="item.badge && item.badge > 0" class="bottom-tab-badge">{{ item.badge }}</span>
        </div>
        <span
          class="bottom-tab-label"
          :style="{ color: activeNavId === item.id ? 'var(--gold)' : 'var(--muted-foreground)' }"
        >
          {{ item.label.split(' ')[0] }}
        </span>
        <span v-if="activeNavId === item.id" class="bottom-tab-indicator" />
      </router-link>
    </nav>
  </div>
</template>

<style scoped>
.app-shell {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: var(--bg);
}

/* ─── Topnav ─── */
.topnav {
  position: sticky;
  top: 0;
  z-index: 30;
  flex-shrink: 0;
  background: var(--primary);
  border-bottom: 1.5px solid rgba(200, 164, 92, 0.25);
}

.topnav-inner {
  display: flex;
  align-items: center;
  height: 56px;
  padding: 0 1rem;
  gap: 0.5rem;
}

@media (min-width: 640px) {
  .topnav-inner { padding: 0 1.5rem; gap: 0.75rem; }
}

.topnav-hamburger {
  display: flex;
  padding: 0.5rem;
  border-radius: var(--radius-lg);
  border: none;
  background: transparent;
  color: var(--gold-light);
  cursor: pointer;
  transition: all 0.2s;
}
.topnav-hamburger:hover {
  color: var(--gold);
  background: rgba(200, 164, 92, 0.15);
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
  background: rgba(200, 164, 92, 0.2);
  display: none;
  align-items: center;
  justify-content: center;
}
.topnav-logo-icon i {
  font-size: 0.85rem;
  color: var(--gold-light);
}
@media (min-width: 768px) {
  .topnav-logo-icon { display: flex; }
}

.topnav-logo-text {
  color: var(--gold-light);
  font-weight: 600;
  font-size: 0.875rem;
  font-family: var(--font-serif);
}

.topnav-spacer { flex: 1; }
@media (min-width: 768px) {
  .topnav-spacer { display: none; }
}

.topnav-actions {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.topnav-action-btn {
  padding: 0.5rem;
  border-radius: var(--radius-lg);
  border: none;
  background: transparent;
  color: rgba(226, 201, 146, 0.5);
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}
.topnav-action-btn:hover {
  color: var(--gold-light);
  background: rgba(200, 164, 92, 0.1);
}
.topnav-action-active {
  background: rgba(200, 164, 92, 0.18);
  color: var(--gold-light);
}

.topnav-bell-dot {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 7px;
  height: 7px;
  background: var(--gold);
  border-radius: 50%;
  border: 1.5px solid var(--primary);
}

.topnav-user-divider {
  display: none;
  width: 1px;
  height: 20px;
  background: rgba(200, 164, 92, 0.2);
  margin: 0 0.25rem;
}
@media (min-width: 768px) {
  .topnav-user-divider { display: block; }
}

.topnav-user-chip { position: relative; }

.topnav-user-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.25rem 0.5rem 0.25rem 0.25rem;
  border-radius: var(--radius-xl);
  border: 1px solid rgba(200, 164, 92, 0.2);
  background: transparent;
  cursor: pointer;
  transition: all 0.2s;
}
.topnav-user-btn:hover {
  border-color: rgba(200, 164, 92, 0.4);
  background: rgba(200, 164, 92, 0.08);
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
  color: var(--gold-light);
  flex-shrink: 0;
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
  border: 1.5px solid var(--gold-dark);
}

.topnav-user-name {
  display: none;
  color: var(--gold-light);
  font-size: 0.8125rem;
  font-weight: 500;
}
@media (min-width: 1024px) {
  .topnav-user-name { display: block; }
}

.topnav-user-btn .pi-chevron-down {
  font-size: 0.7rem;
  color: rgba(226, 201, 146, 0.4);
}

.topnav-dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + 0.5rem);
  width: 220px;
  background: var(--bg-elevated);
  border-radius: var(--radius-xl);
  box-shadow: 0 12px 48px rgba(44, 36, 32, 0.15);
  border: 1px solid var(--border);
  overflow: hidden;
  z-index: 50;
}

.topnav-dropdown-header {
  padding: 0.875rem 1rem;
  border-bottom: 1px solid var(--border);
}

.dropdown-user-name {
  font-family: var(--font-serif);
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--foreground);
}
.dropdown-user-role {
  font-size: 0.75rem;
  color: var(--muted-foreground);
  margin-top: 0.1rem;
}

.topnav-dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 0.85rem;
  border: none;
  background: transparent;
  color: var(--foreground);
  cursor: pointer;
  transition: all 0.15s;
}
.topnav-dropdown-item:hover {
  background: var(--muted);
}
.topnav-dropdown-item i {
  color: var(--muted-foreground);
  font-size: 0.9rem;
}

.topnav-dropdown-divider {
  border: none;
  border-top: 1px solid var(--border);
  margin: 0;
}

.dropdown-logout {
  color: var(--burgundy) !important;
}
.dropdown-logout:hover {
  background: #FDF2F2 !important;
}

/* Search bar */
.topnav-search-bar {
  border-top: 1px solid rgba(200, 164, 92, 0.12);
  padding: 0.75rem 1rem;
  background: rgba(15, 36, 25, 0.6);
}
@media (min-width: 640px) {
  .topnav-search-bar { padding: 0.75rem 1.5rem; }
}

.topnav-search-inner {
  max-width: 560px;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(200, 164, 92, 0.2);
  border-radius: var(--radius-xl);
  padding: 0.6rem 1rem;
  color: white;
}
.topnav-search-inner i {
  color: var(--gold-dark);
  font-size: 0.9rem;
  flex-shrink: 0;
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
  color: rgba(255, 255, 255, 0.35);
  font-style: italic;
}

.topnav-search-close {
  padding: 0.25rem;
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.3);
  cursor: pointer;
  transition: color 0.2s;
}
.topnav-search-close:hover { color: rgba(255, 255, 255, 0.6); }

/* ─── Main Content ─── */
.app-main {
  flex: 1;
  overflow: auto;
  padding: 1.5rem 1rem;
  padding-bottom: 80px;
}
@media (min-width: 768px) {
  .app-main { padding: 1.5rem 1.5rem; padding-bottom: 2rem; }
}
@media (min-width: 1024px) {
  .app-main { padding: 2rem; }
}

/* ─── Drawer ─── */
.drawer-backdrop {
  position: fixed;
  inset: 0;
  z-index: 40;
  background: rgba(44, 36, 32, 0.4);
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
  background: linear-gradient(180deg, var(--primary-dark) 0%, var(--primary) 100%);
  border-right: 1.5px solid rgba(200, 164, 92, 0.2);
  transform: translateX(-100%);
  transition: transform 0.3s ease-out;
}
.drawer-open { transform: translateX(0); }

.drawer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 2rem 1.25rem 1.25rem;
  border-bottom: 1px solid rgba(200, 164, 92, 0.15);
}

.drawer-logo { display: flex; align-items: center; gap: 0.75rem; }

.drawer-logo-icon {
  width: 36px;
  height: 36px;
  border-radius: 0.5rem;
  background: rgba(200, 164, 92, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
}
.drawer-logo-icon i {
  font-size: 1rem;
  color: var(--gold-light);
}

.drawer-title {
  color: var(--gold-light);
  font-weight: 600;
  font-size: 1rem;
  font-family: var(--font-serif);
}
.drawer-subtitle {
  color: rgba(226, 201, 146, 0.4);
  font-size: 0.75rem;
}

.drawer-close {
  padding: 0.25rem;
  border: none;
  background: transparent;
  color: rgba(226, 201, 146, 0.4);
  cursor: pointer;
  transition: color 0.2s;
}
.drawer-close:hover { color: var(--gold-light); }

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
  border-radius: var(--radius-lg);
  border: none;
  background: transparent;
  color: rgba(226, 201, 146, 0.5);
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}
.drawer-item:hover {
  color: var(--gold-light);
  background: rgba(200, 164, 92, 0.1);
}
.drawer-item-active {
  background: rgba(200, 164, 92, 0.18);
  color: var(--gold-light);
  font-weight: 500;
}

a.drawer-item, a.bottom-tab-item {
  text-decoration: none;
}

.drawer-footer {
  padding: 1rem 1.25rem 1.5rem;
  border-top: 1px solid rgba(200, 164, 92, 0.15);
}

.drawer-user { display: flex; align-items: center; gap: 0.75rem; }

.drawer-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--gold-light);
  flex-shrink: 0;
  background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-light) 100%);
  border: 1.5px solid rgba(200, 164, 92, 0.3);
}

.drawer-user-name {
  color: var(--gold-light);
  font-size: 0.8125rem;
  font-weight: 500;
}
.drawer-user-role {
  color: rgba(226, 201, 146, 0.4);
  font-size: 0.75rem;
}

.drawer-logout {
  padding: 0.5rem;
  border: none;
  background: transparent;
  color: var(--burgundy);
  cursor: pointer;
  transition: opacity 0.2s;
  font-weight: 600;
}
.drawer-logout:hover { opacity: 0.8; }

/* ─── Bottom Tab Bar ─── */
.bottom-tabbar {
  display: flex;
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 30;
  align-items: center;
  border-top: 1px solid var(--border);
  background: rgba(250, 247, 242, 0.92);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  padding-bottom: env(safe-area-inset-bottom, 0px);
}
@media (min-width: 768px) {
  .bottom-tabbar { display: none; }
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

.bottom-tab-icon-wrap { position: relative; }
.bottom-tab-icon-wrap i { font-size: 1.3rem; }

.bottom-tab-badge {
  position: absolute;
  top: -4px;
  right: -6px;
  background: var(--destructive);
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
  background: var(--gold);
}
</style>
