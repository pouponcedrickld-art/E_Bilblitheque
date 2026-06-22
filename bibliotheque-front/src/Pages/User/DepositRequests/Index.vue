<script setup lang="ts">
// Importations Vue, stores et composants PrimeVue
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDepositRequestsStore } from '@/stores/depositRequests'
import { useToastStore } from '@/stores/toast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'
import { formatDate } from '@/Utils/formatters'

// Routeur et stores
const router = useRouter()
const authStore = useAuthStore()
const store = useDepositRequestsStore()
const toastStore = useToastStore()

// L'utilisateur peut créér une demande (compte actif requis)
const canCreate = computed(() => authStore.user?.status === 'active')

// État de chargement et filtre de recherche
const loading = ref(false)
const globalFilter = ref('')

// Liste filtrée par recherche textuelle
const filteredRequests = computed(() => {
  if (!globalFilter.value) return store.requests
  const q = globalFilter.value.toLowerCase()
  return store.requests.filter(r =>
    r.title.toLowerCase().includes(q) ||
    r.status.toLowerCase().includes(q)
  )
})

// Charge les demandes de l'utilisateur connecté
async function fetchData() {
  loading.value = true
  try {
    await store.fetchMine()
    if (store.error) toastStore.error(store.error)
  } catch {
    toastStore.error('Erreur lors du chargement des dépôts.')
  } finally {
    loading.value = false
  }
}

// Navigue vers le détail d'une demande
function viewRequest(id: number) {
  router.push(`/user/deposits/${id}`)
}

// Navigue vers le formulaire de création
function createNew() {
  router.push('/user/deposits/create')
}

// Charge les données au montage
onMounted(fetchData)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1>Mes dépôts</h1>
        <p>Consultez et suivez l'état de vos demandes de dépôt</p>
      </div>
      <Button v-if="canCreate" label="Nouveau dépôt" icon="pi pi-plus" @click="createNew" />
    </div>

    <div v-if="!canCreate" class="alert alert-info">
      Votre compte est en attente de validation par un administrateur. Vous ne pouvez pas faire de demande de dépôt pour le moment.
    </div>

    <div class="toolbar">
      <IconField iconPosition="left">
        <InputIcon><i class="pi pi-search" /></InputIcon>
        <InputText v-model="globalFilter" placeholder="Rechercher..." class="search-input" />
      </IconField>
    </div>

    <DataTable
      :value="filteredRequests"
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
.toolbar { display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: center; }
.search-input { min-width: 260px; }

.alert {
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  margin-bottom: 1rem;
}

.alert-info {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: #1e40af;
}
</style>
