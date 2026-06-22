<script setup lang="ts">
// Liste des demandes attribuées au gestionnaire
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'
import { formatDate } from '@/Utils/formatters'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'

const router = useRouter()

// Interface d'une demande de dépôt
interface DepositRequest {
  id: number
  title: string
  description: string | null
  status: string
  applicant: { id: number; first_name: string; last_name: string; full_name: string } | null
  created_at: string
}

// Demandes de dépôt
const requests = ref<DepositRequest[]>([])
const loading = ref(true)

// Récupère la liste des demandes attribuées
async function fetchRequests() {
  loading.value = true
  try {
    const res = await http.get('/deposit-requests')
    requests.value = res.data?.data ?? res.data ?? []
  } catch {
    // silencieux
  } finally {
    loading.value = false
  }
}

// Redirige vers la page d'examen
function goReview(id: number) {
  router.push(`/manager/requests/${id}/review`)
}

// Redirige vers la page de second avis
function goSecondOpinion(id: number) {
  router.push(`/manager/requests/${id}/second-opinion`)
}

// Charge les demandes au montage
onMounted(fetchRequests)
</script>

<template>
  <div class="index-page">
    <div class="page-header">
      <h1>Demandes attribuées</h1>
      <Button icon="pi pi-refresh" label="Actualiser" severity="secondary" text @click="fetchRequests" />
    </div>

    <DataTable
      :value="requests"
      :loading="loading"
      striped-rows
      paginator
      :rows="15"
      :rows-per-page-options="[10, 15, 25, 50]"
      sort-field="created_at"
      :sort-order="-1"
      class="requests-table"
    >
      <Column field="title" header="Titre" sortable style="min-width: 200px" />
      <Column header="Demandeur" sortable sort-field="applicant.full_name" style="min-width: 160px">
        <template #body="{ data }">
          {{ data.applicant?.full_name ?? 'N/A' }}
        </template>
      </Column>
      <Column header="Statut" style="min-width: 130px">
        <template #body="{ data }">
          <StatusBadge :status="data.status" />
        </template>
      </Column>
      <Column field="created_at" header="Date" sortable style="min-width: 120px">
        <template #body="{ data }">
          {{ formatDate(data.created_at) }}
        </template>
      </Column>
      <Column header="Actions" style="min-width: 180px">
        <template #body="{ data }">
          <div class="actions">
            <Button
              icon="pi pi-eye"
              label="Examiner"
              severity="info"
              size="small"
              @click="goReview(data.id)"
            />
            <Button
              v-if="data.status === 'second_review'"
              icon="pi pi-comment"
              label="Second avis"
              severity="warn"
              size="small"
              @click="goSecondOpinion(data.id)"
            />
          </div>
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<style scoped>
.index-page {
  max-width: 1200px;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.page-header h1 {
  font-size: 1.4rem;
  font-weight: 700;
}

.requests-table {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
}

.actions {
  display: flex;
  gap: 0.5rem;
}
</style>
