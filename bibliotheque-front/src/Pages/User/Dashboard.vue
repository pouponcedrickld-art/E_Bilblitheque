<script setup lang="ts">
// Importations Vue et utilitaires
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import http from '@/services/http'
import { BookOpen, Tags, PenTool, Users, Download, Eye, Hourglass } from '@lucide/vue'

// Store d'authentification et routeur
const authStore = useAuthStore()
const router = useRouter()

// Statistiques du tableau de bord
const stats = ref({
  references: 0,
  categories: 0,
  authors: 0,
  pendingRequests: 0,
  users: 0,
  downloads: 0,
  views: 0,
})

// État de chargement
const loading = ref(true)

// Titre dynamique selon le rôle
const dashboardTitle = computed(() => {
  const titles: Record<string, string> = {
    admin: 'Tableau de bord Administrateur',
    responsable_rh: 'Tableau de bord RH',
    responsable_demande: 'Tableau de bord Demandes',
    user: 'Mon espace',
  }
  return titles[authStore.user?.role ?? ''] || 'Tableau de bord'
})

// Récupère les statistiques depuis l'API
async function fetchStats() {
  loading.value = true
  try {
    const [refs, cats, authors, users, downloads, views] = await Promise.all([
      http.get('/references').catch(() => ({ data: { data: [] } })),
      http.get('/categories').catch(() => ({ data: { data: [] } })),
      http.get('/authors').catch(() => ({ data: { data: [] } })),
      (authStore.isAdmin || authStore.isResponsableRH) ? http.get('/users').catch(() => ({ data: { data: [] } })) : Promise.resolve({ data: { data: [] } }),
      (authStore.isAdmin || authStore.isResponsableRH) ? http.get('/downloads/stats').catch(() => ({ data: {} })) : Promise.resolve({ data: {} }),
      authStore.isAdmin ? http.get('/views/stats').catch(() => ({ data: {} })) : Promise.resolve({ data: {} }),
    ])

    stats.value = {
      references: (refs.data?.data ?? refs.data ?? []).length,
      categories: (cats.data?.data ?? cats.data ?? []).length,
      authors: (authors.data?.data ?? authors.data ?? []).length,
      pendingRequests: 0,
      users: (users.data?.data ?? users.data ?? []).length,
      downloads: downloads.data?.total_downloads ?? 0,
      views: views.data?.total_views ?? 0,
    }
  } catch {
    // silencieux
  } finally {
    loading.value = false
  }
}

// Cartes de statistiques selon le rôle
const roleCards = computed(() => {
  const cards: { label: string; value: number | string; icon: any }[] = []
  const s = stats.value

  if (authStore.isAdmin) {
    cards.push(
      { label: 'Références', value: s.references, icon: BookOpen },
      { label: 'Catégories', value: s.categories, icon: Tags },
      { label: 'Auteurs', value: s.authors, icon: PenTool },
      { label: 'Utilisateurs', value: s.users, icon: Users },
      { label: 'Téléchargements', value: s.downloads, icon: Download },
      { label: 'Consultations', value: s.views, icon: Eye },
    )
  } else if (authStore.isResponsableRH) {
    cards.push(
      { label: 'Références', value: s.references, icon: BookOpen },
      { label: 'Auteurs', value: s.authors, icon: PenTool },
      { label: 'Téléchargements', value: s.downloads, icon: Download },
    )
  } else if (authStore.isResponsableDemande) {
    cards.push(
      { label: 'Demandes en attente', value: s.pendingRequests || '0', icon: Hourglass },
      { label: 'Références', value: s.references, icon: BookOpen },
    )
  } else {
    cards.push(
      { label: 'Références', value: s.references, icon: BookOpen },
    )
  }

  return cards
})

// Charge les données au montage du composant
onMounted(fetchStats)
</script>

<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <div>
        <h1>{{ dashboardTitle }}</h1>
        <p class="greeting">Bonne journée, {{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
      </div>
    </div>

    <div v-if="loading" class="loading">Chargement des statistiques...</div>

    <template v-else>
      <div class="stats-grid">
        <div v-for="card in roleCards" :key="card.label" class="stat-card">
          <div class="stat-icon-box">
            <component :is="card.icon" :size="20" class="stat-svg" />
          </div>
          <div class="stat-body">
            <span class="stat-value">{{ card.value }}</span>
            <span class="stat-label">{{ card.label }}</span>
          </div>
        </div>
      </div>

      <div class="content-grid">
        <div class="card">
          <h2>Aperçu</h2>
          <div class="overview-stats">
            <div class="overview-item">
              <i class="pi pi-book" style="color: var(--primary);" />
              <div>
                <span class="overview-value">{{ stats.references }}</span>
                <span class="overview-label">Références disponibles</span>
              </div>
            </div>
            <div v-if="authStore.isAdmin || authStore.isResponsableRH" class="overview-item">
              <i class="pi pi-download" style="color: var(--accent);" />
              <div>
                <span class="overview-value">{{ stats.downloads }}</span>
                <span class="overview-label">Téléchargements</span>
              </div>
            </div>
            <div v-if="authStore.isAdmin" class="overview-item">
              <i class="pi pi-eye" style="color: var(--primary-light);" />
              <div>
                <span class="overview-value">{{ stats.views }}</span>
                <span class="overview-label">Consultations</span>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <h2>Actions rapides</h2>
          <div class="actions-list">
            <button v-if="authStore.isAdmin || authStore.isResponsableRH" class="action-btn" @click="router.push('/admin/references/create')">
              <i class="pi pi-plus" />
              <span>Nouvelle référence</span>
            </button>
            <button v-if="authStore.isAdmin" class="action-btn" @click="router.push('/admin/users')">
              <i class="pi pi-users" />
              <span>Gérer les utilisateurs</span>
            </button>
            <button v-if="authStore.isResponsableDemande || authStore.isUser" class="action-btn" @click="router.push('/user/deposits/create')">
              <i class="pi pi-upload" />
              <span>Faire une demande de dépôt</span>
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
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
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
  background: rgba(27, 67, 50, 0.1);
}

.stat-svg {
  color: var(--primary);
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

.overview-stats {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.overview-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 0;
  border-bottom: 1px solid var(--border);
}

.overview-item:last-child {
  border-bottom: none;
}

.overview-item i {
  font-size: 1.5rem;
  width: 2.5rem;
  text-align: center;
}

.overview-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--foreground);
  font-family: var(--font-serif);
  display: block;
  line-height: 1.2;
}

.overview-label {
  font-size: 0.8rem;
  color: var(--muted-foreground);
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
  .content-grid {
    grid-template-columns: 1fr 1fr;
  }

  .dashboard-header h1 {
    font-size: 2rem;
  }
}
</style>
