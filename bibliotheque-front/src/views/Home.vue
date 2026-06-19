<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/axios'
import type { Reference } from '@/types'

const router = useRouter()
const references = ref<Reference[]>([])
const loading = ref(true)
const search = ref('')

async function fetchReferences() {
  loading.value = true
  try {
    const res = await http.get('/references')
    references.value = res.data?.data ?? res.data ?? []
  } catch {
    references.value = []
  } finally {
    loading.value = false
  }
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

onMounted(fetchReferences)
</script>

<template>
  <div class="home">
    <div class="hero">
      <h1>Bibliothèque Numérique</h1>
      <p>Explorez notre collection de références académiques et professionnelles</p>
    </div>

    <div class="filters">
      <input v-model="search" type="text" class="search-input" placeholder="Rechercher un titre, un auteur..." />
    </div>

    <div v-if="loading" class="loading">
      <p>Chargement des références...</p>
    </div>

    <div v-else-if="references.length === 0" class="empty">
      <p>Aucune référence trouvée.</p>
    </div>

    <div v-else class="grid">
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
  </div>
</template>

<style scoped>
.home {
  max-width: 1200px;
  margin: 0 auto;
}

.hero {
  text-align: center;
  padding: 2.5rem 1rem;
}

.hero h1 {
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: 0.5rem;
}

.hero p {
  font-size: 1rem;
  color: var(--text-secondary);
  max-width: 500px;
  margin: 0 auto;
}

.filters {
  margin-bottom: 1.5rem;
}

.search-input {
  width: 100%;
  padding: 0.7rem 1rem;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  font-size: 0.9rem;
  outline: none;
  background: #fff;
  transition: border-color 0.15s;
}

.search-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.loading, .empty {
  text-align: center;
  padding: 3rem;
  color: var(--text-secondary);
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
