<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import http from '@/services/http'

const authStore = useAuthStore()
const router = useRouter()

const stats = ref({ pendingRequests: 0, references: 0 })
const loading = ref(true)

async function fetchStats() {
  loading.value = true
  try {
    const refs = await http.get('/references').catch(() => ({ data: { data: [] } }))
    stats.value = {
      pendingRequests: 0,
      references: (refs.data?.data ?? refs.data ?? []).length,
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
      <h1>Tableau de bord Demandes</h1>
      <p>Bienvenue, {{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
    </div>

    <div v-if="loading" class="loading">Chargement...</div>

    <div v-else class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-body">
          <span class="stat-value">{{ stats.pendingRequests }}</span>
          <span class="stat-label">Demandes en attente</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-body">
          <span class="stat-value">{{ stats.references }}</span>
          <span class="stat-label">Références</span>
        </div>
      </div>
    </div>

    <div class="quick-links">
      <h2>Actions rapides</h2>
      <div class="links-grid">
        <button class="link-card">
          <i class="pi pi-upload" /> Faire une demande de dépôt
        </button>
        <button class="link-card" @click="router.push('/catalogue')">
          <i class="pi pi-search" /> Parcourir le catalogue
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.dashboard-header { margin-bottom: 1.5rem; }
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
