<script setup lang="ts">
// Liste des références pour l'admin
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Chip from 'primevue/chip'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import { useConfirm } from 'primevue/useconfirm'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'

// Interface d'une référence
interface Reference {
  id: number
  title: string
  subtitle: string | null
  document_type: string
  status: string
  created_at: string
  keywords?: { id: number; name: string }[]
  allow_download: boolean
  file_path: string | null
}

const router = useRouter()
const toastStore = useToastStore()
const confirm = useConfirm()

// Références et filtres
const references = ref<Reference[]>([])
const loading = ref(false)
const globalFilter = ref('')
const statusFilter = ref('')

// Options de filtre par statut
const statuses = [
  { label: 'Tous', value: '' },
  { label: 'Publié', value: 'published' },
  { label: 'Brouillon', value: 'draft' },
  { label: 'Archivé', value: 'archived' },
]

// Filtre les références par statut et recherche
const filteredRefs = computed(() => {
  let items = references.value
  if (statusFilter.value) items = items.filter(r => r.status === statusFilter.value)
  if (globalFilter.value) {
    const q = globalFilter.value.toLowerCase()
    items = items.filter(r =>
      r.title.toLowerCase().includes(q) ||
      (r.subtitle || '').toLowerCase().includes(q) ||
      r.document_type?.name?.toLowerCase().includes(q) ||
      r.status.toLowerCase().includes(q)
    )
  }
  return items
})

// Récupère la liste des références
async function fetchReferences() {
  loading.value = true
  try {
    const res = await http.get('/references')
    references.value = res.data?.data ?? res.data ?? []
  } finally {
    loading.value = false
  }
}

// Demande confirmation avant suppression
function confirmDelete(id: number) {
  confirm.require({
    message: 'Supprimer cette référence ?',
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await http.delete(`/references/${id}`)
        toastStore.success('Référence supprimée.')
        await fetchReferences()
      } catch {
        toastStore.error('Erreur lors de la suppression.')
      }
    },
  })
}

// Redirige vers la page de publication
async function publish(id: number) {
  router.push(`/admin/references/${id}/publish`)
}

// Ouvre le document en lecture
function viewDocument(id: number) {
  window.open(`/user/references/${id}/read`, '_blank')
}

// Force le téléchargement sur une référence
async function forceDownload(id: number) {
  confirm.require({
    message: 'Autoriser le téléchargement de cette référence même si le propriétaire a bloqué ? Le propriétaire sera notifié.',
    header: 'Forcer le téléchargement',
    icon: 'pi pi-lock-open',
    acceptClass: 'p-button-warning',
    accept: async () => {
      try {
        await http.post(`/references/${id}/force-download`)
        toastStore.success('Téléchargement autorisé. Le propriétaire a été notifié.')
        await fetchReferences()
      } catch {
        toastStore.error('Erreur lors de l\'opération.')
      }
    },
  })
}

// Charge les références au montage
onMounted(fetchReferences)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Gestion des références</h1>
      <Button icon="pi pi-plus" label="Nouvelle référence" @click="router.push('/admin/references/create')" />
      <Button icon="pi pi-refresh" label="Actualiser" severity="secondary" @click="fetchReferences" />
    </div>

    <div class="toolbar">
      <IconField iconPosition="left">
        <InputIcon><i class="pi pi-search" /></InputIcon>
        <InputText v-model="globalFilter" placeholder="Rechercher..." class="search-input" />
      </IconField>
      <Select v-model="statusFilter" :options="statuses" optionLabel="label" optionValue="value" placeholder="Filtrer par statut" clearable class="filter-select" />
    </div>

    <DataTable
      :value="filteredRefs"
      :loading="loading"
      striped-rows
      paginator
      :rows="20"
      :rows-per-page-options="[10, 20, 50]"
      sort-field="created_at"
      :sort-order="-1"
    >
      <Column field="title" header="Titre" sortable />
      <Column field="document_type" header="Type" sortable>
        <template #body="{ data }">
          <Tag :value="data.document_type?.label ?? data.document_type?.name ?? '-'" />
        </template>
      </Column>
      <Column field="status" header="Statut" sortable>
        <template #body="{ data }">
          <StatusBadge :status="data.status" />
        </template>
      </Column>
      <Column header="Téléchargement">
        <template #body="{ data }">
          <Tag v-if="data.allow_download" value="Autorisé" severity="success" />
          <Tag v-else value="Bloqué" severity="danger" />
        </template>
      </Column>
      <Column field="created_at" header="Date" sortable>
        <template #body="{ data }">
          {{ new Date(data.created_at).toLocaleDateString() }}
        </template>
      </Column>
      <Column header="Mots-clés" class="keywords-col">
        <template #body="{ data }">
          <div class="keyword-list">
            <Chip v-for="kw in (data.keywords ?? []).slice(0, 3)" :key="kw.id" :label="kw.name" class="kw-chip" />
            <span v-if="(data.keywords ?? []).length > 3" class="kw-more">+{{ (data.keywords ?? []).length - 3 }}</span>
          </div>
        </template>
      </Column>
      <Column header="Actions" style="min-width: 10rem" class="actions-col">
        <template #body="{ data }">
          <div class="actions">
            <Button
              v-if="data.file_path"
              icon="pi pi-eye"
              severity="info"
              text
              v-tooltip.top="'Voir le document'"
              @click="viewDocument(data.id)"
            />
            <Button
              icon="pi pi-pencil"
              severity="info"
              text
              @click="router.push(`/admin/references/${data.id}/edit`)"
            />
            <Button
              v-if="data.status !== 'published'"
              icon="pi pi-send"
              severity="success"
              text
              @click="publish(data.id)"
            />
            <Button
              v-if="!data.allow_download"
              icon="pi pi-lock-open"
              severity="warning"
              text
              v-tooltip.top="'Forcer le téléchargement'"
              @click="forceDownload(data.id)"
            />
            <Button
              icon="pi pi-trash"
              severity="danger"
              text
              @click="confirmDelete(data.id)"
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
.actions { display: flex; gap: 0.25rem; }
.toolbar { display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: center; flex-wrap: wrap; }
.search-input { min-width: 260px; }
.filter-select { min-width: 170px; }
.keyword-list { display: flex; gap: 0.25rem; flex-wrap: wrap; align-items: center; }
.kw-chip { font-size: 0.75rem; }
.kw-more { font-size: 0.75rem; color: var(--text-secondary); font-weight: 600; }
@media (max-width: 768px) { .keywords-col { display: none; } }
@media (max-width: 640px) {
  .page { padding: 1rem; }
  .page-header { flex-wrap: wrap; gap: 0.5rem; }
  .page-header h1 { font-size: 1.1rem; }
  .toolbar { flex-direction: column; align-items: stretch; }
  .search-input { min-width: 0; width: 100%; }
  .filter-select { min-width: 0; width: 100%; }
  .actions-col { min-width: 8rem; }
}
</style>
