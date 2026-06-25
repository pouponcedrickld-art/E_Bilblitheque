<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import http from '@/services/http'
import type { Reference, Category } from '@/types'
import Button from 'primevue/button'
import Paginator from 'primevue/paginator'
import CatalogFilters from '@/Components/Visitor/CatalogFilters.vue'
import DocTypeIcon from '@/Components/Shared/DocTypeIcon.vue'

interface FilterState {
  category_id?: number | null
  document_type_id?: number | null
  language?: string | null
  keyword?: string | null
}

const router = useRouter()
const route = useRoute()
const references = ref<Reference[]>([])
const categories = ref<Category[]>([])
const languageLabels: Record<string, string> = {
  fr: 'Français', en: 'Anglais', ar: 'Arabe', es: 'Espagnol',
  de: 'Allemand', pt: 'Portugais', it: 'Italien', ru: 'Russe',
  zh: 'Chinois', ja: 'Japonais', autre: 'Autre',
}
const documentTypes = ref<{ id: number; name: string; label: string }[]>([])
const loading = ref(false)
const search = ref('')
const filters = ref<FilterState>({
  category_id: null,
  document_type_id: null,
  language: null,
  keyword: null,
})
const totalRecords = ref(0)
const currentPage = ref(1)
const rows = 15

let debounceTimer: ReturnType<typeof setTimeout> | null = null

async function fetchReferences(page = 1) {
  loading.value = true
  try {
    const params: Record<string, any> = { page, per_page: rows }
    if (search.value.trim()) params.search = search.value.trim()
    if (filters.value.category_id) params.category_id = filters.value.category_id
    if (filters.value.document_type_id) params.document_type_id = filters.value.document_type_id
    if (filters.value.language) params.language = filters.value.language
    if (filters.value.keyword) params.keyword = filters.value.keyword
    const res = await http.get('/references', { params })
    const body = res.data
    references.value = body.data ?? body ?? []
    totalRecords.value = body.total ?? body.meta?.total ?? references.value.length
    currentPage.value = body.current_page ?? body.meta?.current_page ?? page
  } catch {
    references.value = []
    totalRecords.value = 0
  } finally {
    loading.value = false
  }
}

async function fetchCategories() {
  try {
    const res = await http.get('/categories')
    categories.value = res.data?.data ?? res.data ?? []
  } catch {}
}

async function fetchDocumentTypes() {
  try {
    const res = await http.get('/document-types')
    documentTypes.value = res.data?.data ?? res.data ?? []
  } catch {}
}

function onSearchInput() {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => fetchReferences(1), 300)
}

function onPageChange(event: { page: number; first: number; rows: number }) {
  fetchReferences(event.page + 1)
}

function viewDetail(id: number) {
  router.push(`/references/${id}`)
}

onMounted(() => {
  if (route.query.search) {
    search.value = route.query.search as string
  }
  fetchCategories()
  fetchDocumentTypes()
  fetchReferences()
})
</script>

<template>
  <div class="catalogue">
    <aside class="filters-sidebar">
      <div class="filters-card">
        <CatalogFilters v-model="filters" :categories="categories" :document-types="documentTypes" />
        <hr class="gold-rule" />
        <Button label="Appliquer les filtres" @click="fetchReferences(1)" class="filter-btn" />
      </div>
    </aside>

    <div class="main-content">
      <div class="search-bar">
        <i class="pi pi-search search-icon" />
        <input v-model="search" type="text" class="search-input" placeholder="Rechercher un titre, un auteur..." @input="onSearchInput" />
      </div>

      <div v-if="loading && !references.length" class="loading-spinner">
        <i class="pi pi-spin pi-spinner" />
        <p>Chargement des références...</p>
      </div>

      <div v-else-if="!loading && references.length === 0" class="empty-state">
        <i class="pi pi-book-open" />
        <p v-if="search">Aucun résultat pour "{{ search }}".</p>
        <p v-else>Aucune référence trouvée.</p>
      </div>

      <div v-else class="results">
        <p v-if="search" class="result-count">
          <span class="count-num">{{ totalRecords }}</span> résultat(s) pour "{{ search }}"
        </p>

        <div class="grid">
          <div
            v-for="ref in references"
            :key="ref.id"
            class="card"
            @click="viewDetail(ref.id)"
          >
            <div class="card-icon"><DocTypeIcon :type="ref.document_type?.name" :size="32" /></div>
            <div class="card-body">
              <div class="card-top">
                <span class="card-badge">{{ ref.document_type?.label ?? ref.document_type?.name ?? '-' }}</span>
              </div>
              <h3 class="card-title">{{ ref.title }}</h3>
              <p v-if="ref.authors?.length" class="card-authors">
                {{ ref.authors.map(a => a.full_name).join(', ') }}
              </p>
              <div class="card-meta">
                <span v-if="ref.publication_year"><i class="pi pi-calendar" /> {{ ref.publication_year }}</span>
                <span v-if="ref.language">{{ languageLabels[ref.language] ?? ref.language }}</span>
              </div>
            </div>
          </div>
        </div>

        <Paginator
          v-if="totalRecords > rows"
          :first="(currentPage - 1) * rows"
          :rows="rows"
          :totalRecords="totalRecords"
          @page="onPageChange"
          class="paginator"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.catalogue {
  display: grid;
  grid-template-columns: 260px 1fr;
  gap: 1.5rem;
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}

.filters-sidebar {
  position: sticky;
  top: 1.5rem;
  align-self: start;
}

.filters-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.filter-btn {
  width: 100%;
  background: var(--primary) !important;
  border: none !important;
  border-radius: var(--radius-lg) !important;
  padding: 0.65rem !important;
}
.filter-btn:hover {
  background: var(--primary-dark) !important;
  box-shadow: 0 0 0 3px var(--gold-glow) !important;
}

.main-content { min-width: 0; }

.search-bar {
  display: flex;
  align-items: center;
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 0 1rem;
  margin-bottom: 1.5rem;
  transition: border-color 0.2s;
}
.search-bar:focus-within {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px var(--gold-glow);
}

.search-icon {
  color: var(--gold-dark);
  font-size: 0.9rem;
  flex-shrink: 0;
}

.search-input {
  flex: 1;
  padding: 0.75rem 0.75rem;
  border: none;
  background: transparent;
  font-size: 0.875rem;
  outline: none;
  color: var(--foreground);
  font-family: var(--font-sans);
}
.search-input::placeholder {
  color: var(--muted-foreground);
  font-style: italic;
}

.result-count {
  font-size: 0.85rem;
  color: var(--muted-foreground);
  margin-bottom: 1rem;
}

.count-num { font-weight: 700; color: var(--foreground); }

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 0.85rem;
}

.card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.15rem;
  display: flex;
  gap: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}
.card:hover {
  border-color: var(--border-gold);
  box-shadow: 0 4px 16px rgba(200,164,92,0.1);
  transform: translateY(-2px);
}

.card-icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  color: var(--gold-dark);
}

.card-body { flex: 1; min-width: 0; }

.card-top { margin-bottom: 0.25rem; }

.card-badge {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  background: rgba(200, 164, 92, 0.12);
  color: var(--gold-dark);
  border-radius: 999px;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.card-title {
  font-size: 0.9rem;
  font-weight: 600;
  line-height: 1.35;
  margin-bottom: 0.3rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  color: var(--foreground);
}

.card-authors {
  font-size: 0.8rem;
  color: var(--muted-foreground);
  margin-bottom: 0.35rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card-meta {
  display: flex;
  gap: 0.75rem;
  font-size: 0.75rem;
  color: var(--muted-foreground);
}
.card-meta i { font-size: 0.65rem; margin-right: 0.15rem; }

.paginator { margin-top: 1.5rem; }

:deep(.p-paginator) {
  background: transparent;
  border: none;
}
:deep(.p-paginator .p-paginator-page.p-highlight) {
  background: var(--primary);
  color: var(--gold-light);
  border-radius: var(--radius-lg);
}
:deep(.p-paginator .p-paginator-page:hover) {
  border-radius: var(--radius-lg);
}

@media (max-width: 768px) {
  .catalogue { grid-template-columns: 1fr; }
  .filters-sidebar { position: static; }
  .grid { grid-template-columns: 1fr; }
}
</style>
