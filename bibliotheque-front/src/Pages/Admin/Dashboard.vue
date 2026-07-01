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
      references: refs.data?.total ?? (refs.data?.data ?? refs.data ?? []).length,
      categories: cats.data?.total ?? (cats.data?.data ?? cats.data ?? []).length,
      authors: authors.data?.total ?? (authors.data?.data ?? authors.data ?? []).length,
      users: users.data?.total ?? (users.data?.data ?? users.data ?? []).length,
      downloads: downloads.data?.total_downloads ?? 0,
      views: views.data?.total_views ?? 0,
    }
  } catch {
  } finally {
    loading.value = false
  }
}

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

    <div v-if="loading" class="loading-spinner">
      <i class="pi pi-spin pi-spinner" />
      <p>Chargement des statistiques...</p>
    </div>

    <template v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon" style="background: rgba(200,164,92,0.12); color: var(--gold-dark);">
            <i class="pi pi-book" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.references }}</span>
            <span class="stat-label">Références</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: rgba(200,164,92,0.12); color: var(--gold-dark);">
            <i class="pi pi-tags" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.categories }}</span>
            <span class="stat-label">Catégories</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: rgba(200,164,92,0.12); color: var(--gold-dark);">
            <i class="pi pi-pencil" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.authors }}</span>
            <span class="stat-label">Auteurs</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: rgba(200,164,92,0.12); color: var(--gold-dark);">
            <i class="pi pi-users" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.users }}</span>
            <span class="stat-label">Utilisateurs</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: rgba(200,164,92,0.12); color: var(--gold-dark);">
            <i class="pi pi-download" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.downloads }}</span>
            <span class="stat-label">Téléchargements</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: rgba(200,164,92,0.12); color: var(--gold-dark);">
            <i class="pi pi-eye" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.views }}</span>
            <span class="stat-label">Consultations</span>
          </div>
        </div>
      </div>

      <div class="content-grid">
        <div class="card-section">
          <h2>Répartition</h2>
          <hr class="gold-rule-left" />
          <div class="bar-chart">
            <div class="bar-item">
              <span class="bar-label">Références</span>
              <div class="bar-track">
                <div class="bar-fill" :style="{ width: Math.min(100, (stats.references / Math.max(...Object.values(stats), 1)) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.references }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Catégories</span>
              <div class="bar-track">
                <div class="bar-fill bar-fill--gold" :style="{ width: Math.min(100, (stats.categories / Math.max(...Object.values(stats), 1)) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.categories }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Auteurs</span>
              <div class="bar-track">
                <div class="bar-fill bar-fill--light" :style="{ width: Math.min(100, (stats.authors / Math.max(...Object.values(stats), 1)) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.authors }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Utilisateurs</span>
              <div class="bar-track">
                <div class="bar-fill bar-fill--primary" :style="{ width: Math.min(100, (stats.users / Math.max(...Object.values(stats), 1)) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.users }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Téléchargements</span>
              <div class="bar-track">
                <div class="bar-fill bar-fill--burgundy" :style="{ width: Math.min(100, (stats.downloads / Math.max(...Object.values(stats), 1)) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.downloads }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Consultations</span>
              <div class="bar-track">
                <div class="bar-fill bar-fill--leather" :style="{ width: Math.min(100, (stats.views / Math.max(...Object.values(stats), 1)) * 100) + '%' }" />
              </div>
              <span class="bar-value">{{ stats.views }}</span>
            </div>
          </div>
        </div>

        <div class="card-section">
          <h2>Actions rapides</h2>
          <hr class="gold-rule-left" />
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
  max-width: 1200px;
  margin: 0 auto;
}

.dashboard-header {
  margin-bottom: 2rem;
}

.dashboard-header h1 {
  font-family: var(--font-serif);
  font-weight: 700;
  font-size: 1.75rem;
}

.greeting {
  font-size: 0.9rem;
  color: var(--muted-foreground);
  margin-top: 0.25rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.85rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.25s ease;
}
.stat-card:hover {
  border-color: var(--border-gold);
  box-shadow: 0 4px 16px rgba(200,164,92,0.1);
  transform: translateY(-2px);
}

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1.15rem;
}

.stat-body { display: flex; flex-direction: column; min-width: 0; }

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.2;
  font-family: var(--font-serif);
}
.stat-label {
  font-size: 0.75rem;
  color: var(--muted-foreground);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-top: 0.1rem;
}

.content-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}

.card-section {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  transition: all 0.25s ease;
}
.card-section:hover {
  border-color: var(--border-gold);
}
.card-section h2 {
  font-family: var(--font-serif);
  font-weight: 600;
  font-size: 1.1rem;
  margin-bottom: 0;
}

.bar-chart {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 0.75rem;
}

.bar-item {
  display: grid;
  grid-template-columns: 120px 1fr 50px;
  align-items: center;
  gap: 0.75rem;
}

.bar-label {
  font-size: 0.8rem;
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
  transition: width 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.bar-fill--gold { background: var(--gold); }
.bar-fill--light { background: var(--primary-light); }
.bar-fill--primary { background: var(--primary); }
.bar-fill--burgundy { background: var(--burgundy); }
.bar-fill--leather { background: var(--leather); }

.bar-value {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--foreground);
  text-align: right;
  font-family: var(--font-serif);
}

.actions-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.75rem;
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
  transition: all 0.2s;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--foreground);
  text-align: left;
  width: 100%;
  font-family: var(--font-sans);
}
.action-btn i {
  font-size: 1rem;
  color: var(--gold-dark);
  width: 1.25rem;
  text-align: center;
}
.action-btn:hover {
  border-color: var(--gold);
  background: rgba(200, 164, 92, 0.05);
  transform: translateX(3px);
}

@media (min-width: 768px) {
  .stats-grid { grid-template-columns: repeat(3, 1fr); }
  .content-grid { grid-template-columns: 1fr 1fr; }
  .dashboard-header h1 { font-size: 2rem; }
}
@media (min-width: 1024px) {
  .stats-grid { grid-template-columns: repeat(4, 1fr); }
  .stat-card:nth-child(5),
  .stat-card:nth-child(6) { grid-column: span 2; }
}
@media (min-width: 1200px) {
  .stats-grid { grid-template-columns: repeat(6, 1fr); }
  .stat-card:nth-child(5),
  .stat-card:nth-child(6) { grid-column: auto; }
}
</style>
