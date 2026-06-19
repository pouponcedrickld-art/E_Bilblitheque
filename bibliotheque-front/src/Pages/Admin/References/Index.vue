<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'

interface Reference {
  id: number
  title: string
  subtitle: string | null
  document_type: string
  status: string
  created_at: string
}

const router = useRouter()
const toastStore = useToastStore()
const confirm = useConfirm()

const references = ref<Reference[]>([])
const loading = ref(false)

async function fetchReferences() {
  loading.value = true
  try {
    const res = await http.get('/references')
    references.value = res.data?.data ?? res.data ?? []
  } finally {
    loading.value = false
  }
}

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

async function publish(id: number) {
  router.push(`/admin/references/${id}/publish`)
}

onMounted(fetchReferences)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Gestion des références</h1>
      <Button icon="pi pi-refresh" label="Actualiser" severity="secondary" @click="fetchReferences" />
    </div>

    <DataTable
      :value="references"
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
          <Tag :value="data.document_type" />
        </template>
      </Column>
      <Column field="status" header="Statut" sortable>
        <template #body="{ data }">
          <StatusBadge :status="data.status" />
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
              icon="pi pi-trash"
              severity="danger"
              text
              @click="confirmDelete(data.id)"
            />
          </div>
        </template>
      </Column>
    </DataTable>

    <ConfirmDialog />
  </div>
</template>

<style scoped>
.page { max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.actions { display: flex; gap: 0.25rem; }
</style>
