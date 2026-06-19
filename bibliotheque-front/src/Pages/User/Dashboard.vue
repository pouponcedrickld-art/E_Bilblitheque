<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import http from '@/services/http'

const authStore = useAuthStore()
const router = useRouter()

const stats = ref({
  references: 0,
  categories: 0,
  authors: 0,
  pendingRequests: 0,
  users: 0,
  downloads: 0,
  views: 0,
})

const loading = ref(true)

const dashboardTitle = computed(() => {
  const titles: Record<string, string> = {
    admin: 'Tableau de bord Administrateur',
    responsable_rh: 'Tableau de bord RH',
    responsable_demande: 'Tableau de bord Demandes',
    user: 'Mon espace',
  }
  return titles[authStore.user?.role ?? ''] || 'Tableau de bord'
})

async function fetchStats() {
  loading.value = true
  try {
    const [refs, cats, authors, users, downloads, views] = await Promise.all([
      http.get('/references').catch(() => ({ data: { data: [] } })),
      http.get('/categories').catch(() => ({ data: { data: [] } })),
      http.get('/authors').catch(() => ({ data: { data: [] } })),
      (authStore.isAdmin || authStore.isResponsableRH) ? http.get('/users').catch(() => ({ data: { data: [] } })) : Promise.resolve({ data: { data: [] } }),
      http.get('/downloads/stats').catch(() => ({ data: {} })),
      http.get('/views/stats').catch(() => ({ data: {} })),
    ])

    stats.value = {
      references: (refs.data?.data ?? refs.data ?? []).length,
      categories: (cats.data?.data ?? cats.data ?? []).length,
      authors: (authors.data?.data ?? authors.data ?? []).length,
      pendingRequests: 0,
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

const roleCards = computed(() => {
  const cards: { label: string; value: number | string; icon: string }[] = []
  const s = stats.value

  if (authStore.isAdmin) {
    cards.push(
      { label: 'Références', value: s.references, icon: '📚' },
      { label: 'Catégories', value: s.categories, icon: '🏷️' },
      { label: 'Auteurs', value: s.authors, icon: '✍️' },
      { label: 'Utilisateurs', value: s.users, icon: '👥' },
      { label: 'Téléchargements', value: s.downloads, icon: '⬇️' },
      { label: 'Consultations', value: s.views, icon: '👁️' },
    )
  } else if (authStore.isResponsableRH) {
    cards.push(
      { label: 'Références', value: s.references, icon: '📚' },
      { label: 'Auteurs', value: s.authors, icon: '✍️' },
      { label: 'Téléchargements', value: s.downloads, icon: '⬇️' },
    )
  } else if (authStore.isResponsableDemande) {
    cards.push(
      { label: 'Demandes en attente', value: s.pendingRequests || '0', icon: '⏳' },
      { label: 'Références', value: s.references, icon: '📚' },
    )
  } else {
    cards.push(
      { label: 'Références', value: s.references, icon: '📚' },
    )
  }

  return cards
})

onMounted(fetchStats)
</script>

<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <h1>{{ dashboardTitle }}</h1>
      <p>Bienvenue, {{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
    </div>

    <div v-if="loading" class="loading">Chargement des statistiques...</div>

    <div v-else class="stats-grid">
      <div v-for="card in roleCards" :key="card.label" class="stat-card">
        <div class="stat-icon">{{ card.icon }}</div>
        <div class="stat-body">
          <span class="stat-value">{{ card.value }}</span>
          <span class="stat-label">{{ card.label }}</span>
        </div>
      </div>
    </div>

    <div class="quick-links">
      <h2>Actions rapides</h2>
      <div class="links-grid">
        <button v-if="authStore.isAdmin || authStore.isResponsableRH" class="link-card" @click="router.push('/catalogue')">
          <i class="pi pi-plus" /> Nouvelle référence
        </button>
        <button v-if="authStore.isAdmin" class="link-card" @click="router.push('/admin/dashboard')">
          <i class="pi pi-users" /> Gérer les utilisateurs
        </button>
        <button v-if="authStore.isResponsableDemande || authStore.isUser" class="link-card">
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
.dashboard-header {
  margin-bottom: 1.5rem;
}

.dashboard-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
}

.dashboard-header p {
  color: var(--text-secondary);
  font-size: 0.9rem;
  margin-top: 0.25rem;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.stat-icon {
  font-size: 1.75rem;
}

.stat-body {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.2;
}

.stat-label {
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.quick-links h2 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.links-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 0.75rem;
}

.link-card {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 1rem;
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.15s;
  font-size: 0.9rem;
  font-weight: 500;
  text-align: left;
}

.link-card:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.link-card i {
  font-size: 1.1rem;
}
</style>
