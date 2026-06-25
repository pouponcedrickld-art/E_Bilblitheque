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

function go(path: string) {
  mobileMenuOpen.value = false
  router.push(path)
}
</script>

<template>
  <div class="guest-shell">
    <nav class="guest-nav">
      <div class="guest-nav-inner">
        <router-link to="/" class="guest-logo">
          <div class="guest-logo-icon">
            <i class="pi pi-book" />
          </div>
          <div>
            <span class="guest-logo-text">BibliNum</span>
            <span class="guest-logo-sub">Bibliothèque Nationale</span>
          </div>
        </router-link>

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
            <button @click="go(authStore.getDashboardPath())" class="btn btn-primary">
              <i class="pi pi-user" />
              Mon espace
            </button>
          </template>
          <template v-else>
            <button @click="go('/login')" class="btn btn-outline">
              Connexion
            </button>
            <button @click="go('/register')" class="btn btn-primary">
              S'inscrire
            </button>
          </template>
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="guest-hamburger">
            <i :class="mobileMenuOpen ? 'pi pi-times' : 'pi pi-bars'" />
          </button>
        </div>
      </div>

      <Transition name="slide-down">
        <div v-if="mobileMenuOpen" class="guest-mobile-menu">
          <button
            v-for="link in navLinks"
            :key="link.label"
            @click="go(link.route)"
            class="guest-mobile-link"
          >
            {{ link.label }}
          </button>
          <hr class="gold-rule" />
          <template v-if="!authStore.isAuthenticated">
            <button @click="go('/login')" class="btn btn-outline" style="width:100%;justify-content:center">
              Connexion
            </button>
            <button @click="go('/register')" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem">
              S'inscrire
            </button>
          </template>
          <template v-else>
            <button @click="go(authStore.getDashboardPath())" class="guest-mobile-link-primary">
              <i class="pi pi-user" /> Mon espace
            </button>
          </template>
        </div>
      </Transition>
    </nav>

    <main class="guest-content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.guest-shell {
  min-height: 100vh;
  background: var(--background);
}

.guest-nav {
  position: sticky;
  top: 0;
  z-index: 30;
  border-bottom: 1px solid var(--border);
  background: rgba(250, 247, 242, 0.88);
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
  gap: 0.65rem;
  flex-shrink: 0;
}

.guest-logo-icon {
  width: 34px;
  height: 34px;
  border-radius: 0.5rem;
  background: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
}
.guest-logo-icon i {
  font-size: 0.9rem;
  color: var(--gold-light);
}

.guest-logo-text {
  font-size: 1rem;
  font-weight: 700;
  font-family: var(--font-serif);
  color: var(--primary);
  display: block;
  line-height: 1.2;
}

.guest-logo-sub {
  font-size: 0.65rem;
  color: var(--muted-foreground);
  display: none;
}
@media (min-width: 480px) {
  .guest-logo-sub { display: block; }
}

.guest-nav-links {
  display: none;
  align-items: center;
  gap: 1.5rem;
}
@media (min-width: 768px) {
  .guest-nav-links { display: flex; }
}

.guest-nav-link {
  background: none;
  border: none;
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--muted-foreground);
  cursor: pointer;
  transition: color 0.2s;
  padding: 0.25rem 0;
  position: relative;
}
.guest-nav-link::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: var(--gold);
  transition: width 0.25s ease;
}
.guest-nav-link:hover {
  color: var(--foreground);
}
.guest-nav-link:hover::after {
  width: 100%;
}

.guest-btn-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.guest-hamburger {
  display: flex;
  padding: 0.5rem;
  border-radius: var(--radius-lg);
  border: none;
  background: transparent;
  color: var(--muted-foreground);
  cursor: pointer;
  transition: all 0.2s;
}
@media (min-width: 768px) {
  .guest-hamburger { display: none; }
}
.guest-hamburger:hover {
  color: var(--foreground);
  background: var(--muted);
}

.guest-mobile-menu {
  display: block;
  border-top: 1px solid var(--border);
  padding: 0.75rem 1.25rem 1.25rem;
  background: var(--bg-card);
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
@media (min-width: 768px) {
  .guest-mobile-menu { display: none !important; }
}

.guest-mobile-link {
  display: block;
  width: 100%;
  text-align: left;
  padding: 0.65rem 0.75rem;
  font-size: 0.875rem;
  border-radius: var(--radius-lg);
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
  border-radius: var(--radius-lg);
  border: none;
  background: var(--primary);
  color: white;
  cursor: pointer;
  margin-top: 0.25rem;
  justify-content: center;
}

@media (max-width: 400px) {
  .btn-outline,
  .btn-primary {
    padding: 0.5rem 0.75rem;
    font-size: 0.8rem;
  }
}

.guest-content {
  flex: 1;
  min-height: calc(100vh - 56px);
}

/* slide-down transition */
.slide-down-enter-active {
  transition: all 0.25s ease-out;
}
.slide-down-leave-active {
  transition: all 0.2s ease-in;
}
.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-8px);
}
.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
