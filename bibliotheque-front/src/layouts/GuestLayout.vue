// Layout public pour les visiteurs non connectés
<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const mobileMenuOpen = ref(false)

const navLinks = [
  { label: 'Catalogue', route: '/catalogue' },
  { label: 'Recherche avancée', route: '/search' },
  { label: 'À propos', route: '/help' },
]

// Navigue vers une route et ferme le menu mobile
function go(path: string) {
  mobileMenuOpen.value = false
  router.push(path)
}
</script>

<template>
  <div style="min-height: 100vh; background: var(--background);">
    <!-- Public Navbar -->
    <nav
      class="guest-nav"
    >
      <div class="guest-nav-inner">
        <router-link to="/" class="guest-logo">
          <i class="pi pi-book" style="font-size: 1.1rem"></i>
          <span class="guest-logo-text">BibliNum</span>
        </router-link>

        <!-- Desktop nav links -->
        <div class="guest-nav-links">
          <button
            v-for="link in navLinks"
            :key="link.label"
            @click="go(link.route)"
            class="guest-nav-link"
          >
            {{ link.label }}
          </button>
        </div>

        <div class="guest-btn-group">
          <template v-if="authStore.isAuthenticated">
            <button
              @click="go(authStore.getDashboardPath())"
              class="guest-btn-primary"
            >
              <i class="pi pi-user" style="font-size: 0.8rem"></i>
              Mon espace
            </button>
          </template>
          <template v-else>
            <button
              @click="go('/login')"
              class="guest-btn-outline"
            >
              Connexion
            </button>
            <button
              @click="go('/register')"
              class="guest-btn-primary"
            >
              S'inscrire
            </button>
          </template>
          <!-- Mobile hamburger -->
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="guest-hamburger"
          >
            <i :class="mobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'" style="font-size: 1.2rem"></i>
          </button>
        </div>
      </div>
      <!-- Mobile menu -->
      <div v-if="mobileMenuOpen" class="guest-mobile-menu">
        <button
          v-for="link in navLinks"
          :key="link.label"
          @click="go(link.route)"
          class="guest-mobile-link"
        >
          {{ link.label }}
        </button>
        <template v-if="!authStore.isAuthenticated">
          <button @click="go('/login')" class="guest-mobile-link" style="margin-top: 0.25rem">
            Connexion
          </button>
        </template>
        <template v-else>
          <button
            @click="go(authStore.getDashboardPath())"
            class="guest-mobile-link-primary"
          >
            <i class="pi pi-user" style="font-size: 0.85rem"></i>
            Mon espace
          </button>
        </template>
      </div>
    </nav>

    <main class="guest-content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.guest-nav {
  position: sticky;
  top: 0;
  z-index: 30;
  border-bottom: 1px solid var(--border);
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

.guest-nav-inner {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
  height: 56px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.guest-logo {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-shrink: 0;
}

.guest-logo i {
  color: var(--primary);
}

.guest-logo-text {
  font-size: 1rem;
  font-weight: 700;
  font-family: var(--font-serif);
  color: var(--primary);
}

.guest-nav-links {
  display: none;
  align-items: center;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .guest-nav-links {
    display: flex;
  }
}

.guest-nav-link {
  background: none;
  border: none;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--muted-foreground);
  cursor: pointer;
  transition: color 0.2s;
  padding: 0.25rem 0;
}

.guest-nav-link:hover {
  color: var(--foreground);
}

.guest-btn-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.guest-btn-outline {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  border-radius: var(--radius-xl);
  border: 1px solid var(--primary);
  background: transparent;
  color: var(--primary);
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  white-space: nowrap;
}

.guest-btn-outline:hover {
  background: rgba(27, 67, 50, 0.06);
}

.guest-btn-primary {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  border-radius: var(--radius-xl);
  border: none;
  background: var(--primary);
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.guest-btn-primary:hover {
  opacity: 0.9;
}

.guest-hamburger {
  display: flex;
  padding: 0.5rem;
  border-radius: var(--radius-xl);
  border: none;
  background: transparent;
  color: var(--muted-foreground);
  cursor: pointer;
  transition: color 0.2s, background 0.2s;
}

@media (min-width: 768px) {
  .guest-hamburger {
    display: none;
  }
}

.guest-hamburger:hover {
  color: var(--foreground);
  background: var(--muted);
}

.guest-mobile-menu {
  display: block;
  border-top: 1px solid var(--border);
  padding: 0.75rem 1rem;
  background: white;
}

@media (min-width: 768px) {
  .guest-mobile-menu {
    display: none;
  }
}

.guest-mobile-link {
  display: block;
  width: 100%;
  text-align: left;
  padding: 0.65rem 0.75rem;
  font-size: 0.875rem;
  border-radius: var(--radius-xl);
  border: none;
  background: transparent;
  color: var(--foreground);
  cursor: pointer;
  transition: background 0.2s;
}

.guest-mobile-link:hover {
  background: var(--muted);
}

.guest-mobile-link-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  text-align: left;
  padding: 0.65rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: var(--radius-xl);
  border: none;
  background: var(--primary);
  color: white;
  cursor: pointer;
  margin-top: 0.25rem;
}

@media (max-width: 400px) {
  .guest-btn-outline,
  .guest-btn-primary {
    padding: 0.45rem 0.65rem;
    font-size: 0.8rem;
  }
}

.guest-content {
  flex: 1;
  min-height: calc(100vh - 56px);
}
</style>
