<script setup lang="ts">
// Gestion des auteurs du catalogue
import { ref, onMounted } from 'vue'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'
import { useConfirm } from 'primevue/useconfirm'

// Interface représentant un auteur
interface Author {
  id: number
  first_name: string
  last_name: string
  full_name: string
  biography: string | null
  references_count?: number
}

const toastStore = useToastStore()
const confirm = useConfirm()

// Liste des auteurs et état de chargement
const authors = ref<Author[]>([])
const loading = ref(false)

// État du dialogue et du formulaire
const dialogVisible = ref(false)
const editing = ref(false)
const selectedAuthor = ref<Author | null>(null)
const form = ref({ first_name: '', last_name: '', biography: '' })
const submitting = ref(false)

// Récupère la liste des auteurs
async function fetch() {
  loading.value = true
  try {
    const res = await http.get('/authors')
    authors.value = res.data?.data ?? res.data ?? []
  } finally {
    loading.value = false
  }
}

// Ouvre le dialogue de création
function openCreate() {
  editing.value = false
  selectedAuthor.value = null
  form.value = { first_name: '', last_name: '', biography: '' }
  dialogVisible.value = true
}

// Ouvre le dialogue de modification
function openEdit(author: Author) {
  editing.value = true
  selectedAuthor.value = author
  form.value = {
    first_name: author.first_name,
    last_name: author.last_name,
    biography: author.biography ?? '',
  }
  dialogVisible.value = true
}

// Crée ou met à jour un auteur
async function submit() {
  submitting.value = true
  try {
    if (editing.value && selectedAuthor.value) {
      await http.put(`/authors/${selectedAuthor.value.id}`, form.value)
      toastStore.success('Auteur mis à jour.')
    } else {
      await http.post('/authors', form.value)
      toastStore.success('Auteur créé.')
    }
    dialogVisible.value = false
    await fetch()
  } catch {
    toastStore.error("Erreur lors de l'enregistrement.")
  } finally {
    submitting.value = false
  }
}

// Supprime un auteur après confirmation
async function deleteAuthor(id: number) {
  confirm.require({
    message: 'Supprimer cet auteur ?',
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await http.delete(`/authors/${id}`)
        toastStore.success('Auteur supprimé.')
        await fetch()
      } catch {
        toastStore.error('Erreur lors de la suppression.')
      }
    },
  })
}

// Charge les auteurs au montage
onMounted(fetch)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Auteurs</h1>
      <Button icon="pi pi-plus" label="Nouvel auteur" @click="openCreate" />
    </div>

    <DataTable :value="authors" :loading="loading" striped-rows paginator :rows="20" :rows-per-page-options="[10, 20, 50]">
      <Column field="full_name" header="Nom complet" sortable />
      <Column field="biography" header="Biographie">
        <template #body="{ data }">
          {{ data.biography ? data.biography.substring(0, 80) + (data.biography.length > 80 ? '...' : '') : '-' }}
        </template>
      </Column>
      <Column field="references_count" header="Références" sortable />
      <Column header="Actions" style="min-width: 8rem">
        <template #body="{ data }">
          <div class="actions">
            <Button icon="pi pi-pencil" severity="info" text @click="openEdit(data)" />
            <Button icon="pi pi-trash" severity="danger" text @click="deleteAuthor(data.id)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog
      v-model:visible="dialogVisible"
      :header="editing ? 'Modifier l\'auteur' : 'Nouvel auteur'"
      :modal="true"
      :style="{ width: '500px' }" class="responsive-dialog"
    >
      <form @submit.prevent="submit" class="dialog-form">
        <div class="form-row">
          <div class="field">
            <label for="author-first-name">Prénom</label>
            <InputText id="author-first-name" v-model="form.first_name" required />
          </div>
          <div class="field">
            <label for="author-last-name">Nom</label>
            <InputText id="author-last-name" v-model="form.last_name" required />
          </div>
        </div>
        <div class="field">
          <label for="author-bio">Biographie</label>
          <Textarea id="author-bio" v-model="form.biography" rows="4" :auto-resize="true" />
        </div>
      </form>
      <template #footer>
        <Button label="Annuler" severity="secondary" text @click="dialogVisible = false" />
        <Button :label="editing ? 'Enregistrer' : 'Créer'" icon="pi pi-check" :loading="submitting" @click="submit" />
      </template>
    </Dialog>

  </div>
</template>

<style scoped>
.page { max-width: 1000px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.actions { display: flex; gap: 0.25rem; }
.dialog-form { display: flex; flex-direction: column; gap: 1rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field label { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
@media (max-width: 640px) {
  .form-row { grid-template-columns: 1fr; }
  :deep(.responsive-dialog) { width: 95vw !important; }
}
</style>


