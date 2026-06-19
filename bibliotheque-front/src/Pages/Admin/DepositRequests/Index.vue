<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'

interface DepositRequest {
  id: number
  title: string
  status: string
  applicant: { full_name?: string; name?: string } | null
  manager: { full_name?: string; name?: string } | null
  created_at: string
}

const router = useRouter()
const items = ref<DepositRequest[]>([])
const loading = ref(false)

async function fetch() {
  loading.value = true
  try {
    const res = await http.get('/deposit-requests')
    items.value = res.data?.data ?? res.data ?? []
  } finally {
    loading.value = false
  }
}

onMounted(fetch)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Demandes de dépôt</h1>
      <Button icon="pi pi-refresh" label="Actualiser" severity="secondary" @click="fetch" />
    </div>

    <DataTable
      :value="items"
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
          {{ data.manager?.full_name ?? data.manager?.name ?? '-' }}
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
</style>
