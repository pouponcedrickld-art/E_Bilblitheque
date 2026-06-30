<script setup lang="ts">
// Historique détaillé des demandes de dépôt
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import Button from 'primevue/button'
import Timeline from 'primevue/timeline'
import Card from 'primevue/card'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'

// Événement d'historique avec les avis associés
interface HistoryEvent {
  id: number
  title: string
  status: string
  applicant: { full_name?: string; name?: string } | null
  created_at: string
  reviews?: { created_at: string; status: string; reviewer: { full_name?: string; name?: string } | null; justification?: string }[]
}

const router = useRouter()
// Liste des événements historiques
const events = ref<HistoryEvent[]>([])
const loading = ref(true)

// Charge l'historique complet avec les avis (déjà chargés via eager loading)
async function fetchHistory() {
  loading.value = true
  try {
    const res = await http.get('/deposit-requests', { params: { per_page: 'all' } })
    const requests = res.data?.data ?? res.data ?? []
    events.value = requests
      .map((req: any) => ({
        id: req.id,
        title: req.title,
        status: req.status,
        applicant: req.applicant,
        created_at: req.created_at,
        reviews: (req.reviews ?? []).sort(
          (a: any, b: any) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
        ),
      }))
      .sort(
        (a: any, b: any) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      )
  } finally {
    loading.value = false
  }
}


// Retourne l'icône correspondant au statut
function statusTransitionIcon(status: string): string {
  const icons: Record<string, string> = {
    pending: 'pi pi-clock',
    approved_by_manager: 'pi pi-check-circle',
    rejected_by_manager: 'pi pi-times-circle',
    second_review: 'pi pi-sync',
    published: 'pi pi-send',
    rejected: 'pi pi-ban',
  }
  return icons[status] || 'pi pi-circle'
}

// Charge l'historique au montage
onMounted(fetchHistory)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Historique des demandes de dépôt</h1>
      <div class="header-actions">
        <Button icon="pi pi-refresh" label="Actualiser" severity="secondary" @click="fetchHistory" />
        <Button icon="pi pi-arrow-left" label="Retour" severity="secondary" text @click="router.push('/admin/deposit-requests')" />
      </div>
    </div>

    <div v-if="loading" class="loading">Chargement...</div>

    <div v-else-if="!events.length" class="empty">Aucun historique.</div>

    <div v-else class="events-list">
      <Card v-for="event in events" :key="event.id" class="event-card">
        <template #title>
          <div class="event-title">
            <span>{{ event.title }}</span>
            <StatusBadge :status="event.status" />
          </div>
        </template>
        <template #subtitle>
          {{ event.applicant?.full_name ?? event.applicant?.name ?? 'N/A' }} - {{ new Date(event.created_at).toLocaleDateString() }}
        </template>
        <template #content>
          <div v-if="event.reviews && event.reviews.length" class="timeline-wrapper">
            <Timeline :value="event.reviews" layout="vertical" align="left">
              <template #marker="{ item }">
                <i :class="statusTransitionIcon(item.status)" />
              </template>
              <template #content="{ item }">
                <div class="timeline-item">
                  <div class="timeline-header">
                    <StatusBadge :status="item.status" />
                    <span class="timeline-reviewer">{{ item.reviewer?.full_name ?? item.reviewer?.name ?? 'Système' }}</span>
                    <span class="timeline-date">{{ new Date(item.created_at).toLocaleString() }}</span>
                  </div>
                  <p v-if="item.justification" class="timeline-justification">{{ item.justification }}</p>
                </div>
              </template>
            </Timeline>
          </div>
          <div v-else class="no-reviews">Aucun avis enregistré.</div>
        </template>
      </Card>
    </div>
  </div>
</template>

<style scoped>
.page { max-width: 900px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.header-actions { display: flex; gap: 0.5rem; }
.loading { text-align: center; padding: 2rem; color: var(--text-secondary); }
.empty { text-align: center; padding: 3rem; color: var(--text-secondary); }
.events-list { display: flex; flex-direction: column; gap: 1rem; }
.event-title { display: flex; align-items: center; gap: 0.75rem; }
.event-card :deep(.p-card-subtitle) { font-size: 0.85rem; color: var(--text-secondary); }
.timeline-wrapper { margin-top: 0.5rem; }
.timeline-item { padding: 0.25rem 0; }
.timeline-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; flex-wrap: wrap; }
.timeline-reviewer { font-weight: 600; font-size: 0.85rem; }
.timeline-date { font-size: 0.8rem; color: var(--text-secondary); margin-left: auto; }
.timeline-justification { font-size: 0.9rem; margin: 0.25rem 0 0; color: var(--text-primary); }
.no-reviews { font-size: 0.85rem; color: var(--text-secondary); font-style: italic; }
</style>
