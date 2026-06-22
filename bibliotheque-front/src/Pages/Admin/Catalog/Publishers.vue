<script setup lang="ts">
// Gestion des éditeurs du catalogue
import { ref, onMounted } from 'vue'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputMask from 'primevue/inputmask'
import Dialog from 'primevue/dialog'
import ConfirmDialog from 'primevue/confirmdialog'
import { useConfirm } from 'primevue/useconfirm'

// Interface représentant un éditeur
interface Publisher {
  id: number
  name: string
  address: string | null
  email: string | null
  website: string | null
  phone: string | null
  references_count?: number
}

const toastStore = useToastStore()
const confirm = useConfirm()

// Liste des éditeurs et état de chargement
const publishers = ref<Publisher[]>([])
const loading = ref(false)

// État du dialogue et du formulaire
const dialogVisible = ref(false)
const editing = ref(false)
const selectedPublisher = ref<Publisher | null>(null)
const form = ref({ name: '', address: '', email: '', website: '', phone: '' })
const submitting = ref(false)

// Récupère la liste des éditeurs
async function fetch() {
  loading.value = true
  try {
    const res = await http.get('/publishers')
    publishers.value = res.data?.data ?? res.data ?? []
  } finally {
    loading.value = false
  }
}

// Ouvre le dialogue de création
function openCreate() {
  editing.value = false
  selectedPublisher.value = null
  form.value = { name: '', address: '', email: '', website: '', phone: '' }
  dialogVisible.value = true
}

// Ouvre le dialogue de modification
function openEdit(pub: Publisher) {
  editing.value = true
  selectedPublisher.value = pub
  form.value = {
    name: pub.name,
    address: pub.address ?? '',
    email: pub.email ?? '',
    website: pub.website ?? '',
    phone: pub.phone ?? '',
  }
  dialogVisible.value = true
}

// Crée ou met à jour un éditeur
async function submit() {
  submitting.value = true
  try {
    if (editing.value && selectedPublisher.value) {
      await http.put(`/publishers/${selectedPublisher.value.id}`, form.value)
      toastStore.success('Éditeur mis à jour.')
    } else {
      await http.post('/publishers', form.value)
      toastStore.success('Éditeur créé.')
    }
    dialogVisible.value = false
    await fetch()
  } catch {
    toastStore.error("Erreur lors de l'enregistrement.")
  } finally {
    submitting.value = false
  }
}

// Supprime un éditeur après confirmation
async function deletePublisher(id: number) {
  confirm.require({
    message: "Supprimer cet éditeur ?",
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await http.delete(`/publishers/${id}`)
        toastStore.success('Éditeur supprimé.')
        await fetch()
      } catch {
        toastStore.error('Erreur lors de la suppression.')
      }
    },
  })
}

// Charge les éditeurs au montage
onMounted(fetch)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Éditeurs</h1>
      <Button icon="pi pi-plus" label="Nouvel éditeur" @click="openCreate" />
    </div>

    <DataTable :value="publishers" :loading="loading" striped-rows paginator :rows="20" :rows-per-page-options="[10, 20, 50]">
      <Column field="name" header="Nom" sortable />
      <Column field="address" header="Adresse">
        <template #body="{ data }">
          {{ data.address || '-' }}
        </template>
      </Column>
      <Column field="email" header="Email">
        <template #body="{ data }">
          {{ data.email || '-' }}
        </template>
      </Column>
      <Column field="website" header="Site web">
        <template #body="{ data }">
          <a v-if="data.website" :href="data.website" target="_blank" rel="noopener">{{ data.website }}</a>
          <span v-else>-</span>
        </template>
      </Column>
      <Column field="phone" header="Téléphone">
        <template #body="{ data }">
          {{ data.phone || '-' }}
        </template>
      </Column>
      <Column field="references_count" header="Références" sortable />
      <Column header="Actions" style="min-width: 8rem">
        <template #body="{ data }">
          <div class="actions">
            <Button icon="pi pi-pencil" severity="info" text @click="openEdit(data)" />
            <Button icon="pi pi-trash" severity="danger" text @click="deletePublisher(data.id)" />
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog
      v-model:visible="dialogVisible"
      :header="editing ? 'Modifier l\'éditeur' : 'Nouvel éditeur'"
      :modal="true"
      :style="{ width: '550px' }" class="responsive-dialog"
    >
      <form @submit.prevent="submit" class="dialog-form">
        <div class="field">
          <label for="pub-name">Nom</label>
          <InputText id="pub-name" v-model="form.name" required />
        </div>
        <div class="field">
          <label for="pub-address">Adresse</label>
          <InputText id="pub-address" v-model="form.address" />
        </div>
        <div class="form-row">
          <div class="field">
            <label for="pub-email">Email</label>
            <InputText id="pub-email" v-model="form.email" type="email" />
          </div>
          <div class="field">
            <label for="pub-phone">Téléphone</label>
            <InputText id="pub-phone" v-model="form.phone" />
          </div>
        </div>
        <div class="field">
          <label for="pub-website">Site web</label>
          <InputText id="pub-website" v-model="form.website" placeholder="https://" />
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
.page { max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
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
