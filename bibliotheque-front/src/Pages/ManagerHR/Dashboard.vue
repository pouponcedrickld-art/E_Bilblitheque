<script setup lang="ts">
// Tableau de bord RH
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import http from '@/services/http'

interface User {
  status: string
}

// Statistiques des utilisateurs
interface UserStats {
  total: number
  active: number
  suspended: number
}

const authStore = useAuthStore()
const router = useRouter()

// Statistiques et état de chargement
const stats = ref<UserStats>({ total: 0, active: 0, suspended: 0 })
const loading = ref(true)

// Calcule les statistiques à partir des utilisateurs
async function fetchStats() {
  loading.value = true
  try {
    const response = await http.get('/users')
    const users: User[] = response.data?.data ?? response.data ?? []
    stats.value = {
      total: users.length,
      active: users.filter((u) => u.status === 'active').length,
      suspended: users.filter((u) => u.status === 'suspended').length,
    }
  } catch {
    stats.value = { total: 0, active: 0, suspended: 0 }
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
        <h1>Tableau de bord RH</h1>
        <p class="greeting">Bonne journée, {{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
      </div>
    </div>

    <div v-if="loading" class="loading">Chargement des statistiques...</div>

    <template v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon-box" style="background: rgba(27, 67, 50, 0.1); color: var(--primary);">
            <i class="pi pi-users" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.total }}</span>
            <span class="stat-label">Total utilisateurs</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-box" style="background: rgba(52, 199, 89, 0.1); color: var(--accent);">
            <i class="pi pi-check-circle" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.active }}</span>
            <span class="stat-label">Actifs</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-box" style="background: rgba(255, 59, 48, 0.1); color: var(--destructive);">
            <i class="pi pi-ban" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ stats.suspended }}</span>
            <span class="stat-label">Suspendus</span>
          </div>
        </div>
      </div>

      <div class="content-grid">
        <div class="card">
          <h2>Répartition des comptes</h2>
          <div class="bar-chart">
            <div class="bar-item">
              <span class="bar-label">Actifs</span>
              <div class="bar-track">
                <div
                  class="bar-fill bar-fill--green"
                  :style="{ width: stats.total > 0 ? (stats.active / stats.total) * 100 + '%' : '0%' }"
                />
              </div>
              <span class="bar-value">{{ stats.active }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Suspendus</span>
              <div class="bar-track">
                <div
                  class="bar-fill bar-fill--red"
                  :style="{ width: stats.total > 0 ? (stats.suspended / stats.total) * 100 + '%' : '0%' }"
                />
              </div>
              <span class="bar-value">{{ stats.suspended }}</span>
            </div>
          </div>
          <div v-if="stats.total > 0" class="donut-wrapper">
            <div class="donut">
              <svg viewBox="0 0 36 36" class="donut-svg">
                <path
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                  fill="none"
                  stroke="var(--muted)"
                  stroke-width="3"
                />
                <path
                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                  fill="none"
                  stroke="var(--accent)"
                  stroke-width="3"
                  :stroke-dasharray="`${(stats.active / stats.total) * 100} ${100 - (stats.active / stats.total) * 100}`"
                />
              </svg>
              <div class="donut-center">
                <span class="donut-pct">{{ Math.round((stats.active / stats.total) * 100) }}%</span>
                <span class="donut-label">actifs</span>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <h2>Actions rapides</h2>
          <div class="actions-list">
            <button class="action-btn" @click="router.push('/rh/users')">
              <i class="pi pi-users" />
              <span>Gérer les utilisateurs</span>
            </button>
            <button class="action-btn" @click="router.push('/rh/users/create')">
              <i class="pi pi-user-plus" />
              <span>Nouvel utilisateur</span>
            </button>
            <button class="action-btn" @click="router.push('/rh/activity-logs')">
              <i class="pi pi-history" />
              <span>Journal d'activité</span>
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
  margin-bottom: 1.5rem;
}

.bar-item {
  display: grid;
  grid-template-columns: 100px 1fr 40px;
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
  transition: width 0.6s ease;
}

.bar-fill--green { background: var(--accent); }
.bar-fill--red { background: var(--destructive); }

.bar-value {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--foreground);
  text-align: right;
}

.donut-wrapper {
  display: flex;
  justify-content: center;
  padding-top: 0.5rem;
}

.donut {
  position: relative;
  width: 120px;
  height: 120px;
}

.donut-svg {
  width: 100%;
  height: 100%;
  transform: rotate(-90deg);
}

.donut-center {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.donut-pct {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--foreground);
  font-family: var(--font-serif);
  line-height: 1;
}

.donut-label {
  font-size: 0.75rem;
  color: var(--muted-foreground);
  margin-top: 0.15rem;
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
</style>
