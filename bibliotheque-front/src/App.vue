<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Toast from 'primevue/toast'
</script>

<template>
  <Toast position="top-right" />
  <AppLayout>
    <router-view />
  </AppLayout>
</template>

<style>
:root {
  --sidebar-width: 260px;
  --navbar-height: 60px;
  --primary: #3b82f6;
  --primary-dark: #2563eb;
  --bg: #f8fafc;
  --sidebar-bg: #1e293b;
  --sidebar-text: #cbd5e1;
  --sidebar-active: #3b82f6;
  --text-primary: #1e293b;
  --text-secondary: #64748b;
  --border: #e2e8f0;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  background: var(--bg);
  color: var(--text-primary);
  -webkit-font-smoothing: antialiased;
}

a {
  text-decoration: none;
  color: inherit;
}
</style>
