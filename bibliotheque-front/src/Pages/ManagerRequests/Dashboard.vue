<script setup lang="ts">
// Tableau de bord des demandes pour le gestionnaire
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import http from '@/services/http'

const authStore = useAuthStore()
const router = useRouter()

// Statistiques des demandes de dépôt
interface RequestStats {
  total: number
  pending: number
  approved: number
  rejected: number
}

// Statistiques et état de chargement
const stats = ref<RequestStats>({ total: 0, pending: 0, approved: 0, rejected: 0 })
const loading = ref(true)

// Calcule les stats à partir des demandes attribuées
async function fetchStats() {
  loading.value = true
  try {
    const res = await http.get('/deposit-requests')
    const requests: any[] = res.data?.data ?? res.data ?? []
    stats.value = {
      total: requests.length,
      pending: requests.filter(r => r.status === 'pending').length,
      approved: requests.filter(r => r.status === 'approved_by_manager').length,
      rejected: requests.filter(r => r.status === 'rejected_by_manager').length,
    }
  } catch {
    // silencieux
  } finally {
    loading.value = false
  }
}

// Cartes de statistiques pour l'affichage
const statCards = computed(() => [
  { label: 'Total attribuées', value: stats.value.total, icon: 'pi pi-inbox', color: 'var(--primary)', bg: 'rgba(27, 67, 50, 0.1)' },
  { label: 'En attente', value: stats.value.pending, icon: 'pi pi-hourglass', color: '#52b788', bg: 'rgba(82, 183, 136, 0.1)' },
  { label: 'Validées', value: stats.value.approved, icon: 'pi pi-check-circle', color: 'var(--accent)', bg: 'rgba(52, 199, 89, 0.1)' },
  { label: 'Refusées', value: stats.value.rejected, icon: 'pi pi-times-circle', color: 'var(--destructive)', bg: 'rgba(255, 59, 48, 0.1)' },
])

// Charge les stats au montage
onMounted(fetchStats)
</script>

<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <div>
        <h1>Tableau de bord Demandes</h1>
        <p class="greeting">Bonne journée, {{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
      </div>
    </div>

    <div v-if="loading" class="loading">Chargement des statistiques...</div>

    <template v-else>
      <div class="stats-grid">
        <div v-for="card in statCards" :key="card.label" class="stat-card">
          <div class="stat-icon-box" :style="{ background: card.bg, color: card.color }">
            <i :class="card.icon" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ card.value }}</span>
            <span class="stat-label">{{ card.label }}</span>
          </div>
        </div>
      </div>

      <div class="content-grid">
        <div class="card">
          <h2>Répartition des demandes</h2>
          <div class="bar-chart" v-if="stats.total > 0">
            <div class="bar-item">
              <span class="bar-label">En attente</span>
              <div class="bar-track">
                <div
                  class="bar-fill bar-fill--orange"
                  :style="{ width: (stats.pending / stats.total) * 100 + '%' }"
                />
              </div>
              <span class="bar-value">{{ stats.pending }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Validées</span>
              <div class="bar-track">
                <div
                  class="bar-fill bar-fill--green"
                  :style="{ width: (stats.approved / stats.total) * 100 + '%' }"
                />
              </div>
              <span class="bar-value">{{ stats.approved }}</span>
            </div>
            <div class="bar-item">
              <span class="bar-label">Refusées</span>
              <div class="bar-track">
                <div
                  class="bar-fill bar-fill--red"
                  :style="{ width: (stats.rejected / stats.total) * 100 + '%' }"
                />
              </div>
              <span class="bar-value">{{ stats.rejected }}</span>
            </div>
          </div>
          <div v-else class="empty-chart">
            <i class="pi pi-inbox" />
            <p>Aucune demande pour le moment</p>
          </div>
        </div>

        <div class="card">
          <h2>Actions rapides</h2>
          <div class="actions-list">
            <button class="action-btn" @click="router.push('/manager/requests')">
              <i class="pi pi-inbox" />
              <span>Voir toutes les demandes</span>
            </button>
            <button v-if="stats.pending > 0" class="action-btn" @click="router.push('/manager/requests')">
              <i class="pi pi-verified" />
              <span>Examiner les demandes ({{ stats.pending }})</span>
            </button>
            <button class="action-btn" @click="router.push('/catalogue')">
              <i class="pi pi-search" />
              <span>Parcourir le catalogue</span>
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

.bar-fill--orange { background: #52b788; }
.bar-fill--green { background: var(--accent); }
.bar-fill--red { background: var(--destructive); }

.bar-value {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--foreground);
  text-align: right;
}

.empty-chart {
  text-align: center;
  padding: 2rem;
  color: var(--muted-foreground);
}

.empty-chart i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  opacity: 0.4;
}

.empty-chart p {
  font-size: 0.9rem;
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
    grid-template-columns: repeat(4, 1fr);
  }

  .content-grid {
    grid-template-columns: 1fr 1fr;
  }

  .dashboard-header h1 {
    font-size: 2rem;
  }
}
</style>
