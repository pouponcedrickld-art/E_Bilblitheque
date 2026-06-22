<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import MultiSelect from 'primevue/multiselect'
import InputNumber from 'primevue/inputnumber'
import Button from 'primevue/button'
import Message from 'primevue/message'

const route = useRoute()
const router = useRouter()
const toastStore = useToastStore()

const form = ref({
  title: '',
  subtitle: '',
  abstract: '',
  isbn: '',
  publication_year: null as number | null,
  language: '',
  document_type: 'article',
  category_id: null as number | null,
  publisher_id: null as number | null,
  pages: null as number | null,
  status: 'draft',
  keyword_ids: [] as number[],
})

const categories = ref<{ id: number; name: string }[]>([])
const publishers = ref<{ id: number; name: string }[]>([])
const keywords = ref<{ id: number; name: string }[]>([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')

const documentTypes = [
  { label: 'Article', value: 'article' },
  { label: 'Livre', value: 'book' },
  { label: 'Thèse', value: 'thesis' },
  { label: 'Rapport', value: 'report' },
  { label: 'Article de conférence', value: 'conference' },
  { label: 'Chapitre de livre', value: 'book_chapter' },
]

async function load() {
  loading.value = true
  try {
    const id = route.params.id as string
    const [refRes, catRes, pubRes, kwRes] = await Promise.all([
      http.get(`/references/${id}`),
      http.get('/categories'),
      http.get('/publishers'),
      http.get('/keywords'),
    ])
    const refData = refRes.data?.data ?? refRes.data
    form.value = {
      title: refData.title ?? '',
      subtitle: refData.subtitle ?? '',
      abstract: refData.abstract ?? '',
      isbn: refData.isbn ?? '',
      publication_year: refData.publication_year ?? null,
      language: refData.language ?? '',
      document_type: refData.document_type ?? 'article',
      category_id: refData.category_id ?? refData.category?.id ?? null,
      publisher_id: refData.publisher_id ?? refData.publisher?.id ?? null,
      pages: refData.pages ?? null,
      status: refData.status ?? 'draft',
      keyword_ids: (refData.keywords ?? []).map((k: any) => k.id),
    }
    categories.value = catRes.data?.data ?? catRes.data ?? []
    publishers.value = pubRes.data?.data ?? pubRes.data ?? []
    keywords.value = kwRes.data?.data ?? kwRes.data ?? []
  } catch {
    error.value = 'Impossible de charger la référence.'
  } finally {
    loading.value = false
  }
}

async function submit() {
  submitting.value = true
  error.value = ''
  try {
    const id = route.params.id as string
    await http.put(`/references/${id}`, { ...form.value, keyword_ids: form.value.keyword_ids })
    toastStore.success('Référence mise à jour.')
    router.push('/admin/references')
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erreur lors de la mise à jour.'
  } finally {
    submitting.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Modifier la référence</h1>
      <Button icon="pi pi-arrow-left" label="Retour" severity="secondary" text @click="router.push('/admin/references')" />
    </div>

    <div v-if="loading" class="loading">Chargement...</div>

    <Message v-if="error" severity="error" :closable="false" class="mb-3">{{ error }}</Message>

    <form v-else @submit.prevent="submit" class="form">
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
          <InputText id="language" v-model="form.language" placeholder="ex: fr, en" />
        </div>
        <div class="field">
          <label for="document_type">Type de document</label>
          <Select id="document_type" v-model="form.document_type" :options="documentTypes" option-label="label" option-value="value" />
        </div>
        <div class="field">
          <label for="category_id">Catégorie</label>
          <Select id="category_id" v-model="form.category_id" :options="categories" option-label="name" option-value="id" show-clear placeholder="Sélectionner une catégorie" />
        </div>
        <div class="field">
          <label for="publisher_id">Éditeur</label>
          <Select id="publisher_id" v-model="form.publisher_id" :options="publishers" option-label="name" option-value="id" show-clear placeholder="Sélectionner un éditeur" />
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
          <MultiSelect id="keywords" v-model="form.keyword_ids" :options="keywords" option-label="name" option-value="id" placeholder="Sélectionner des mots-clés" :max-selected-labels="5" />
        </div>
      </div>

      <div class="form-actions">
        <Button icon="pi pi-check" label="Enregistrer" type="submit" :loading="submitting" />
        <Button icon="pi pi-times" label="Annuler" severity="secondary" text @click="router.push('/admin/references')" />
      </div>
    </form>
  </div>
</template>

<style scoped>
.page { max-width: 800px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.loading { text-align: center; padding: 2rem; color: var(--text-secondary); }
.mb-3 { margin-bottom: 1rem; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field.full { grid-column: 1 / -1; }
.field label { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
.form-actions { margin-top: 1.5rem; display: flex; gap: 0.75rem; }
@media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
</style>
