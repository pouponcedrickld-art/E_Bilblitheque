<script setup lang="ts">
import { ref, onMounted } from 'vue'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'

interface Category {
  id: number
  name: string
  slug: string
  description: string | null
  references_count?: number
}

const toastStore = useToastStore()
const confirm = useConfirm()

const categories = ref<Category[]>([])
const loading = ref(false)

const dialogVisible = ref(false)
const editing = ref(false)
const selectedCategory = ref<Category | null>(null)
const form = ref({ name: '', description: '' })
const submitting = ref(false)

async function fetch() {
  loading.value = true
  try {
    const res = await http.get('/categories')
    categories.value = res.data?.data ?? res.data ?? []
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = false
  selectedCategory.value = null
  form.value = { name: '', description: '' }
  dialogVisible.value = true
}

function openEdit(cat: Category) {
  editing.value = true
  selectedCategory.value = cat
  form.value = { name: cat.name, description: cat.description ?? '' }
  dialogVisible.value = true
}

async function submit() {
  submitting.value = true
  try {
    if (editing.value && selectedCategory.value) {
      await http.put(`/categories/${selectedCategory.value.id}`, form.value)
      toastStore.success('Catégorie mise à jour.')
    } else {
      await http.post('/categories', form.value)
      toastStore.success('Catégorie créée.')
    }
    dialogVisible.value = false
    await fetch()
  } catch {
    toastStore.error("Erreur lors de l'enregistrement.")
  } finally {
    submitting.value = false
  }
}

async function deleteCategory(id: number) {
  confirm.require({
    message: 'Supprimer cette catégorie ?',
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await http.delete(`/categories/${id}`)
        toastStore.success('Catégorie supprimée.')
        await fetch()
      } catch {
        toastStore.error('Erreur lors de la suppression.')
      }
    },
  })
}

onMounted(fetch)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Catégories</h1>
      <Button icon="pi pi-plus" label="Nouvelle catégorie" @click="openCreate" />
    </div>

    <DataTable :value="categories" :loading="loading" striped-rows paginator :rows="20" :rows-per-page-options="[10, 20, 50]">
      <Column field="name" header="Nom" sortable />
      <Column field="slug" header="Slug" />
      <Column field="description" header="Description">
        <template #body="{ data }">
          {{ data.description || '-' }}
        </template>
      </Column>
      <Column field="references_count" header="Références" sortable />
      <Column header="Actions" style="min-width: 8rem">
        <template #body="{ data }">
          <div class="actions">
            <Button icon="pi pi-pencil" severity="info" text @click="openEdit(data)" />
            <Button icon="pi pi-trash" severity="danger" text @click="deleteCategory(data.id)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog
      v-model:visible="dialogVisible"
      :header="editing ? 'Modifier la catégorie' : 'Nouvelle catégorie'"
      :modal="true"
      :style="{ width: '450px' }"
    >
      <form @submit.prevent="submit" class="dialog-form">
        <div class="field">
          <label for="cat-name">Nom</label>
          <InputText id="cat-name" v-model="form.name" required />
        </div>
        <div class="field">
          <label for="cat-desc">Description</label>
          <Textarea id="cat-desc" v-model="form.description" rows="3" :auto-resize="true" />
        </div>
      </form>
      <template #footer>
        <Button label="Annuler" severity="secondary" text @click="dialogVisible = false" />
        <Button :label="editing ? 'Enregistrer' : 'Créer'" icon="pi pi-check" :loading="submitting" @click="submit" />
      </template>
    </Dialog>

    <ConfirmDialog />
  </div>
</template>

<style scoped>
.page { max-width: 1000px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.actions { display: flex; gap: 0.25rem; }
.dialog-form { display: flex; flex-direction: column; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field label { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
</style>
