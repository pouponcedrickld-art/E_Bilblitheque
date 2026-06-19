<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useDepositRequestsStore } from '@/stores/depositRequests'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'
import { formatDate } from '@/Utils/formatters'

const router = useRouter()
const store = useDepositRequestsStore()

const loading = ref(false)

async function fetchData() {
  loading.value = true
  try {
    await store.fetchMine()
  } finally {
    loading.value = false
  }
}

function viewRequest(id: number) {
  router.push(`/user/deposits/${id}`)
}

function createNew() {
  router.push('/user/deposits/create')
}

onMounted(fetchData)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1>Mes dépôts</h1>
        <p>Consultez et suivez l'état de vos demandes de dépôt</p>
      </div>
      <Button label="Nouveau dépôt" icon="pi pi-plus" @click="createNew" />
    </div>

    <DataTable
      :value="store.requests"
      :loading="loading"
      striped-rows
      paginator
      :rows="10"
      :rows-per-page-options="[5, 10, 20, 50]"
      sort-field="created_at"
      :sort-order="-1"
      class="p-datatable-sm"
    >
      <Column field="title" header="Titre" sortable style="min-width: 14rem" />
      <Column field="status" header="Statut" sortable style="min-width: 10rem">
        <template #body="{ data }">
          <StatusBadge :status="data.status" />
        </template>
      </Column>
      <Column field="created_at" header="Date" sortable style="min-width: 10rem">
        <template #body="{ data }">
          {{ formatDate(data.created_at) }}
        </template>
      </Column>
      <Column header="Actions" style="min-width: 8rem">
        <template #body="{ data }">
          <Button
            icon="pi pi-eye"
            class="p-button-rounded p-button-text p-button-sm"
            @click="viewRequest(data.id)"
            v-tooltip.left="'Voir le détail'"
          />
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<style scoped>
.page {
  max-width: 1100px;
}

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  gap: 1rem;
}

.page-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
}

.page-header p {
  color: var(--text-secondary);
  font-size: 0.9rem;
  margin-top: 0.25rem;
}
</style>
