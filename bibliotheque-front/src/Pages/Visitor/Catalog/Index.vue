<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import http from '@/services/http'
import type { Reference, Category } from '@/types'
import Button from 'primevue/button'
import Paginator from 'primevue/paginator'
import CatalogFilters from '@/Components/Visitor/CatalogFilters.vue'

interface FilterState {
  category_id?: number | null
  document_type?: string | null
  language?: string | null
  keyword?: string | null
}

const router = useRouter()
const route = useRoute()
const references = ref<Reference[]>([])
const categories = ref<Category[]>([])
const loading = ref(false)
const search = ref('')
const filters = ref<FilterState>({
  category_id: null,
  document_type: null,
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
    if (filters.value.document_type) params.document_type = filters.value.document_type
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
  } catch {
    // ignore
  }
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

function getTypeIcon(type: string): string {
  const icons: Record<string, string> = {
    livre: '📖', memoire: '📄', these: '🎓', article: '📰',
    revue: '📓', rapport: '📑', guide: '📋',
  }
  return icons[type] || '📄'
}

onMounted(() => {
  if (route.query.search) {
    search.value = route.query.search as string
  }
  fetchCategories()
  fetchReferences()
})
</script>

<template>
  <div class="catalogue">
    <aside class="filters-sidebar">
      <CatalogFilters v-model="filters" :categories="categories" />
      <Button label="Appliquer les filtres" @click="fetchReferences(1)" class="filter-btn" />
    </aside>

    <div class="main-content">
      <div class="search-bar">
        <input v-model="search" type="text" class="search-input" placeholder="Rechercher un titre, un auteur..." @input="onSearchInput" />
      </div>

      <div v-if="loading && !references.length" class="loading">
        <p>Chargement des références...</p>
      </div>

      <div v-else-if="!loading && references.length === 0" class="empty">
        <p v-if="search">Aucun résultat pour "{{ search }}".</p>
        <p v-else>Aucune référence trouvée.</p>
      </div>

      <div v-else class="results">
        <p v-if="search" class="result-count">{{ totalRecords }} résultat(s) pour "{{ search }}"</p>

        <div class="grid">
          <div
            v-for="ref in references"
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
.catalogue { display: grid; grid-template-columns: 260px 1fr; gap: 1.5rem; max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
.filters-sidebar { position: sticky; top: 1.5rem; align-self: start; display: flex; flex-direction: column; gap: 1rem; }
.filter-btn { width: 100%; }
.main-content { min-width: 0; }
.search-bar { margin-bottom: 1.5rem; }
.search-input { width: 100%; padding: 0.7rem 1rem; border: 1px solid var(--border); border-radius: var(--radius-sm); font-size: 0.9rem; outline: none; background: #fff; transition: border-color 0.15s; }
.search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(27, 67, 50, 0.15); }
.loading, .empty { text-align: center; padding: 3rem; color: var(--text-secondary); }
.result-count { font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.card { background: #fff; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 1.25rem; display: flex; gap: 1rem; cursor: pointer; transition: all 0.15s; }
.card:hover { border-color: var(--primary); box-shadow: 0 2px 8px rgba(27, 67, 50, 0.1); transform: translateY(-1px); }
.card-icon { font-size: 2rem; flex-shrink: 0; }
.card-body { flex: 1; min-width: 0; }
.card-badge { display: inline-block; padding: 0.15rem 0.5rem; background: rgba(27, 67, 50, 0.08); color: var(--primary); border-radius: 999px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.35rem; }
.card-title { font-size: 0.95rem; font-weight: 600; line-height: 1.35; margin-bottom: 0.35rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.card-authors { font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 0.35rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.card-meta { display: flex; gap: 0.75rem; font-size: 0.75rem; color: var(--text-secondary); }
.paginator { margin-top: 1.5rem; }
@media (max-width: 768px) {
  .catalogue { grid-template-columns: 1fr; }
  .filters-sidebar { position: static; }
}
</style>
