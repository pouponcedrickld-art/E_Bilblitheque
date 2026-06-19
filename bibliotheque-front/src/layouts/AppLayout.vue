<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const mobileMenuOpen = ref(false)
const userMenu = ref(false)

const menuItems = computed(() => {
  if (!authStore.user) return []

  const role = authStore.user.role
  type MenuItem = { label: string; icon?: string; to: string }
  const items: MenuItem[] = []

  items.push({ label: 'Tableau de bord', icon: 'pi pi-home', to: authStore.getDashboardPath() })

  if (role === 'admin' || role === 'responsable_rh') {
    items.push({ label: 'Références', icon: 'pi pi-book', to: '/catalogue' })
  }

  if (role === 'responsable_demande') {
    items.push({ label: 'Demandes', icon: 'pi pi-upload', to: '/demandes/dashboard' })
  }

  if (role === 'user') {
    items.push({ label: 'Mes dépôts', icon: 'pi pi-upload', to: '/user/dashboard' })
  }

  return items
})

function getInitials(): string {
  const u = authStore.user
  if (!u) return '?'
  return (u.first_name.charAt(0) + u.last_name.charAt(0)).toUpperCase()
}

function isActive(to: string): boolean {
  return route.path === to
}

function closeMobileMenu() {
  mobileMenuOpen.value = false
}

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

async function handleLogout() {
  userMenu.value = false
  mobileMenuOpen.value = false
  await authStore.logout()
  router.push('/')
}
</script>

<template>
  <div class="layout">
    <header class="navbar">
      <router-link to="/" class="logo">
        <span class="logo-icon">📚</span>
        <span class="logo-text">BiblioNum</span>
      </router-link>

      <nav v-if="authStore.isAuthenticated" class="nav-links">
        <router-link
          v-for="item in menuItems"
          :key="item.label"
          :to="item.to"
          class="nav-link"
          :class="{ active: isActive(item.to) }"
        >
          <i :class="item.icon" />
          <span>{{ item.label }}</span>
        </router-link>
      </nav>

      <nav v-else class="nav-links">
        <router-link to="/" class="nav-link" :class="{ active: isActive('/') }">Accueil</router-link>
        <router-link to="/catalogue" class="nav-link" :class="{ active: isActive('/catalogue') }">Catalogue</router-link>
      </nav>

      <div class="navbar-right">
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
          <router-link to="/login" class="btn-outline">Connexion</router-link>
          <router-link to="/register" class="btn-primary">Inscription</router-link>
        </template>

        <button class="hamburger" @click="toggleMobileMenu" aria-label="Menu">
          <i class="pi pi-bars" />
        </button>
      </div>
    </header>

    <div v-if="mobileMenuOpen" class="mobile-overlay" @click="closeMobileMenu" />

    <div class="mobile-menu" :class="{ open: mobileMenuOpen }">
      <div class="mobile-menu-header">
        <span class="logo-icon">📚 BiblioNum</span>
        <button class="close-btn" @click="closeMobileMenu">&times;</button>
      </div>

      <nav class="mobile-nav">
        <template v-if="authStore.isAuthenticated">
          <router-link
            v-for="item in menuItems"
            :key="item.label"
            :to="item.to"
            class="mobile-link"
            :class="{ active: isActive(item.to) }"
            @click="closeMobileMenu"
          >
            <i :class="item.icon" />
            <span>{{ item.label }}</span>
          </router-link>
          <hr class="divider" />
          <button class="mobile-link mobile-logout" @click="handleLogout">
            <i class="pi pi-sign-out" /> Déconnexion
          </button>
        </template>
        <template v-else>
          <router-link to="/" class="mobile-link" @click="closeMobileMenu">Accueil</router-link>
          <router-link to="/catalogue" class="mobile-link" @click="closeMobileMenu">Catalogue</router-link>
          <hr class="divider" />
          <router-link to="/login" class="mobile-link" @click="closeMobileMenu">Connexion</router-link>
          <router-link to="/register" class="mobile-link" @click="closeMobileMenu">Inscription</router-link>
        </template>
      </nav>
    </div>

    <main class="content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.navbar {
  position: sticky;
  top: 0;
  z-index: 100;
  height: var(--navbar-height);
  background: #fff;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  padding: 0 1.5rem;
  gap: 1.5rem;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text-primary);
  white-space: nowrap;
}

.logo-icon {
  font-size: 1.3rem;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.75rem;
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--text-secondary);
  border-radius: 0.375rem;
  transition: all 0.15s;
  white-space: nowrap;
}

.nav-link i {
  font-size: 0.9rem;
}

.nav-link:hover {
  color: var(--primary);
  background: #f1f5f9;
}

.nav-link.active {
  color: var(--primary);
  background: #eff6ff;
}

.navbar-right {
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

.btn-outline {
  padding: 0.4rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--text-secondary);
  border: 1px solid var(--border);
  transition: all 0.15s;
}

.btn-outline:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.btn-primary {
  padding: 0.4rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  font-weight: 500;
  background: var(--primary);
  color: #fff;
  transition: all 0.15s;
}

.btn-primary:hover {
  background: var(--primary-dark);
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

.mobile-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 199;
}

.mobile-menu {
  display: none;
  position: fixed;
  top: 0;
  right: 0;
  width: 280px;
  height: 100vh;
  background: #fff;
  box-shadow: -4px 0 16px rgba(0, 0, 0, 0.1);
  z-index: 200;
  flex-direction: column;
  transform: translateX(100%);
  transition: transform 0.25s ease;
}

.mobile-menu.open {
  transform: translateX(0);
}

.mobile-menu-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: var(--text-secondary);
  cursor: pointer;
}

.mobile-nav {
  flex: 1;
  overflow-y: auto;
  padding: 0.5rem 0;
}

.mobile-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.25rem;
  font-size: 0.9rem;
  color: var(--text-primary);
  transition: background 0.15s;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.mobile-link i {
  width: 1.25rem;
  text-align: center;
}

.mobile-link:hover {
  background: var(--bg);
}

.mobile-link.active {
  background: #eff6ff;
  color: var(--primary);
  font-weight: 600;
}

.mobile-logout {
  color: #dc2626;
}

.divider {
  border: none;
  border-top: 1px solid var(--border);
  margin: 0.5rem 1.25rem;
}

.content {
  flex: 1;
  padding: 1.5rem;
}

@media (max-width: 768px) {
  .nav-links {
    display: none;
  }

  .hamburger {
    display: block;
  }

  .mobile-overlay {
    display: block;
  }

  .mobile-menu {
    display: flex;
  }

  .user-info {
    display: none;
  }
}
</style>
