<script setup lang="ts">
// Liste des demandes de dépôt pour l'admin
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'

// Interface d'une demande de dépôt
interface DepositRequest {
  id: number
  title: string
  status: string
  applicant: { full_name?: string; name?: string } | null
  assigned_manager: { full_name?: string; name?: string } | null
  created_at: string
}

const router = useRouter()
// Demandes de dépôt et filtres
const items = ref<DepositRequest[]>([])
const loading = ref(false)
const globalFilter = ref('')

// Filtre les demandes par recherche textuelle
const filteredItems = computed(() => {
  if (!globalFilter.value) return items.value
  const q = globalFilter.value.toLowerCase()
  return items.value.filter(r =>
    r.title.toLowerCase().includes(q) ||
    (r.status || '').toLowerCase().includes(q) ||
    (r.applicant?.full_name || r.applicant?.name || '').toLowerCase().includes(q)
  )
})

// Récupère la liste des demandes
async function fetch() {
  loading.value = true
  try {
    const res = await http.get('/deposit-requests', { params: { per_page: 'all' } })
    items.value = res.data?.data ?? res.data ?? []
  } finally {
    loading.value = false
  }
}

// Charge les demandes au montage
onMounted(fetch)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Demandes de dépôt</h1>
      <Button icon="pi pi-refresh" label="Actualiser" severity="secondary" @click="fetch" />
    </div>

    <div class="toolbar">
      <IconField iconPosition="left">
        <InputIcon><i class="pi pi-search" /></InputIcon>
        <InputText v-model="globalFilter" placeholder="Rechercher..." class="search-input" />
      </IconField>
    </div>

    <DataTable
      :value="filteredItems"
      :loading="loading"
      striped-rows
      paginator
      :rows="20"
      :rows-per-page-options="[10, 20, 50]"
      sort-field="created_at"
      :sort-order="-1"
    >
      <Column field="title" header="Titre" sortable />
      <Column header="Demandeur" sortable>
        <template #body="{ data }">
          {{ data.applicant?.full_name ?? data.applicant?.name ?? '-' }}
        </template>
      </Column>
      <Column field="status" header="Statut" sortable>
        <template #body="{ data }">
          <StatusBadge :status="data.status" />
        </template>
      </Column>
      <Column header="Gestionnaire" sortable>
        <template #body="{ data }">
          {{ data.assigned_manager?.full_name ?? data.assigned_manager?.name ?? '-' }}
        </template>
      </Column>
      <Column field="created_at" header="Date" sortable>
        <template #body="{ data }">
          {{ new Date(data.created_at).toLocaleDateString() }}
        </template>
      </Column>
      <Column header="Actions" style="min-width: 10rem">
        <template #body="{ data }">
          <div class="actions">
            <Button
              icon="pi pi-eye"
              label="Examiner"
              severity="info"
              @click="router.push(`/admin/deposit-requests/${data.id}/review`)"
            />
            <Button
              icon="pi pi-history"
              severity="secondary"
              text
              @click="router.push('/admin/deposit-requests/history')"
            />
          </div>
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<style scoped>
.page { max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.actions { display: flex; gap: 0.5rem; }
.toolbar { display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: center; }
.search-input { min-width: 260px; }
@media (max-width: 640px) {
  .page { padding: 1rem; }
  .page-header { flex-wrap: wrap; gap: 0.5rem; }
  .page-header h1 { font-size: 1.1rem; }
  .toolbar { flex-direction: column; align-items: stretch; }
  .search-input { min-width: 0; width: 100%; }
  .actions { flex-direction: column; }
  .actions .p-button { width: 100%; }
}
</style>
