<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Menubar from 'primevue/menubar'
import Avatar from 'primevue/avatar'
import Menu from 'primevue/menu'
import type { MenuItem } from 'primevue/menuitem'

const authStore = useAuthStore()
const router = useRouter()
// const route = useRoute()

const userMenu = ref<InstanceType<typeof Menu>>()
const userMenuItems = ref<MenuItem[]>([
  {
    label: 'Mon profil',
    icon: 'pi pi-user',
    command: () => router.push('/user/profile'),
  },
  {
    separator: true,
  },
  {
    label: 'Déconnexion',
    icon: 'pi pi-sign-out',
    command: async () => {
      await authStore.logout()
      router.push('/')
    },
  },
])

function go(to: string) {
  router.push(to)
}

const navItems = computed<MenuItem[]>(() => {
  if (!authStore.user) return []

  const role = authStore.user.role

  const dashboard = {
    label: 'Tableau de bord',
    icon: 'pi pi-home',
    command: () => go(authStore.getDashboardPath()),
  }

  if (role === 'admin') {
    return [
      dashboard,
      {
        label: 'Catalogue',
        icon: 'pi pi-book',
        items: [
          { label: 'Références', icon: 'pi pi-file', command: () => go('/admin/references') },
          { label: 'Catégories', icon: 'pi pi-tags', command: () => go('/admin/categories') },
          { label: 'Auteurs', icon: 'pi pi-pencil', command: () => go('/admin/authors') },
          { label: 'Éditeurs', icon: 'pi pi-building', command: () => go('/admin/publishers') },
          { label: 'Mots-clés', icon: 'pi pi-hashtag', command: () => go('/admin/keywords') },
        ],
      },
      {
        label: 'Demandes',
        icon: 'pi pi-inbox',
        command: () => go('/admin/deposit-requests'),
      },
      {
        label: 'Utilisateurs',
        icon: 'pi pi-users',
        command: () => go('/admin/users'),
      },
      {
        label: 'Journal',
        icon: 'pi pi-history',
        command: () => go('/admin/logs'),
      },
    ]
  }

  if (role === 'responsable_rh') {
    return [
      dashboard,
      { label: 'Catalogue', icon: 'pi pi-book', command: () => go('/catalogue') },
      { label: 'Utilisateurs', icon: 'pi pi-users', command: () => go('/rh/users') },
      { label: 'Activités', icon: 'pi pi-history', command: () => go('/rh/activity-logs') },
    ]
  }

  if (role === 'responsable_demande') {
    return [
      dashboard,
      { label: 'Demandes', icon: 'pi pi-inbox', command: () => go('/manager/requests') },
      { label: 'Catalogue', icon: 'pi pi-book', command: () => go('/catalogue') },
    ]
  }

  if (role === 'user') {
    return [
      dashboard,
      { label: 'Catalogue', icon: 'pi pi-book', command: () => go('/catalogue') },
      { label: 'Mes dépôts', icon: 'pi pi-upload', command: () => go('/user/deposits') },
      { label: 'Nouveau dépôt', icon: 'pi pi-plus', command: () => go('/user/deposits/create') },
    ]
  }

  return []
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

const email = computed(() => authStore.user?.email ?? '')

function toggleUserMenu(event: Event) {
  userMenu.value?.toggle(event)
}
</script>

<template>
  <div class="layout">
    <Menubar :model="navItems" class="app-menubar">
      <template #start>
        <router-link to="/" class="logo">
          <span class="logo-icon">📚</span>
          <span class="logo-text">BiblioNum</span>
        </router-link>
      </template>

      <template #end>
        <div v-if="authStore.isAuthenticated" class="user-section">
          <button class="user-trigger" @click="toggleUserMenu" aria-haspopup="true">
            <Avatar :label="initials" size="large" shape="circle" class="user-avatar" />
            <div class="user-meta">
              <span class="user-name">{{ fullName }}</span>
              <span class="user-role">{{ authStore.roleLabel[authStore.user?.role ?? ''] }}</span>
            </div>
            <i class="pi pi-chevron-down trigger-icon" />
          </button>
          <Menu ref="userMenu" id="user-menu" :model="userMenuItems" :popup="true" />
        </div>
        <div v-else class="auth-buttons">
          <router-link to="/login" class="btn btn-outline">Connexion</router-link>
          <router-link to="/register" class="btn btn-primary">Inscription</router-link>
        </div>
      </template>
    </Menubar>

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

.app-menubar {
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

.user-section {
  display: flex;
  align-items: center;
}

.user-trigger {
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

.user-trigger:hover {
  background: var(--bg);
}

.user-avatar {
  background: var(--primary) !important;
  color: #fff !important;
  font-weight: 600;
  font-size: 0.8rem;
}

.user-meta {
  display: flex;
  flex-direction: column;
  text-align: left;
  line-height: 1.2;
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

.trigger-icon {
  font-size: 0.7rem;
  color: var(--text-secondary);
}

.auth-buttons {
  display: flex;
  align-items: center;
  gap: 0.5rem;
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

@media (max-width: 768px) {
  .user-meta {
    display: none;
  }

  .trigger-icon {
    display: none;
  }
}
</style>
