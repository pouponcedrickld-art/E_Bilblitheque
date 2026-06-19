<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import http from '@/services/http'

const authStore = useAuthStore()
const router = useRouter()

const stats = ref({
  references: 0, categories: 0, authors: 0,
  users: 0, downloads: 0, views: 0,
})
const loading = ref(true)

async function fetchStats() {
  loading.value = true
  try {
    const [refs, cats, authors, users, downloads, views] = await Promise.all([
      http.get('/references').catch(() => ({ data: { data: [] } })),
      http.get('/categories').catch(() => ({ data: { data: [] } })),
      http.get('/authors').catch(() => ({ data: { data: [] } })),
      http.get('/users').catch(() => ({ data: { data: [] } })),
      http.get('/downloads/stats').catch(() => ({ data: {} })),
      http.get('/views/stats').catch(() => ({ data: {} })),
    ])
    stats.value = {
      references: (refs.data?.data ?? refs.data ?? []).length,
      categories: (cats.data?.data ?? cats.data ?? []).length,
      authors: (authors.data?.data ?? authors.data ?? []).length,
      users: (users.data?.data ?? users.data ?? []).length,
      downloads: downloads.data?.total ?? 0,
      views: views.data?.total ?? 0,
    }
  } catch {
    // silencieux
  } finally {
    loading.value = false
  }
}

onMounted(fetchStats)
</script>

<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <h1>Tableau de bord Administrateur</h1>
      <p>Bienvenue, {{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
    </div>

    <div v-if="loading" class="loading">Chargement des statistiques...</div>

    <div v-else class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-body">
          <span class="stat-value">{{ stats.references }}</span>
          <span class="stat-label">Références</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🏷️</div>
        <div class="stat-body">
          <span class="stat-value">{{ stats.categories }}</span>
          <span class="stat-label">Catégories</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✍️</div>
        <div class="stat-body">
          <span class="stat-value">{{ stats.authors }}</span>
          <span class="stat-label">Auteurs</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-body">
          <span class="stat-value">{{ stats.users }}</span>
          <span class="stat-label">Utilisateurs</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⬇️</div>
        <div class="stat-body">
          <span class="stat-value">{{ stats.downloads }}</span>
          <span class="stat-label">Téléchargements</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">👁️</div>
        <div class="stat-body">
          <span class="stat-value">{{ stats.views }}</span>
          <span class="stat-label">Consultations</span>
        </div>
      </div>
    </div>

    <div class="quick-links">
      <h2>Actions rapides</h2>
      <div class="links-grid">
        <button class="link-card" @click="router.push('/admin/references/create')">
          <i class="pi pi-plus" /> Nouvelle référence
        </button>
        <button class="link-card" @click="router.push('/admin/users')">
          <i class="pi pi-users" /> Gérer les utilisateurs
        </button>
        <button class="link-card" @click="router.push('/catalogue')">
          <i class="pi pi-search" /> Parcourir le catalogue
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-header {
  margin-bottom: 1.5rem;
}
.dashboard-header h1 { font-size: 1.5rem; font-weight: 700; }
.dashboard-header p { color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem; }
.loading { text-align: center; padding: 2rem; color: var(--text-secondary); }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.stat-card { background: #fff; border: 1px solid var(--border); border-radius: 0.5rem; padding: 1.25rem; display: flex; align-items: center; gap: 0.75rem; }
.stat-icon { font-size: 1.75rem; }
.stat-body { display: flex; flex-direction: column; }
.stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }
.stat-label { font-size: 0.8rem; color: var(--text-secondary); }
.quick-links h2 { font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; }
.links-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem; }
.link-card { display: flex; align-items: center; gap: 0.6rem; padding: 1rem; background: #fff; border: 1px solid var(--border); border-radius: 0.5rem; cursor: pointer; transition: all 0.15s; font-size: 0.9rem; font-weight: 500; text-align: left; }
.link-card:hover { border-color: var(--primary); color: var(--primary); }
.link-card i { font-size: 1.1rem; }
</style>
