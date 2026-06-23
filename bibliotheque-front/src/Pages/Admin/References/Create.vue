<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import MultiSelect from 'primevue/multiselect'
import InputNumber from 'primevue/inputnumber'
import FileUpload from 'primevue/fileupload'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Message from 'primevue/message'

const router = useRouter()
const toastStore = useToastStore()

const form = ref({
  title: '',
  subtitle: '',
  abstract: '',
  isbn: '',
  publication_year: null as number | null,
  language: 'fr',
  document_type_id: null as number | null,
  category_id: null as number | null,
  publisher_id: null as number | null,
  pages: null as number | null,
  status: 'draft',
  keyword_ids: [] as number[],
})

const categories = ref<{ id: number; name: string }[]>([])
const publishers = ref<{ id: number; name: string }[]>([])
const documentTypes = ref<{ id: number; name: string; label: string }[]>([])
const keywords = ref<{ id: number; name: string }[]>([])
const coverFile = ref<File | null>(null)
const coverPreview = ref<string | null>(null)
const submitting = ref(false)
const error = ref('')

const showCategoryDialog = ref(false)
const newCategoryName = ref('')
const newCategoryDescription = ref('')
const creatingCategory = ref(false)

const showPublisherDialog = ref(false)
const newPublisherName = ref('')
const newPublisherCountry = ref('')
const creatingPublisher = ref(false)

const showDocTypeDialog = ref(false)
const newDocTypeName = ref('')
const newDocTypeLabel = ref('')
const creatingDocType = ref(false)

const showKeywordDialog = ref(false)
const newKeywordName = ref('')
const creatingKeyword = ref(false)

const languages = [
  { label: 'Français', value: 'fr' },
  { label: 'Anglais', value: 'en' },
  { label: 'Autre', value: 'autre' },
]

async function loadFormData() {
  try {
    const [catRes, pubRes, dtRes, kwRes] = await Promise.all([
      http.get('/categories'),
      http.get('/publishers'),
      http.get('/document-types'),
      http.get('/keywords'),
    ])
    categories.value = catRes.data?.data ?? catRes.data ?? []
    publishers.value = pubRes.data?.data ?? pubRes.data ?? []
    documentTypes.value = dtRes.data?.data ?? dtRes.data ?? []
    keywords.value = kwRes.data?.data ?? kwRes.data ?? []
  } catch {
    error.value = 'Impossible de charger les données du formulaire.'
  }
}

async function createCategory() {
  if (!newCategoryName.value.trim()) return
  creatingCategory.value = true
  try {
    const res = await http.post('/categories', {
      name: newCategoryName.value.trim(),
      description: newCategoryDescription.value.trim() || null,
    })
    const created = res.data?.data ?? res.data
    categories.value.push(created)
    form.value.category_id = created.id
    showCategoryDialog.value = false
    newCategoryName.value = ''
    newCategoryDescription.value = ''
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erreur lors de la création.'
  } finally {
    creatingCategory.value = false
  }
}

async function createPublisher() {
  if (!newPublisherName.value.trim()) return
  creatingPublisher.value = true
  try {
    const res = await http.post('/publishers', {
      name: newPublisherName.value.trim(),
      country: newPublisherCountry.value.trim() || null,
    })
    const created = res.data?.data ?? res.data
    publishers.value.push(created)
    form.value.publisher_id = created.id
    showPublisherDialog.value = false
    newPublisherName.value = ''
    newPublisherCountry.value = ''
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erreur lors de la création.'
  } finally {
    creatingPublisher.value = false
  }
}

async function createDocType() {
  if (!newDocTypeName.value.trim()) return
  creatingDocType.value = true
  try {
    const res = await http.post('/document-types', {
      name: newDocTypeName.value.trim(),
      label: newDocTypeLabel.value.trim() || newDocTypeName.value.trim(),
    })
    const created = res.data?.data ?? res.data
    documentTypes.value.push(created)
    form.value.document_type_id = created.id
    showDocTypeDialog.value = false
    newDocTypeName.value = ''
    newDocTypeLabel.value = ''
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erreur lors de la création.'
  } finally {
    creatingDocType.value = false
  }
}

async function createKeyword() {
  if (!newKeywordName.value.trim()) return
  creatingKeyword.value = true
  try {
    const res = await http.post('/keywords', {
      name: newKeywordName.value.trim(),
    })
    const created = res.data?.data ?? res.data
    keywords.value.push(created)
    form.value.keyword_ids.push(created.id)
    showKeywordDialog.value = false
    newKeywordName.value = ''
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erreur lors de la création.'
  } finally {
    creatingKeyword.value = false
  }
}

function onCoverChange(event: Event) {
  const input = event.target as HTMLInputElement
  if (input.files && input.files[0]) {
    coverFile.value = input.files[0]
    coverPreview.value = URL.createObjectURL(input.files[0])
  }
}

async function submit() {
  submitting.value = true
  error.value = ''
  try {
    const fd = new FormData()
    fd.append('title', form.value.title)
    fd.append('subtitle', form.value.subtitle)
    fd.append('abstract', form.value.abstract)
    fd.append('isbn', form.value.isbn)
    if (form.value.publication_year) fd.append('publication_year', String(form.value.publication_year))
    fd.append('language', form.value.language)
    if (form.value.document_type_id) fd.append('document_type_id', String(form.value.document_type_id))
    if (form.value.category_id) fd.append('category_id', String(form.value.category_id))
    if (form.value.publisher_id) fd.append('publisher_id', String(form.value.publisher_id))
    if (form.value.pages) fd.append('pages', String(form.value.pages))
    fd.append('status', form.value.status)
    form.value.keyword_ids.forEach(id => fd.append('keyword_ids[]', String(id)))
    if (coverFile.value) fd.append('cover_image', coverFile.value)

    await http.post('/references', fd)
    toastStore.success('Référence créée avec succès.')
    router.push('/admin/references')
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erreur lors de la création.'
  } finally {
    submitting.value = false
  }
}

onMounted(loadFormData)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Nouvelle référence</h1>
      <Button icon="pi pi-arrow-left" label="Retour" severity="secondary" text @click="router.push('/admin/references')" />
    </div>

    <Message v-if="error" severity="error" :closable="false" class="mb-3">{{ error }}</Message>

    <form @submit.prevent="submit" class="form">
      <div class="form-grid">
        <div class="field">
          <label for="title">Titre</label>
          <InputText id="title" v-model="form.title" required />
        </div>
        <div class="field">
          <label for="subtitle">Sous-titre</label>
          <InputText id="subtitle" v-model="form.subtitle" />
        </div>
        <div class="field full">
          <label for="abstract">Résumé</label>
          <Textarea id="abstract" v-model="form.abstract" rows="4" :auto-resize="true" />
        </div>
        <div class="field">
          <label for="isbn">ISBN</label>
          <InputText id="isbn" v-model="form.isbn" />
        </div>
        <div class="field">
          <label for="publication_year">Année de publication</label>
          <InputNumber id="publication_year" v-model="form.publication_year" :min="1000" :max="2100" />
        </div>
        <div class="field">
          <label for="language">Langue</label>
          <Select id="language" v-model="form.language" :options="languages" option-label="label" option-value="value" />
        </div>
        <div class="field">
          <label for="document_type_id">Type de document</label>
          <div class="select-with-create">
            <Select id="document_type_id" v-model="form.document_type_id" :options="documentTypes" option-label="label" option-value="id" show-clear placeholder="Sélectionner un type" class="flex-1" />
            <Button icon="pi pi-plus" class="p-button-sm p-button-outlined add-btn" @click="showDocTypeDialog = true" v-tooltip.top="'Ajouter un type'" />
          </div>
        </div>
        <div class="field">
          <label for="category_id">Catégorie</label>
          <div class="select-with-create">
            <Select id="category_id" v-model="form.category_id" :options="categories" option-label="name" option-value="id" show-clear placeholder="Sélectionner une catégorie" class="flex-1" />
            <Button icon="pi pi-plus" class="p-button-sm p-button-outlined add-btn" @click="showCategoryDialog = true" v-tooltip.top="'Ajouter une catégorie'" />
          </div>
        </div>
        <div class="field">
          <label for="publisher_id">Éditeur</label>
          <div class="select-with-create">
            <Select id="publisher_id" v-model="form.publisher_id" :options="publishers" option-label="name" option-value="id" show-clear placeholder="Sélectionner un éditeur" class="flex-1" />
            <Button icon="pi pi-plus" class="p-button-sm p-button-outlined add-btn" @click="showPublisherDialog = true" v-tooltip.top="'Ajouter un éditeur'" />
          </div>
        </div>
        <div class="field">
          <label for="pages">Nombre de pages</label>
          <InputNumber id="pages" v-model="form.pages" :min="1" />
        </div>
        <div class="field">
          <label for="status">Statut</label>
          <Select id="status" v-model="form.status" :options="[{ label: 'Brouillon', value: 'draft' }, { label: 'Publié', value: 'published' }, { label: 'Archivé', value: 'archived' }]" option-label="label" option-value="value" />
        </div>
        <div class="field full">
          <label for="keywords">Mots-clés</label>
          <div class="select-with-create">
            <MultiSelect id="keywords" v-model="form.keyword_ids" :options="keywords" option-label="name" option-value="id" placeholder="Sélectionner des mots-clés" :max-selected-labels="5" class="flex-1" />
            <Button icon="pi pi-plus" class="p-button-sm p-button-outlined add-btn" @click="showKeywordDialog = true" v-tooltip.top="'Ajouter un mot-clé'" />
          </div>
        </div>
        <div class="field full">
          <label for="cover_image">Image de couverture</label>
          <input type="file" id="cover_image" accept="image/*" @change="onCoverChange" class="file-input" />
          <div v-if="coverPreview" class="cover-preview">
            <img :src="coverPreview" alt="Aperçu" />
          </div>
        </div>
      </div>

      <div class="form-actions">
        <Button icon="pi pi-check" label="Créer la référence" type="submit" :loading="submitting" />
        <Button icon="pi pi-times" label="Annuler" severity="secondary" text @click="router.push('/admin/references')" />
      </div>
    </form>

    <Dialog v-model:visible="showCategoryDialog" header="Nouvelle catégorie" :modal="true" class="create-dialog">
      <div class="dialog-form">
        <div class="field">
          <label for="new-category-name">Nom <span class="required">*</span></label>
          <InputText id="new-category-name" v-model="newCategoryName" class="input" placeholder="Ex: Mathématiques, Histoire..." />
        </div>
        <div class="field">
          <label for="new-category-desc">Description</label>
          <Textarea id="new-category-desc" v-model="newCategoryDescription" rows="3" class="input" placeholder="Optionnelle" />
        </div>
      </div>
      <template #footer>
        <Button label="Annuler" severity="secondary" text @click="showCategoryDialog = false" />
        <Button label="Créer" icon="pi pi-check" :loading="creatingCategory" :disabled="!newCategoryName.trim()" @click="createCategory" />
      </template>
    </Dialog>

    <Dialog v-model:visible="showPublisherDialog" header="Nouvel éditeur" :modal="true" class="create-dialog">
      <div class="dialog-form">
        <div class="field">
          <label for="new-publisher-name">Nom <span class="required">*</span></label>
          <InputText id="new-publisher-name" v-model="newPublisherName" class="input" placeholder="Ex: Dunod, Hachette..." />
        </div>
        <div class="field">
          <label for="new-publisher-country">Pays</label>
          <InputText id="new-publisher-country" v-model="newPublisherCountry" class="input" placeholder="Optionnel" />
        </div>
      </div>
      <template #footer>
        <Button label="Annuler" severity="secondary" text @click="showPublisherDialog = false" />
        <Button label="Créer" icon="pi pi-check" :loading="creatingPublisher" :disabled="!newPublisherName.trim()" @click="createPublisher" />
      </template>
    </Dialog>

    <Dialog v-model:visible="showDocTypeDialog" header="Nouveau type de document" :modal="true" class="create-dialog">
      <div class="dialog-form">
        <div class="field">
          <label for="new-doctype-name">Nom technique <span class="required">*</span></label>
          <InputText id="new-doctype-name" v-model="newDocTypeName" class="input" placeholder="Ex: article, livre..." />
        </div>
        <div class="field">
          <label for="new-doctype-label">Libellé</label>
          <InputText id="new-doctype-label" v-model="newDocTypeLabel" class="input" placeholder="Optionnel (sinon reprend le nom)" />
        </div>
      </div>
      <template #footer>
        <Button label="Annuler" severity="secondary" text @click="showDocTypeDialog = false" />
        <Button label="Créer" icon="pi pi-check" :loading="creatingDocType" :disabled="!newDocTypeName.trim()" @click="createDocType" />
      </template>
    </Dialog>

    <Dialog v-model:visible="showKeywordDialog" header="Nouveau mot-clé" :modal="true" class="create-dialog">
      <div class="dialog-form">
        <div class="field">
          <label for="new-keyword-name">Nom <span class="required">*</span></label>
          <InputText id="new-keyword-name" v-model="newKeywordName" class="input" placeholder="Ex: machine learning, histoire..." />
        </div>
      </div>
      <template #footer>
        <Button label="Annuler" severity="secondary" text @click="showKeywordDialog = false" />
        <Button label="Créer" icon="pi pi-check" :loading="creatingKeyword" :disabled="!newKeywordName.trim()" @click="createKeyword" />
      </template>
    </Dialog>
  </div>
</template>

<style scoped>
.page { max-width: 800px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.mb-3 { margin-bottom: 1rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field.full { grid-column: 1 / -1; }
.field label { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
.required { color: var(--destructive); }
.form-actions { margin-top: 1.5rem; display: flex; gap: 0.75rem; }
.file-input { padding: 0.5rem; border: 1px solid var(--border); border-radius: 0.5rem; background: #fff; font-size: 0.85rem; }
.cover-preview { margin-top: 0.5rem; width: 120px; height: 160px; border-radius: 0.5rem; overflow: hidden; border: 1px solid var(--border); }
.cover-preview img { width: 100%; height: 100%; object-fit: cover; }
.flex-1 { flex: 1; }
.select-with-create { display: flex; gap: 0.35rem; align-items: center; }
.add-btn { flex-shrink: 0; }
.dialog-form { display: flex; flex-direction: column; gap: 1rem; }
@media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
</style>
