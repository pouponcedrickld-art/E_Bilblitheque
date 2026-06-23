<script setup lang="ts">
import { ref, onMounted } from 'vue'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import InputNumber from 'primevue/inputnumber'
import FileUpload from 'primevue/fileupload'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'

const emit = defineEmits<{
  (e: 'submit', data: FormData): void
}>()
const toastStore = useToastStore()

const form = ref({
  title: '',
  subtitle: '',
  abstract: '',
  description: '',
  isbn: '',
  publication_year: null as number | null,
  language: 'fr',
  document_type_id: null as number | null,
  category_id: null as number | null,
  publisher_id: null as number | null,
  pages: null as number | null,
})

const categories = ref<{ id: number; name: string }[]>([])
const publishers = ref<{ id: number; name: string }[]>([])
const documentTypes = ref<{ id: number; name: string; label: string }[]>([])
const coverFile = ref<File | null>(null)
const coverPreview = ref<string | null>(null)
const documentFile = ref<File | null>(null)
const submitted = ref(false)

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

const languages = [
  { label: 'Français', value: 'fr' },
  { label: 'Anglais', value: 'en' },
  { label: 'Autre', value: 'autre' },
]

async function loadFormData() {
  try {
    const [catRes, pubRes, dtRes] = await Promise.all([
      http.get('/categories'),
      http.get('/publishers'),
      http.get('/document-types'),
    ])
    categories.value = catRes.data?.data ?? catRes.data ?? []
    publishers.value = pubRes.data?.data ?? pubRes.data ?? []
    documentTypes.value = dtRes.data?.data ?? dtRes.data ?? []
  } catch {}
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
    toastStore.success('Catégorie créée avec succès.')
  } catch (err: any) {
    const msg = err.response?.data?.message || err.response?.data?.errors?.name?.[0] || 'Erreur lors de la création.'
    toastStore.error(msg)
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
    toastStore.success('Éditeur créé avec succès.')
  } catch (err: any) {
    const msg = err.response?.data?.message || err.response?.data?.errors?.name?.[0] || 'Erreur lors de la création.'
    toastStore.error(msg)
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
    toastStore.success('Type de document créé avec succès.')
  } catch (err: any) {
    const msg = err.response?.data?.message || err.response?.data?.errors?.name?.[0] || 'Erreur lors de la création.'
    toastStore.error(msg)
  } finally {
    creatingDocType.value = false
  }
}

function onCoverChange(event: { files: File[] }) {
  const file = event.files[0]
  if (file) {
    coverFile.value = file
    coverPreview.value = URL.createObjectURL(file)
  }
}

function onDocumentChange(event: { files: File[] }) {
  documentFile.value = event.files[0] || null
}

function handleSubmit() {
  submitted.value = true
  if (!form.value.title.trim()) return

  const fd = new FormData()
  fd.append('title', form.value.title)
  if (form.value.subtitle) fd.append('subtitle', form.value.subtitle)
  if (form.value.abstract) fd.append('abstract', form.value.abstract)
  if (form.value.description) fd.append('description', form.value.description)
  if (form.value.isbn) fd.append('isbn', form.value.isbn)
  if (form.value.publication_year) fd.append('publication_year', String(form.value.publication_year))
  fd.append('language', form.value.language)
  if (form.value.document_type_id) fd.append('document_type_id', String(form.value.document_type_id))
  if (form.value.category_id) fd.append('category_id', String(form.value.category_id))
  if (form.value.publisher_id) fd.append('publisher_id', String(form.value.publisher_id))
  if (form.value.pages) fd.append('pages', String(form.value.pages))
  if (coverFile.value) fd.append('cover_image', coverFile.value)
  if (documentFile.value) fd.append('proposed_file', documentFile.value)

  emit('submit', fd)
}

onMounted(loadFormData)
</script>

<template>
  <form @submit.prevent="handleSubmit" class="form">
    <div class="form-grid">
      <div class="field">
        <label for="title">Titre <span class="required">*</span></label>
        <InputText id="title" v-model="form.title" :class="['input', { 'input-invalid': submitted && !form.title.trim() }]" />
        <small v-if="submitted && !form.title.trim()" class="error">Le titre est requis.</small>
      </div>
      <div class="field">
        <label for="subtitle">Sous-titre</label>
        <InputText id="subtitle" v-model="form.subtitle" class="input" />
      </div>
      <div class="field full">
        <label for="abstract">Résumé</label>
        <Textarea id="abstract" v-model="form.abstract" rows="4" :auto-resize="true" class="input" />
      </div>
      <div class="field full">
        <label for="description">Description</label>
        <Textarea id="description" v-model="form.description" rows="3" :auto-resize="true" class="input" />
      </div>
      <div class="field">
        <label for="isbn">ISBN</label>
        <InputText id="isbn" v-model="form.isbn" class="input" />
      </div>
      <div class="field">
        <label for="publication_year">Année de publication</label>
        <InputNumber id="publication_year" v-model="form.publication_year" :min="1000" :max="2100" class="input" />
      </div>
      <div class="field">
        <label for="language">Langue</label>
        <Select id="language" v-model="form.language" :options="languages" option-label="label" option-value="value" class="input" />
      </div>
      <div class="field">
        <label for="document_type_id">Type de document</label>
        <div class="select-with-create">
          <Select id="document_type_id" v-model="form.document_type_id" :options="documentTypes" option-label="label" option-value="id" show-clear placeholder="Sélectionner un type" class="input" />
          <Button icon="pi pi-plus" class="p-button-sm p-button-outlined add-btn" @click="showDocTypeDialog = true" v-tooltip.top="'Ajouter un type de document'" />
        </div>
      </div>
      <div class="field">
        <label for="category_id">Catégorie</label>
        <div class="select-with-create">
          <Select id="category_id" v-model="form.category_id" :options="categories" option-label="name" option-value="id" show-clear placeholder="Sélectionner une catégorie" class="input" />
          <Button icon="pi pi-plus" class="p-button-sm p-button-outlined add-btn" @click="showCategoryDialog = true" v-tooltip.top="'Ajouter une catégorie'" />
        </div>
      </div>
      <div class="field">
        <label for="publisher_id">Éditeur</label>
        <div class="select-with-create">
          <Select id="publisher_id" v-model="form.publisher_id" :options="publishers" option-label="name" option-value="id" show-clear placeholder="Sélectionner un éditeur" class="input" />
          <Button icon="pi pi-plus" class="p-button-sm p-button-outlined add-btn" @click="showPublisherDialog = true" v-tooltip.top="'Ajouter un éditeur'" />
        </div>
      </div>
      <div class="field">
        <label for="pages">Nombre de pages</label>
        <InputNumber id="pages" v-model="form.pages" :min="1" class="input" />
      </div>
      <div class="field full">
        <label>Image de couverture</label>
        <FileUpload mode="basic" accept="image/*" :auto="false" @select="onCoverChange" choose-label="Choisir une image" />
        <div v-if="coverPreview" class="cover-preview">
          <img :src="coverPreview" alt="Aperçu" />
        </div>
      </div>
      <div class="field full">
        <label>Fichier PDF/DOC</label>
        <FileUpload mode="basic" name="proposed_file" accept=".pdf,.doc,.docx,.odt,.txt" :auto="false" @select="onDocumentChange" choose-label="Choisir un fichier" />
        <small v-if="documentFile" class="file-name">{{ documentFile.name }}</small>
      </div>
    </div>

    <Button type="submit" label="Soumettre la demande" icon="pi pi-send" class="submit-btn" />

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
  </form>
</template>

<style scoped>
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
.field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.field.full {
  grid-column: 1 / -1;
}
.field label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-primary);
}
.required {
  color: var(--destructive);
}
.input {
  width: 100%;
}
.input-invalid {
  border-color: var(--destructive) !important;
}
.error {
  font-size: 0.8rem;
  color: var(--destructive);
}
.select-with-create {
  display: flex;
  gap: 0.35rem;
  align-items: center;
}
.select-with-create .input {
  flex: 1;
}
.add-btn {
  flex-shrink: 0;
}
.cover-preview {
  margin-top: 0.5rem;
  width: 120px;
  height: 160px;
  border-radius: 0.5rem;
  overflow: hidden;
  border: 1px solid var(--border);
}
.cover-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.file-name {
  font-size: 0.8rem;
  color: var(--text-secondary);
  margin-top: 0.15rem;
}
.submit-btn {
  margin-top: 1rem;
  align-self: flex-start;
}
.dialog-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
}
</style>
