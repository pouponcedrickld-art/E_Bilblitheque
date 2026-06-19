<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const sidebarOpen = ref(false)
const isPublicRoute = computed(() => {
  return !route.meta.requiresAuth
})

const userMenu = ref(false)

const menuItems = computed(() => {
  if (!authStore.user) return []

  const role = authStore.user.role
  type MenuItem = { label?: string; icon?: string; to?: string; action?: () => void; divider?: boolean }
  const items: MenuItem[] = []

  items.push({ label: 'Tableau de bord', icon: 'pi pi-home', to: authStore.getDashboardPath() })

  if (role === 'admin' || role === 'responsable_rh') {
    items.push({ label: 'Références', icon: 'pi pi-book', to: '/catalogue' })
    items.push({ label: 'Catégories', icon: 'pi pi-tags', to: '/catalogue' })
    items.push({ label: 'Auteurs', icon: 'pi pi-users', to: '/catalogue' })
    items.push({ label: 'Éditeurs', icon: 'pi pi-building', to: '/catalogue' })
  }

  if (role === 'responsable_demande' || role === 'user') {
    items.push({ label: 'Mes dépôts', icon: 'pi pi-upload', to: '/user/dashboard' })
  }

  if (role === 'admin') {
    items.push({ divider: true })
    items.push({ label: 'Utilisateurs', icon: 'pi pi-cog', to: '/admin/dashboard' })
    items.push({ label: 'Logs', icon: 'pi pi-chart-bar', to: '/admin/dashboard' })
  }

  items.push({ divider: true })
  items.push({ label: 'Déconnexion', icon: 'pi pi-sign-out', action: handleLogout })

  return items
})

function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

function closeSidebar() {
  sidebarOpen.value = false
}

async function handleLogout() {
  userMenu.value = false
  await authStore.logout()
  closeSidebar()
  router.push('/')
}

function getInitials(): string {
  const u = authStore.user
  if (!u) return '?'
  return (u.first_name.charAt(0) + u.last_name.charAt(0)).toUpperCase()
}
</script>

<template>
  <div class="layout">
    <!-- Sidebar overlay (mobile) -->
    <div v-if="sidebarOpen" class="sidebar-overlay" @click="closeSidebar" />

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-header">
        <span class="sidebar-logo">📚 BiblioNum</span>
      </div>

      <nav class="sidebar-nav">
        <template v-if="authStore.isAuthenticated">
          <div
            v-for="item in menuItems"
            :key="item.label"
            class="nav-item"
            :class="{ active: item.to && route.path === item.to }"
          >
            <hr v-if="item.divider" class="nav-divider" />
            <template v-else>
              <router-link v-if="item.to" :to="item.to" class="nav-link" @click="closeSidebar">
                <i :class="item.icon" />
                <span>{{ item.label }}</span>
              </router-link>
              <button v-else class="nav-link nav-btn" @click="item.action">
                <i :class="item.icon" />
                <span>{{ item.label }}</span>
              </button>
            </template>
          </div>
        </template>

        <template v-else>
          <router-link to="/" class="nav-link" @click="closeSidebar">
            <i class="pi pi-home" />
            <span>Accueil</span>
          </router-link>
          <router-link to="/catalogue" class="nav-link" @click="closeSidebar">
            <i class="pi pi-book" />
            <span>Catalogue</span>
          </router-link>
          <hr class="nav-divider" />
          <router-link to="/login" class="nav-link" @click="closeSidebar">
            <i class="pi pi-sign-in" />
            <span>Connexion</span>
          </router-link>
          <router-link to="/register" class="nav-link" @click="closeSidebar">
            <i class="pi pi-user-plus" />
            <span>Inscription</span>
          </router-link>
        </template>
      </nav>
    </aside>

    <!-- Main content area -->
    <div class="main-area">
      <!-- Top navbar -->
      <header class="topbar">
        <button class="hamburger" @click="toggleSidebar" aria-label="Menu">
          <i class="pi pi-bars" />
        </button>

        <div class="topbar-title">
          Bibliothèque Numérique
        </div>

        <div class="topbar-right">
          <template v-if="authStore.isAuthenticated">
            <div class="user-menu-wrapper">
              <button class="user-btn" @click="userMenu = !userMenu">
                <div class="user-avatar">{{ getInitials() }}</div>
                <div class="user-info">
                  <span class="user-name">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</span>
                  <span class="user-role">{{ authStore.roleLabel[authStore.user?.role ?? ''] }}</span>
                </div>
                <i class="pi pi-chevron-down" />
              </button>
              <div v-if="userMenu" class="user-dropdown" @click="userMenu = false">
                <div class="dropdown-header">
                  <strong>{{ authStore.user?.email }}</strong>
                </div>
                <button class="dropdown-item" @click="handleLogout">
                  <i class="pi pi-sign-out" /> Déconnexion
                </button>
              </div>
            </div>
          </template>
          <template v-else>
            <router-link to="/login" class="topbar-btn">Connexion</router-link>
            <router-link to="/register" class="topbar-btn topbar-btn-primary">Inscription</router-link>
          </template>
        </div>
      </header>

      <!-- Page content -->
      <main class="content">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
}

/* Sidebar overlay */
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 99;
}

/* Sidebar */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: var(--sidebar-width);
  height: 100vh;
  background: var(--sidebar-bg);
  color: var(--sidebar-text);
  display: flex;
  flex-direction: column;
  z-index: 100;
  transition: transform 0.25s ease;
}

.sidebar-header {
  height: var(--navbar-height);
  display: flex;
  align-items: center;
  padding: 0 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.sidebar-logo {
  font-size: 1.2rem;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.02em;
}

.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem 0;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.7rem 1.25rem;
  color: var(--sidebar-text);
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.15s;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
}

.nav-link i {
  width: 1.25rem;
  text-align: center;
  font-size: 1rem;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #fff;
}

.nav-item.active .nav-link {
  background: rgba(59, 130, 246, 0.15);
  color: var(--sidebar-active);
  border-right: 3px solid var(--sidebar-active);
}

.nav-divider {
  border: none;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  margin: 0.5rem 1.25rem;
}

/* Main area */
.main-area {
  flex: 1;
  margin-left: var(--sidebar-width);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* Top navbar */
.topbar {
  height: var(--navbar-height);
  background: #fff;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  padding: 0 1.5rem;
  gap: 1rem;
  position: sticky;
  top: 0;
  z-index: 50;
}

.hamburger {
  display: none;
  background: none;
  border: none;
  font-size: 1.25rem;
  color: var(--text-secondary);
  cursor: pointer;
  padding: 0.25rem;
}

.topbar-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
}

.topbar-right {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-menu-wrapper {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.35rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  background: none;
  cursor: pointer;
  transition: background 0.15s;
}

.user-btn:hover {
  background: var(--bg);
}

.user-avatar {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background: var(--primary);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
}

.user-info {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.user-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-primary);
}

.user-role {
  font-size: 0.7rem;
  color: var(--text-secondary);
}

.user-btn .pi-chevron-down {
  font-size: 0.7rem;
  color: var(--text-secondary);
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.35rem;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  min-width: 200px;
  z-index: 200;
}

.dropdown-header {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--border);
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.6rem 1rem;
  border: none;
  background: none;
  font-size: 0.85rem;
  color: var(--text-primary);
  cursor: pointer;
  transition: background 0.15s;
}

.dropdown-item:hover {
  background: var(--bg);
}

.topbar-btn {
  padding: 0.4rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--text-secondary);
  transition: all 0.15s;
}

.topbar-btn-primary {
  background: var(--primary);
  color: #fff;
}

.topbar-btn-primary:hover {
  background: var(--primary-dark);
}

/* Content area */
.content {
  flex: 1;
  padding: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
  .hamburger {
    display: block;
  }

  .sidebar {
    transform: translateX(-100%);
  }

  .sidebar.open {
    transform: translateX(0);
  }

  .sidebar-overlay {
    display: block;
  }

  .main-area {
    margin-left: 0;
  }

  .user-info {
    display: none;
  }
}
</style>
