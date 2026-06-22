<script setup lang="ts">
// Composant du tableau de bord admin - statistiques globales
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import http from '@/services/http'

const authStore = useAuthStore()
const router = useRouter()

// Statistiques du tableau de bord
const stats = ref({
  references: 0, categories: 0, authors: 0,
  users: 0, downloads: 0, views: 0,
})
const loading = ref(true)

// Charge les statistiques depuis différents endpoints
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

// Charge les stats au montage
onMounted(fetchStats)
</script>

<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <div>
        <h1>Tableau de bord</h1>
        <p class="greeting">Bonne journée, {{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
      </div>
    </div>

    <div v-if="loading" class="loading">Chargement des statistiques...</div>

    <template v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon-box" style="background: rgba(27, 67, 50, 0.1); color: var(--primary);">
            <i class="pi pi-book" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.references }}</span>
            <span class="stat-label">Références</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-box" style="background: rgba(45, 106, 79, 0.1); color: var(--primary-light);">
            <i class="pi pi-tags" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.categories }}</span>
            <span class="stat-label">Catégories</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-box" style="background: rgba(64, 145, 108, 0.1); color: var(--primary-lighter);">
            <i class="pi pi-pencil" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.authors }}</span>
            <span class="stat-label">Auteurs</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-box" style="background: rgba(82, 183, 136, 0.1); color: #52b788;">
            <i class="pi pi-users" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.users }}</span>
            <span class="stat-label">Utilisateurs</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-box" style="background: rgba(52, 199, 89, 0.1); color: var(--accent);">
            <i class="pi pi-download" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.downloads }}</span>
            <span class="stat-label">Téléchargements</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-box" style="background: rgba(45, 106, 79, 0.1); color: var(--primary-light);">
            <i class="pi pi-eye" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.views }}</span>
            <span class="stat-label">Consultations</span>
          </div>
        </div>
      </div>

      <div class="content-grid">
        <div class="card">
          <h2>Répartition</h2>
          <div class="bar-chart">
            <div class="bar-item">
              <span class="bar-label">Références</span>
              <div class="bar-track">
                <div class="bar-fill" :style="{ width: Math.min(100, (stats.references / Math.max(...Object.values(stats))) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.references }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Catégories</span>
              <div class="bar-track">
                <div class="bar-fill bar-fill--orange" :style="{ width: Math.min(100, (stats.categories / Math.max(...Object.values(stats))) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.categories }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Auteurs</span>
              <div class="bar-track">
                <div class="bar-fill bar-fill--teal" :style="{ width: Math.min(100, (stats.authors / Math.max(...Object.values(stats))) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.authors }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Utilisateurs</span>
              <div class="bar-track">
                <div class="bar-fill bar-fill--blue" :style="{ width: Math.min(100, (stats.users / Math.max(...Object.values(stats))) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.users }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Téléchargements</span>
              <div class="bar-track">
                <div class="bar-fill bar-fill--green" :style="{ width: Math.min(100, (stats.downloads / Math.max(...Object.values(stats))) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.downloads }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Consultations</span>
              <div class="bar-track">
                <div class="bar-fill bar-fill--purple" :style="{ width: Math.min(100, (stats.views / Math.max(...Object.values(stats))) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.views }}</span>
            </div>
          </div>
        </div>

        <div class="card">
          <h2>Actions rapides</h2>
          <div class="actions-list">
            <button class="action-btn" @click="router.push('/admin/references/create')">
              <i class="pi pi-plus" />
              <span>Nouvelle référence</span>
            </button>
            <button class="action-btn" @click="router.push('/admin/users')">
              <i class="pi pi-users" />
              <span>Gérer les utilisateurs</span>
            </button>
            <button class="action-btn" @click="router.push('/catalogue')">
              <i class="pi pi-search" />
              <span>Parcourir le catalogue</span>
            </button>
            <button class="action-btn" @click="router.push('/admin/deposit-requests')">
              <i class="pi pi-inbox" />
              <span>Demandes de dépôt</span>
            </button>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.dashboard {
  padding: 1.5rem;
  max-width: 1200px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 2rem;
}

.dashboard-header h1 {
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.75rem;
  color: var(--foreground);
}

.greeting {
  font-size: 0.95rem;
  color: var(--muted-foreground);
  margin-top: 0.25rem;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: var(--muted-foreground);
  font-size: 0.95rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  transition: box-shadow 0.2s;
}

.stat-card:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.stat-icon-box {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: var(--radius-xl);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1.15rem;
}

.stat-body {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.2;
  color: var(--foreground);
  font-family: var(--font-serif);
}

.stat-label {
  font-size: 0.8rem;
  color: var(--muted-foreground);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.content-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

.card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.card h2 {
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.1rem;
  margin-bottom: 1.25rem;
  color: var(--foreground);
}

.bar-chart {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.bar-item {
  display: grid;
  grid-template-columns: 120px 1fr 40px;
  align-items: center;
  gap: 0.75rem;
}

.bar-label {
  font-size: 0.85rem;
  color: var(--muted-foreground);
  white-space: nowrap;
}

.bar-track {
  height: 8px;
  background: var(--muted);
  border-radius: 999px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  border-radius: 999px;
  background: var(--primary);
  transition: width 0.6s ease;
}

.bar-fill--orange { background: #52b788; }
.bar-fill--teal { background: #40916C; }
.bar-fill--blue { background: var(--primary); }
.bar-fill--green { background: var(--accent); }
.bar-fill--purple { background: #74c69d; }

.bar-value {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--foreground);
  text-align: right;
}

.actions-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  cursor: pointer;
  transition: all 0.15s;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--foreground);
  text-align: left;
  width: 100%;
}

.action-btn i {
  font-size: 1rem;
  color: var(--primary);
  width: 1.25rem;
  text-align: center;
}

.action-btn:hover {
  border-color: var(--primary);
  background: rgba(27, 67, 50, 0.03);
}

@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(3, 1fr);
  }

  .content-grid {
    grid-template-columns: 1fr 1fr;
  }

  .dashboard-header h1 {
    font-size: 2rem;
  }
}

@media (min-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(4, 1fr);
  }

  .stat-card:nth-child(5),
  .stat-card:nth-child(6) {
    grid-column: span 2;
  }
}

@media (min-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(6, 1fr);
  }

  .stat-card:nth-child(5),
  .stat-card:nth-child(6) {
    grid-column: auto;
  }
}
</style>
