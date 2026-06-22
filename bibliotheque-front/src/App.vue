<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import GuestLayout from '@/layouts/GuestLayout.vue'
import AppLayout from '@/layouts/AppLayout.vue'

const route = useRoute()
const authStore = useAuthStore()

const layout = computed(() => {
  if (route.meta.requiresAuth) return AppLayout
  if (route.meta.guestOnly && !authStore.isAuthenticated) return GuestLayout
  if (authStore.isAuthenticated) return AppLayout
  return GuestLayout
})
</script>

<template>
  <Toast position="top-right" />
  <ConfirmDialog />
  <component :is="layout">
    <router-view />
  </component>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&family=Inter:wght@400;500;600;700&display=swap');

:root {
  --font-size: 16px;
  --background: #F2F2F7;
  --foreground: #1C1C1E;
  --card: #FFFFFF;
  --card-foreground: #1C1C1E;
  --primary: #1B4332;
  --primary-light: #2D6A4F;
  --primary-lighter: #40916C;
  --primary-foreground: #FFFFFF;
  --secondary: #E5E5EA;
  --secondary-foreground: #1C1C1E;
  --muted: #F2F2F7;
  --muted-foreground: #6E6E73;
  --accent: #34C759;
  --accent-foreground: #FFFFFF;
  --destructive: #FF3B30;
  --destructive-foreground: #ffffff;
  --border: rgba(60, 60, 67, 0.13);
  --input-background: #F2F2F7;
  --ring: #1B4332;
  --text-primary: var(--foreground);
  --text-secondary: var(--muted-foreground);
  --bg: var(--muted);
  --primary-dark: #143026;
  --radius: 0.75rem;
  --radius-sm: calc(var(--radius) - 4px);
  --radius-md: calc(var(--radius) - 2px);
  --radius-lg: var(--radius);
  --radius-xl: calc(var(--radius) + 4px);
  --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --font-serif: 'Playfair Display', Georgia, serif;
  --navbar-height: 56px;
  --sidebar-width: 260px;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: var(--font-sans);
  background: var(--background);
  color: var(--foreground);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-size: var(--font-size);
  line-height: 1.5;
}

h1, h2, h3, h4, h5, h6 {
  font-family: var(--font-serif);
  font-weight: 600;
  line-height: 1.3;
}

a { text-decoration: none; color: inherit; }

/* Scrollbar styling */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(60, 60, 67, 0.2); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: rgba(60, 60, 67, 0.35); }

/* No scrollbar utility */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Utility classes */
.gradient-hero {
  background: linear-gradient(150deg, #0F2419 0%, #1B4332 50%, #2D6A4F 100%);
}
</style>
