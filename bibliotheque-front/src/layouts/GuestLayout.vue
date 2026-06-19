<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
</script>

<template>
  <div class="guest-layout">
    <header class="topbar">
      <router-link to="/" class="logo">📚 BiblioNum</router-link>

      <nav class="nav-links">
        <router-link to="/" class="nav-link">Accueil</router-link>
        <router-link to="/catalogue" class="nav-link">Catalogue</router-link>
      </nav>

      <div class="topbar-right">
        <template v-if="authStore.isAuthenticated">
          <router-link :to="authStore.getDashboardPath()" class="btn btn-outline">
            Mon espace
          </router-link>
        </template>
        <template v-else>
          <router-link to="/login" class="btn btn-outline">Connexion</router-link>
          <router-link to="/register" class="btn btn-primary">Inscription</router-link>
        </template>
      </div>
    </header>

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

.topbar {
  height: var(--navbar-height);
  background: #fff;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  padding: 0 1.5rem;
  gap: 2rem;
  position: sticky;
  top: 0;
  z-index: 50;
}

.logo {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--text-primary);
  white-space: nowrap;
}

.nav-links {
  display: flex;
  gap: 1rem;
}

.nav-link {
  font-size: 0.9rem;
  color: var(--text-secondary);
  padding: 0.3rem 0.5rem;
  border-radius: 0.25rem;
  transition: color 0.15s;
}

.nav-link:hover {
  color: var(--primary);
}

.topbar-right {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.btn {
  padding: 0.45rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  border: none;
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

@media (max-width: 640px) {
  .nav-links {
    display: none;
  }
}
</style>
