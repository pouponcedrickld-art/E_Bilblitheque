<script setup lang="ts">
import { ref, onMounted } from 'vue'
import http from '@/services/http'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import MultiSelect from 'primevue/multiselect'
import InputNumber from 'primevue/inputnumber'
import FileUpload from 'primevue/fileupload'
import Button from 'primevue/button'

const emit = defineEmits<{
  (e: 'submit', data: FormData): void
}>()

const form = ref({
  title: '',
  subtitle: '',
  abstract: '',
  description: '',
  isbn: '',
  publication_year: null as number | null,
  language: 'fr',
  document_type: 'article',
  category_id: null as number | null,
  publisher_id: null as number | null,
  pages: null as number | null,
})

const categories = ref<{ id: number; name: string }[]>([])
const publishers = ref<{ id: number; name: string }[]>([])
const keywords = ref<{ id: number; name: string }[]>([])
const coverFile = ref<File | null>(null)
const coverPreview = ref<string | null>(null)
const documentFile = ref<File | null>(null)
const submitted = ref(false)

const documentTypes = [
  { label: 'Article', value: 'article' },
  { label: 'Livre', value: 'livre' },
  { label: 'Mémoire', value: 'memoire' },
  { label: 'Thèse', value: 'these' },
  { label: 'Revue', value: 'revue' },
  { label: 'Rapport', value: 'rapport' },
  { label: 'Guide', value: 'guide' },
  { label: 'Autre', value: 'autre' },
]

const languages = [
  { label: 'Français', value: 'fr' },
  { label: 'Anglais', value: 'en' },
  { label: 'Autre', value: 'autre' },
]

async function loadFormData() {
  try {
    const [catRes, pubRes] = await Promise.all([
      http.get('/categories'),
      http.get('/publishers'),
    ])
    categories.value = catRes.data?.data ?? catRes.data ?? []
    publishers.value = pubRes.data?.data ?? pubRes.data ?? []
  } catch {}
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
  fd.append('document_type', form.value.document_type)
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
        <label for="document_type">Type de document</label>
        <Select id="document_type" v-model="form.document_type" :options="documentTypes" option-label="label" option-value="value" class="input" />
      </div>
      <div class="field">
        <label for="category_id">Catégorie</label>
        <Select id="category_id" v-model="form.category_id" :options="categories" option-label="name" option-value="id" show-clear placeholder="Sélectionner une catégorie" class="input" />
      </div>
      <div class="field">
        <label for="publisher_id">Éditeur</label>
        <Select id="publisher_id" v-model="form.publisher_id" :options="publishers" option-label="name" option-value="id" show-clear placeholder="Sélectionner un éditeur" class="input" />
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
@media (max-width: 640px) {
  .form-grid { grid-template-columns: 1fr; }
}
</style>
