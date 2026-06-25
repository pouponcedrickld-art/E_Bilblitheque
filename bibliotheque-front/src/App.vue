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
  <Toast position="bottom-right" />
  <ConfirmDialog appendTo="body" />
  <component :is="layout">
    <router-view />
  </component>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap');

:root {
  --font-size: 16px;

  /* ── Fond « Papier vélin » ── */
  --bg: #F5F0E8;
  --background: #F5F0E8;
  --bg-card: #FAF7F2;
  --bg-elevated: #FFFFFF;
  --foreground: #2C2420;
  --card: #FAF7F2;
  --card-foreground: #2C2420;

  /* ── Bois & Vert forêt ── */
  --primary: #1A3A32;
  --primary-dark: #0F2419;
  --primary-light: #2D6A4F;
  --primary-lighter: #40916C;
  --primary-foreground: #FFFFFF;

  /* ── Dorures ── */
  --gold: #C8A45C;
  --gold-light: #E2C992;
  --gold-dark: #A8883A;
  --gold-glow: rgba(200, 164, 92, 0.25);

  /* ── Cuir & Maroquin ── */
  --burgundy: #6B2D3E;
  --leather: #8B6F47;
  --warm-brown: #3D2B1F;

  /* ── Surfaces & textes ── */
  --secondary: #E8E2D8;
  --secondary-foreground: #2C2420;
  --muted: #EDE8DF;
  --muted-foreground: #8B8178;
  --accent: #C8A45C;
  --accent-foreground: #FFFFFF;
  --destructive: #A83232;
  --destructive-foreground: #FFFFFF;
  --success: #2D6A4F;
  --success-foreground: #FFFFFF;
  --warning: #C8A45C;

  /* ── Bordures ── */
  --border: rgba(44, 36, 32, 0.10);
  --border-gold: rgba(200, 164, 92, 0.30);
  --input-background: #FAF7F2;
  --ring: #C8A45C;

  /* ── Radius ── */
  --radius: 0.625rem;
  --radius-sm: calc(var(--radius) - 2px);
  --radius-md: var(--radius);
  --radius-lg: calc(var(--radius) + 4px);
  --radius-xl: calc(var(--radius) + 8px);

  /* ── Typographie ── */
  --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --font-serif: 'Playfair Display', Georgia, serif;
  --font-alt: 'Cormorant Garamond', Georgia, serif;

  /* ── Dimensions ── */
  --navbar-height: 56px;
  --sidebar-width: 260px;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

::selection {
  background: var(--gold);
  color: var(--primary-dark);
}

html {
  scroll-behavior: smooth;
}

body {
  font-family: var(--font-sans);
  background: var(--background);
  color: var(--foreground);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-size: var(--font-size);
  line-height: 1.6;
}

h1, h2, h3, h4, h5, h6 {
  font-family: var(--font-serif);
  font-weight: 600;
  line-height: 1.3;
  color: var(--foreground);
}

a { text-decoration: none; color: inherit; }

/* ── Filet doré ── */
.gold-rule {
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--gold), transparent);
  border: none;
  margin: 1.5rem 0;
}

.gold-rule-left {
  height: 2px;
  width: 60px;
  background: var(--gold);
  border: none;
  margin: 0.75rem 0 1.25rem;
}

/* ── Scrollbar de bibliothèque ── */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: var(--muted); }
::-webkit-scrollbar-thumb { background: var(--gold-dark); border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: var(--gold); }

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* ── Hero gradient ── */
.gradient-hero {
  background: linear-gradient(150deg, #0F2419 0%, #1A3A32 50%, #2D6A4F 100%);
}
.gradient-hero-gold {
  background: linear-gradient(150deg, #0F2419 0%, #1A3A32 40%, #3D2B1F 100%);
}

/* ── Boutons ── */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.675rem 1.25rem;
  border: none;
  border-radius: var(--radius-lg);
  font-size: 0.875rem;
  font-weight: 600;
  font-family: var(--font-sans);
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}
.btn:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-primary {
  background: var(--primary);
  color: white;
}
.btn-primary:hover:not(:disabled) {
  background: var(--primary-dark);
  box-shadow: 0 4px 16px rgba(26, 58, 50, 0.30);
  transform: translateY(-1px);
}

.btn-gold {
  background: var(--gold);
  color: var(--primary-dark);
}
.btn-gold:hover:not(:disabled) {
  background: var(--gold-dark);
  box-shadow: 0 4px 16px rgba(200, 164, 92, 0.35);
  transform: translateY(-1px);
}

.btn-outline {
  background: transparent;
  border: 1.5px solid var(--gold);
  color: var(--gold-dark);
}
.btn-outline:hover:not(:disabled) {
  background: rgba(200, 164, 92, 0.08);
  box-shadow: 0 0 0 3px var(--gold-glow);
}

.btn-ghost {
  background: transparent;
  color: var(--muted-foreground);
}
.btn-ghost:hover:not(:disabled) {
  background: var(--muted);
  color: var(--foreground);
}

/* ── Inputs ── */
.input {
  padding: 0.75rem 1rem;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-lg);
  font-size: 0.875rem;
  font-family: var(--font-sans);
  background: var(--input-background);
  color: var(--foreground);
  outline: none;
  transition: all 0.2s ease;
  width: 100%;
}
.input::placeholder {
  color: var(--muted-foreground);
  opacity: 0.7;
  font-style: italic;
}
.input:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px var(--gold-glow);
}
.input-invalid {
  border-color: var(--destructive) !important;
}
.input-invalid:focus {
  box-shadow: 0 0 0 3px rgba(168, 50, 50, 0.20) !important;
}
.input-icon {
  position: relative;
}
.input-icon .input {
  padding-left: 2.5rem;
}
.input-icon i,
.input-icon .icon {
  position: absolute;
  left: 0.85rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--muted-foreground);
  font-size: 0.9rem;
  pointer-events: none;
}

/* ── Cards ── */
.card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  transition: all 0.25s ease;
}
.card:hover {
  box-shadow: 0 8px 32px rgba(44, 36, 32, 0.08);
}
.card-gold {
  border-color: var(--border-gold);
}
.card-gold:hover {
  box-shadow: 0 8px 32px rgba(200, 164, 92, 0.12);
}

/* ── Badge sceau ── */
.seal-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.3rem 0.85rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid;
  position: relative;
}

/* ── Section title ── */
.section-title {
  font-family: var(--font-serif);
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--foreground);
  position: relative;
}

/* ── Animations ── */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active {
  transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.slide-up-leave-active {
  transition: all 0.2s ease;
}
.slide-up-enter-from {
  opacity: 0;
  transform: translateY(16px);
}
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* ── Page wrapper ── */
.page {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}
.page-header {
  margin-bottom: 2rem;
  position: relative;
}
.page-header h1 {
  font-size: 1.75rem;
  font-weight: 700;
}
.page-header p {
  color: var(--muted-foreground);
  font-size: 0.9rem;
  margin-top: 0.25rem;
}

/* ── Alerts ── */
.alert {
  padding: 0.85rem 1.15rem;
  border-radius: var(--radius-lg);
  font-size: 0.85rem;
  margin-bottom: 1rem;
  border: 1px solid;
}
.alert-error {
  background: #FDF2F2;
  border-color: #FECACA;
  color: var(--destructive);
}
.alert-success {
  background: #F0FDF4;
  border-color: #86EFAC;
  color: var(--success);
}
.alert-info {
  background: #F5F0E8;
  border-color: var(--border-gold);
  color: var(--warm-brown);
}

/* ── Loading ── */
.loading-spinner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: var(--muted-foreground);
  gap: 0.75rem;
}
.loading-spinner i {
  font-size: 2rem;
  color: var(--gold);
}

/* ── Empty state ── */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: var(--muted-foreground);
  gap: 0.75rem;
}
.empty-state i {
  font-size: 2.5rem;
  opacity: 0.3;
  color: var(--gold-dark);
}
</style>
