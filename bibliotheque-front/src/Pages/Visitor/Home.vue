<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import type { Reference } from '@/types'

const router = useRouter()
const featured = ref<Reference[]>([])
const references = ref<Reference[]>([])
const stats = ref({ total_references: 0, total_categories: 0, total_authors: 0, total_downloads: 0, total_views: 0 })
const loading = ref(true)
const search = ref('')

async function fetchData() {
  loading.value = true
  try {
    const [featRes, refsRes, statsRes] = await Promise.all([
      http.get('/references/featured'),
      http.get('/references'),
      http.get('/stats'),
    ])
    featured.value = featRes.data?.data ?? featRes.data ?? []
    references.value = refsRes.data?.data ?? refsRes.data ?? []
    stats.value = statsRes.data ?? stats.value
  } catch {
    featured.value = []
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

onMounted(fetchData)
</script>

<template>
  <div class="home">
    <div class="hero">
      <h1>Bibliothèque Numérique</h1>
      <p>Explorez notre collection de références académiques et professionnelles</p>
    </div>

    <div class="filters">
      <input v-model="search" type="text" class="search-input" placeholder="Rechercher un titre, un auteur..." @keyup.enter="search ? $router.push(`/search?q=${encodeURIComponent(search)}`) : undefined" />
    </div>

    <div v-if="loading" class="loading">
      <p>Chargement des références...</p>
    </div>

    <template v-else>
      <div class="section">
        <h2 class="section-title">La bibliothèque en chiffres</h2>
        <div class="stats-grid">
          <div class="stat-card">
            <span class="stat-value">{{ stats.total_references }}</span>
            <span class="stat-label">Références</span>
          </div>
          <div class="stat-card">
            <span class="stat-value">{{ stats.total_categories }}</span>
            <span class="stat-label">Catégories</span>
          </div>
          <div class="stat-card">
            <span class="stat-value">{{ stats.total_authors }}</span>
            <span class="stat-label">Auteurs</span>
          </div>
          <div class="stat-card">
            <span class="stat-value">{{ stats.total_downloads }}</span>
            <span class="stat-label">Téléchargements</span>
          </div>
          <div class="stat-card">
            <span class="stat-value">{{ stats.total_views }}</span>
            <span class="stat-label">Consultations</span>
          </div>
        </div>
      </div>

      <div v-if="featured.length" class="section">
        <h2 class="section-title">À la une</h2>
        <div class="featured-grid">
          <div
            v-for="ref in featured"
            :key="ref.id"
            class="card featured-card"
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

      <div class="section">
        <h2 class="section-title">Toutes les références</h2>
        <div v-if="references.length === 0" class="empty">
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

.section { margin-bottom: 2rem; }
.section-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 1rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.stat-card { background: #fff; border: 1px solid var(--border, #e5e7eb); border-radius: 0.5rem; padding: 1rem; text-align: center; }
.stat-value { display: block; font-size: 1.5rem; font-weight: 800; line-height: 1.2; }
.stat-label { display: block; font-size: 0.8rem; color: var(--text-secondary, #6b7280); margin-top: 0.25rem; }
.featured-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.featured-card { border-color: var(--primary); border-width: 2px; background: #fafcff; }

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
