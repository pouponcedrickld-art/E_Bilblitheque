<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSearch } from '@/composables/useSearch'
import http from '@/services/http'
import type { Category } from '@/types'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import Card from 'primevue/card'
import Message from 'primevue/message'

const router = useRouter()
const { query, results, loading, filters, search, reset } = useSearch()

const categories = ref<Category[]>([])
const documentTypes = ['livre', 'memoire', 'these', 'article', 'revue', 'rapport', 'guide']
const languages = ['fr', 'en', 'ar', 'es', 'de', 'pt', 'it', 'ru', 'zh', 'ja']

async function fetchCategories() {
  try {
    const res = await http.get('/categories')
    categories.value = res.data?.data ?? res.data ?? []
  } catch {
    categories.value = []
  }
}

function clearFilters() {
  reset()
}

function viewDetail(id: number) {
  router.push(`/references/${id}`)
}

function getTypeIcon(type: string): string {
  const icons: Record<string, string> = {
    livre: '📖', memoire: '📄', these: '🎓', article: '📰',
    revue: '📓', rapport: '📑', guide: '📋',
  }
  return icons[type] || '📄'
}

onMounted(fetchCategories)
</script>

<template>
  <div class="advanced-search">
    <h1 class="page-title">Recherche avancée</h1>

    <Card class="filter-card">
      <template #content>
        <div class="filter-form">
          <div class="field">
            <label>Titre / Résumé</label>
            <InputText v-model="query" placeholder="Rechercher dans le titre ou le résumé..." class="field-input" @keyup.enter="search" />
          </div>
          <div class="field-row">
            <div class="field">
              <label>Catégorie</label>
              <Select v-model="filters.category_id" :options="categories" optionLabel="name" optionValue="id" placeholder="Toutes les catégories" clearable class="field-input" />
            </div>
            <div class="field">
              <label>Type de document</label>
              <Select v-model="filters.document_type" :options="documentTypes" placeholder="Tous les types" clearable class="field-input" />
            </div>
            <div class="field">
              <label>Langue</label>
              <Select v-model="filters.language" :options="languages" placeholder="Toutes les langues" clearable class="field-input" />
            </div>
          </div>
          <div class="field">
            <label>Mot-clé</label>
            <InputText v-model="filters.keyword" placeholder="Rechercher par mot-clé..." class="field-input" />
          </div>
          <div class="filter-actions">
            <Button label="Rechercher" icon="pi pi-search" @click="search" :loading="loading" />
            <Button label="Effacer les filtres" severity="secondary" @click="clearFilters" />
          </div>
        </div>
      </template>
    </Card>

    <div v-if="loading" class="message-box">
      <Message severity="info">Recherche en cours...</Message>
    </div>

    <div v-else-if="results.length === 0 && query" class="message-box">
      <Message severity="warn">Aucun résultat trouvé pour votre recherche.</Message>
    </div>

    <div v-else-if="results.length > 0" class="result-count">
      {{ results.length }} résultat(s) trouvé(s)
    </div>

    <div v-if="results.length > 0" class="grid">
      <div
        v-for="ref in results"
        :key="ref.id"
        class="card"
        @click="viewDetail(ref.id)"
      >
        <div class="card-icon">{{ getTypeIcon(ref.document_type) }}</div>
        <div class="card-body">
          <span class="card-badge">{{ ref.document_type }}</span>
          <h3 class="card-title">{{ ref.title }}</h3>
          <p v-if="ref.authors?.length" class="card-authors">
            {{ ref.authors.map(a => a.full_name).join(', ') }}
          </p>
          <div class="card-meta">
            <span v-if="ref.publication_year">{{ ref.publication_year }}</span>
            <span v-if="ref.language">{{ ref.language }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.advanced-search {
  max-width: 1200px;
  margin: 0 auto;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 1.5rem;
}

.filter-card {
  margin-bottom: 1.5rem;
}

.filter-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.field label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
}

.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1rem;
}

.field-input {
  width: 100%;
}

.filter-actions {
  display: flex;
  gap: 0.75rem;
  padding-top: 0.5rem;
}

.message-box {
  margin-bottom: 1rem;
}

.result-count {
  font-size: 0.85rem;
  color: var(--text-secondary);
  margin-bottom: 1rem;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1.25rem;
  display: flex;
  gap: 1rem;
  cursor: pointer;
  transition: all 0.15s;
}

.card:hover {
  border-color: var(--primary);
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
  transform: translateY(-1px);
}

.card-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.card-body {
  flex: 1;
  min-width: 0;
}

.card-badge {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  background: #eff6ff;
  color: var(--primary);
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  margin-bottom: 0.35rem;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.35;
  margin-bottom: 0.35rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-authors {
  font-size: 0.8rem;
  color: var(--text-secondary);
  margin-bottom: 0.35rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card-meta {
  display: flex;
  gap: 0.75rem;
  font-size: 0.75rem;
  color: var(--text-secondary);
}
</style>
