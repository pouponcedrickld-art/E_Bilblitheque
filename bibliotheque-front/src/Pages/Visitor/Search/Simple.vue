<script setup lang="ts">
// Importations Vue, routeur, composables et composants
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSearch } from '@/composables/useSearch'
import http from '@/services/http'
import type { Category, DocumentType } from '@/types'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import Button from 'primevue/button'
import Message from 'primevue/message'

// Routeur et état de recherche via le composable useSearch
const router = useRouter()
const { query, results, loading, filters, search } = useSearch()
const categories = ref<Category[]>([])
const documentTypes = ref<DocumentType[]>([])

// Récupère les catégories depuis l'API
async function fetchCategories() {
  try {
    const res = await http.get('/categories')
    categories.value = res.data?.data ?? res.data ?? []
  } catch {
    // ignore
  }
}

// Récupère les types de document depuis l'API
async function fetchDocumentTypes() {
  try {
    const res = await http.get('/document-types')
    documentTypes.value = res.data?.data ?? res.data ?? []
  } catch {
    // ignore
  }
}

// Retourne l'icône correspondant au type de document
function getTypeIcon(type: string): string {
  const icons: Record<string, string> = {
    livre: '📖', memoire: '📄', these: '🎓', article: '📰',
    revue: '📓', rapport: '📑', guide: '📋',
  }
  return icons[type] || '📄'
}

// Navigue vers le détail d'une référence
function viewDetail(id: number) {
  router.push(`/references/${id}`)
}

// Charge les catégories au montage
onMounted(() => { fetchCategories(); fetchDocumentTypes() })
</script>

<template>
  <div class="simple-search">
    <div class="search-hero">
      <h1 class="hero-title">Recherche documentaire</h1>
      <p class="hero-subtitle">Explorez notre catalogue de références littéraires et académiques</p>
      <div class="search-bar">
        <span class="search-icon pi pi-search" />
        <InputText v-model="query" placeholder="Titre, auteur, ISBN, mot-clé..." class="search-input" @keyup.enter="search" />
        <Button label="Rechercher" @click="search" :loading="loading" class="search-btn" />
      </div>
    </div>

    <div class="simple-filters">
      <Select v-model="filters.category_id" :options="categories" optionLabel="name" optionValue="id" placeholder="Toutes les catégories" clearable class="filter-select" @change="search" />
      <Select v-model="filters.document_type_id" :options="documentTypes" optionLabel="label" optionValue="id" placeholder="Tous les types" clearable class="filter-select" @change="search" />
    </div>

    <div v-if="loading" class="message-box">
      <Message severity="info" :closable="false">Recherche en cours...</Message>
    </div>

    <div v-else-if="results.length === 0 && query" class="message-box">
      <Message severity="warn" :closable="false">Aucun résultat trouvé pour "{{ query }}". Vérifiez votre recherche ou essayez la <router-link to="/search/advanced" class="link">recherche avancée</router-link>.</Message>
    </div>

    <template v-else-if="results.length > 0">
      <div class="results-header">
        <span class="result-count">{{ results.length }} résultat(s) trouvé(s)</span>
        <router-link to="/search/advanced" class="advanced-link">Recherche avancée <i class="pi pi-arrow-right" /></router-link>
      </div>

      <div class="grid">
        <div v-for="ref in results" :key="ref.id" class="card" @click="viewDetail(ref.id)">
          <div v-if="ref.cover_url" class="card-cover">
            <img :src="ref.cover_url" :alt="ref.title" />
          </div>
          <div v-else class="card-icon-wrapper">
            <span class="card-icon">{{ getTypeIcon(ref.document_type?.name ?? '') }}</span>
          </div>
          <div class="card-body">
            <div class="card-header">
              <span class="card-badge">{{ ref.document_type?.label ?? ref.document_type?.name ?? ref.document_type }}</span>
              <span v-if="ref.status" class="card-status">{{ ref.status === 'published' ? 'Publié' : ref.status }}</span>
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

    <div v-else-if="!query" class="empty-state">
      <div class="empty-icon">🔍</div>
      <p>Utilisez la barre de recherche ci-dessus pour trouver des références dans notre catalogue.</p>
    </div>
  </div>
</template>

<style scoped>
.simple-search {
  max-width: 1200px;
  margin: 0 auto;
}

.search-hero {
  text-align: center;
  padding: 3rem 1rem;
  margin-bottom: 2rem;
  background: linear-gradient(135deg, rgba(27, 67, 50, 0.04) 0%, rgba(45, 106, 79, 0.08) 100%);
  border-radius: var(--radius-xl);
}

.hero-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.hero-subtitle {
  font-size: 1rem;
  color: var(--text-secondary);
  margin-bottom: 1.5rem;
}

.search-bar {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  max-width: 640px;
  margin: 0 auto;
  background: #fff;
  padding: 0.5rem 0.5rem 0.5rem 1rem;
  border-radius: var(--radius-lg);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.search-icon {
  font-size: 1.1rem;
  color: var(--text-secondary);
  flex-shrink: 0;
}

.search-input {
  flex: 1;
  border: none !important;
  box-shadow: none !important;
  font-size: 1rem;
}

.search-btn {
  flex-shrink: 0;
}

.simple-filters {
  display: flex;
  gap: 0.75rem;
  max-width: 640px;
  margin: 1rem auto;
  padding: 0 1rem;
}
.filter-select { flex: 1; }
.message-box {
  margin-bottom: 1.5rem;
}

.link {
  color: var(--primary);
  font-weight: 500;
}

.results-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.result-count {
  font-size: 0.85rem;
  color: var(--text-secondary);
  font-weight: 500;
}

.advanced-link {
  font-size: 0.85rem;
  color: var(--primary);
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.35rem;
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

.card-icon {
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
  .search-hero {
    padding: 2rem 1rem;
  }
  .hero-title { font-size: 1.35rem; }
  .search-bar {
    flex-wrap: wrap;
    padding: 0.75rem;
    gap: 0.5rem;
  }
  .search-input { width: 100%; }
  .search-btn { width: 100%; }
  .grid { grid-template-columns: 1fr; }
  .card-cover { width: 80px; }
  .card-icon-wrapper { width: 80px; min-height: 100px; }
}
</style>
