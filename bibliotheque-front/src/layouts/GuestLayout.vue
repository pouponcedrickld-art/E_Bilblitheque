<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import Menubar from 'primevue/menubar'
import type { MenuItem } from 'primevue/menuitem'

const authStore = useAuthStore()

const navItems: MenuItem[] = [
  { label: 'Accueil', icon: 'pi pi-home', to: '/' },
  { label: 'Catalogue', icon: 'pi pi-book', to: '/catalogue' },
  { label: 'Recherche', icon: 'pi pi-search', to: '/search' },
]
</script>

<template>
  <div class="guest-layout">
    <Menubar :model="navItems" class="guest-menubar">
      <template #start>
        <router-link to="/" class="logo">
          <span class="logo-icon">📚</span>
          <span class="logo-text">BiblioNum</span>
        </router-link>
      </template>

      <template #end>
        <template v-if="authStore.isAuthenticated">
          <router-link :to="authStore.getDashboardPath()" class="btn btn-outline">
            Mon espace
          </router-link>
        </template>
        <template v-else>
          <router-link to="/login" class="btn btn-outline">Connexion</router-link>
          <router-link to="/register" class="btn btn-primary">Inscription</router-link>
        </template>
      </template>
    </Menubar>

    <main class="content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.guest-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.guest-menubar {
  position: sticky;
  top: 0;
  z-index: 100;
  border-radius: 0;
  border-bottom: 1px solid var(--border);
  padding: 0 1rem;
  background: #fff;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text-primary);
  white-space: nowrap;
  margin-right: 1rem;
}

.logo-icon {
  font-size: 1.3rem;
}

.btn {
  padding: 0.45rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  border: none;
  white-space: nowrap;
}

.btn-outline {
  border: 1px solid var(--border);
  color: var(--text-secondary);
  background: transparent;
}

.btn-outline:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.btn-primary {
  background: var(--primary);
  color: #fff;
}

.btn-primary:hover {
  background: var(--primary-dark);
}

.content {
  flex: 1;
  padding: 1.5rem;
}
</style>
