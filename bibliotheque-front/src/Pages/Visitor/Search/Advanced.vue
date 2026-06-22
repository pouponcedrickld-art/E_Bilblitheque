<script setup lang="ts">
// Importations Vue, routeur, composables et composants
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

// Routeur et état de recherche via le composable useSearch
const router = useRouter()
const { query, results, loading, filters, search, reset } = useSearch()

// Données pour les listes déroulantes
const categories = ref<Category[]>([])
const documentTypes = [
  { label: 'Livre', value: 'livre' },
  { label: 'Mémoire', value: 'memoire' },
  { label: 'Thèse', value: 'these' },
  { label: 'Article', value: 'article' },
  { label: 'Revue', value: 'revue' },
  { label: 'Rapport', value: 'rapport' },
  { label: 'Guide', value: 'guide' },
]
const languages = [
  { label: 'Français', value: 'fr' },
  { label: 'Anglais', value: 'en' },
  { label: 'Arabe', value: 'ar' },
  { label: 'Espagnol', value: 'es' },
  { label: 'Allemand', value: 'de' },
  { label: 'Portugais', value: 'pt' },
  { label: 'Italien', value: 'it' },
  { label: 'Russe', value: 'ru' },
  { label: 'Chinois', value: 'zh' },
  { label: 'Japonais', value: 'ja' },
]

// Récupère les catégories depuis l'API
async function fetchCategories() {
  try {
    const res = await http.get('/categories')
    categories.value = res.data?.data ?? res.data ?? []
  } catch {
    categories.value = []
  }
}

// Réinitialise tous les filtres
function clearFilters() {
  reset()
}

// Navigue vers le détail d'une référence
function viewDetail(id: number) {
  router.push(`/references/${id}`)
}

// Retourne l'icône correspondant au type de document
function getTypeIcon(type: string): string {
  const icons: Record<string, string> = {
    livre: '📖', memoire: '📄', these: '🎓', article: '📰',
    revue: '📓', rapport: '📑', guide: '📋',
  }
  return icons[type] || '📄'
}

// Charge les catégories au montage
onMounted(fetchCategories)
</script>

<template>
  <div class="advanced-search">
    <div class="page-header">
      <h1 class="page-title">Recherche avancée</h1>
      <p class="page-desc">Affinez votre recherche avec plusieurs critères</p>
    </div>

    <Card class="filter-card">
      <template #content>
        <div class="filter-form">
          <div class="field-row">
            <div class="field flex-2">
              <label>Titre / Résumé</label>
              <InputText v-model="query" placeholder="Rechercher dans le titre ou le résumé..." class="field-input" @keyup.enter="search" />
            </div>
            <div class="field flex-1">
              <label>Auteur</label>
              <InputText v-model="filters.author" placeholder="Nom de l'auteur..." class="field-input" @keyup.enter="search" />
            </div>
          </div>
          <div class="field-row three-col">
            <div class="field">
              <label>Catégorie</label>
              <Select v-model="filters.category_id" :options="categories" optionLabel="name" optionValue="id" placeholder="Toutes les catégories" clearable class="field-input" />
            </div>
            <div class="field">
              <label>Type de document</label>
              <Select v-model="filters.document_type" :options="documentTypes" optionLabel="label" optionValue="value" placeholder="Tous les types" clearable class="field-input" />
            </div>
            <div class="field">
              <label>Langue</label>
              <Select v-model="filters.language" :options="languages" optionLabel="label" optionValue="value" placeholder="Toutes les langues" clearable class="field-input" />
            </div>
          </div>
          <div class="field">
            <label>Mot-clé</label>
            <InputText v-model="filters.keyword" placeholder="Rechercher par mot-clé..." class="field-input" @keyup.enter="search" />
          </div>
          <div class="filter-actions">
            <Button label="Rechercher" icon="pi pi-search" @click="search" :loading="loading" />
            <Button label="Effacer les filtres" severity="secondary" outlined icon="pi pi-times" @click="clearFilters" />
          </div>
        </div>
      </template>
    </Card>

    <div v-if="loading" class="message-box">
      <Message severity="info" :closable="false">Recherche en cours...</Message>
    </div>

    <div v-else-if="results.length === 0 && (query || filters.author)" class="message-box">
      <Message severity="warn" :closable="false">Aucun résultat trouvé pour votre recherche. Essayez de modifier vos critères.</Message>
    </div>

    <template v-else-if="results.length > 0">
      <div class="results-header">
        <span class="result-count">{{ results.length }} résultat(s) trouvé(s)</span>
      </div>

      <div class="grid">
        <div v-for="ref in results" :key="ref.id" class="card" @click="viewDetail(ref.id)">
          <div v-if="ref.cover_url" class="card-cover">
            <img :src="ref.cover_url" :alt="ref.title" />
          </div>
          <div v-else class="card-icon-wrapper">
            <span class="card-icon-lg">{{ getTypeIcon(ref.document_type) }}</span>
          </div>
          <div class="card-body">
            <div class="card-header">
              <span class="card-badge">{{ ref.document_type }}</span>
              <span v-if="ref.status === 'published'" class="card-status">Publié</span>
            </div>
            <h3 class="card-title">{{ ref.title }}</h3>
            <p v-if="ref.subtitle" class="card-subtitle">{{ ref.subtitle }}</p>
            <p v-if="ref.authors?.length" class="card-authors">
              {{ ref.authors.map(a => a.full_name).join(', ') }}
            </p>
            <div class="card-meta">
              <span v-if="ref.publication_year" class="meta-item"><i class="pi pi-calendar" /> {{ ref.publication_year }}</span>
              <span v-if="ref.language" class="meta-item"><i class="pi pi-globe" /> {{ ref.language }}</span>
              <span v-if="ref.keywords?.length" class="meta-item"><i class="pi pi-tag" /> {{ ref.keywords.map(k => k.name).slice(0, 3).join(', ') }}{{ ref.keywords.length > 3 ? '...' : '' }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="empty-state">
      <div class="empty-icon">🎯</div>
      <p>Utilisez les filtres ci-dessus pour affiner votre recherche dans le catalogue.</p>
    </div>
  </div>
</template>

<style scoped>
.advanced-search {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 1.5rem;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}

.page-desc {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

.filter-card {
  margin-bottom: 1.5rem;
  border-radius: var(--radius-lg);
}

.filter-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.field label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-primary);
}

.field-row {
  display: flex;
  gap: 1rem;
}

.field-row.three-col {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
}

.flex-1 { flex: 1; }
.flex-2 { flex: 2; }

.field-input {
  width: 100%;
}

.filter-actions {
  display: flex;
  gap: 0.75rem;
  padding-top: 0.25rem;
}

.message-box {
  margin-bottom: 1.5rem;
}

.results-header {
  margin-bottom: 1rem;
}

.result-count {
  font-size: 0.85rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 1rem;
}

.card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  display: flex;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s;
}

.card:hover {
  border-color: var(--primary);
  box-shadow: 0 4px 16px rgba(27, 67, 50, 0.12);
  transform: translateY(-2px);
}

.card-cover {
  width: 120px;
  min-height: 100%;
  flex-shrink: 0;
  overflow: hidden;
  background: var(--muted);
}

.card-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-icon-wrapper {
  width: 120px;
  min-height: 140px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--muted);
}

.card-icon-lg {
  font-size: 2.5rem;
}

.card-body {
  flex: 1;
  padding: 1rem 1.25rem;
  min-width: 0;
}

.card-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.4rem;
}

.card-badge {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  background: rgba(27, 67, 50, 0.08);
  color: var(--primary);
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 600;
  text-transform: uppercase;
}

.card-status {
  font-size: 0.68rem;
  color: #16a34a;
  font-weight: 500;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.35;
  margin-bottom: 0.2rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-subtitle {
  font-size: 0.8rem;
  color: var(--text-secondary);
  margin-bottom: 0.35rem;
  font-style: italic;
}

.card-authors {
  font-size: 0.8rem;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.meta-item {
  font-size: 0.72rem;
  color: var(--text-secondary);
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.meta-item i {
  font-size: 0.65rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 1rem;
  color: var(--text-secondary);
}

.empty-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.empty-state p {
  max-width: 400px;
  margin: 0 auto;
  font-size: 0.95rem;
  line-height: 1.5;
}

@media (max-width: 768px) {
  .field-row { flex-direction: column; }
  .field-row.three-col { grid-template-columns: 1fr; }
  .grid { grid-template-columns: 1fr; }
  .card-cover { width: 80px; }
  .card-icon-wrapper { width: 80px; min-height: 100px; }
}
</style>
