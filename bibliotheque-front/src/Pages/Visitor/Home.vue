<script setup lang="ts">
// Importations Vue, routeur, services et types
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import type { Reference } from '@/types'

// Routeur et données réactives
const router = useRouter()
const featured = ref<Reference[]>([])
const references = ref<Reference[]>([])
const categories = ref<string[]>([])
const stats = ref({ total_references: 0, total_categories: 0, total_authors: 0, total_downloads: 0, total_views: 0 })
const loading = ref(true)
const query = ref('')
const category = ref('Tout')

// Charge les données de la page d'accueil
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

    // Extract unique categories
    const cats = new Set<string>()
    references.value.forEach(r => {
      if (r.category?.name) cats.add(r.category.name)
    })
    categories.value = Array.from(cats)
  } catch {
    featured.value = []
    references.value = []
  } finally {
    loading.value = false
  }
}

// Filtre les références par catégorie et recherche textuelle
const filtered = computed(() => {
  let list = references.value
  if (category.value !== 'Tout') {
    list = list.filter(r => r.category?.name === category.value)
  }
  if (query.value) {
    const q = query.value.toLowerCase()
    list = list.filter(r =>
      r.title.toLowerCase().includes(q) ||
      (r.subtitle && r.subtitle.toLowerCase().includes(q)) ||
      r.authors?.some(a => a.full_name?.toLowerCase().includes(q))
    )
  }
  return list
})

// Navigue vers une route donnée
function go(path: string) {
  router.push(path)
}

// Navigue vers le détail d'une référence
function viewDetail(id: number) {
  router.push(`/references/${id}`)
}

// Charge les données au montage du composant
onMounted(fetchData)
</script>

<template>
  <div style="background: var(--background); min-height: 100vh">
    <!-- Hero -->
    <section class="hero">
      <div class="hero-bg" />
      <div class="hero-content">
        <div class="hero-badge">
          <span class="hero-badge-dot" />
          <span class="hero-badge-text">{{ loading ? 'Chargement...' : `${stats.total_references} références disponibles` }}</span>
        </div>
        <h1 class="hero-title">
          La connaissance,<br />accessible à tous
        </h1>
        <p class="hero-desc">
          Explorez des milliers d'ouvrages académiques, thèses, manuels et articles scientifiques depuis n'importe quel appareil.
        </p>

        <!-- Search -->
        <div class="hero-search">
          <i class="pi pi-search" style="font-size: 1rem; color: var(--muted-foreground); flex-shrink: 0; margin-left: 1.25rem"></i>
          <input
            v-model="query"
            placeholder="Titre, auteur, mots-clés..."
            class="hero-search-input"
          />
          <button @click="go(`/catalogue?search=${encodeURIComponent(query)}`)" class="hero-search-btn">
            Chercher
          </button>
        </div>

        <!-- Key numbers -->
        <div class="hero-numbers">
          <div class="hero-number-item">
            <p class="hero-number-value">{{ stats.total_references || '0' }}</p>
            <p class="hero-number-label">Références</p>
          </div>
          <div class="hero-number-item">
            <p class="hero-number-value">{{ stats.total_categories || '0' }}</p>
            <p class="hero-number-label">Catégories</p>
          </div>
          <div class="hero-number-item">
            <p class="hero-number-value">{{ (stats.total_authors) || '0' }}</p>
            <p class="hero-number-label">Auteurs</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <i class="pi pi-spin pi-spinner" style="font-size: 2rem; color: var(--muted-foreground)"></i>
      <p style="margin-top: 0.75rem; color: var(--muted-foreground); font-size: 0.875rem;">Chargement des références...</p>
    </div>

    <template v-else>
      <!-- Category pills -->
      <div class="category-bar">
        <div class="category-bar-inner">
          <button
            :class="['category-pill', { 'category-pill-active': category === 'Tout' }]"
            @click="category = 'Tout'"
          >
            Tout
          </button>
          <button
            v-for="cat in categories"
            :key="cat"
            :class="['category-pill', { 'category-pill-active': category === cat }]"
            @click="category = cat"
          >
            {{ cat }}
          </button>
        </div>
      </div>

      <!-- Stats section -->
      <div class="content-section">
        <div class="section-header">
          <h2 class="section-title">La bibliothèque en chiffres</h2>
        </div>
        <div class="stats-grid">
          <div class="stat-card">
            <p class="stat-card-label">Références</p>
            <p class="stat-card-value">{{ stats.total_references }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-card-label">Catégories</p>
            <p class="stat-card-value">{{ stats.total_categories }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-card-label">Auteurs</p>
            <p class="stat-card-value">{{ stats.total_authors }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-card-label">Téléchargements</p>
            <p class="stat-card-value">{{ stats.total_downloads }}</p>
          </div>
          <div class="stat-card">
            <p class="stat-card-label">Consultations</p>
            <p class="stat-card-value">{{ stats.total_views }}</p>
          </div>
        </div>
      </div>

      <!-- Featured -->
      <div v-if="featured.length" class="content-section" style="padding-bottom: 0">
        <div class="section-header">
          <h2 class="section-title">À la une</h2>
          <button @click="go('/catalogue')" class="section-action">Voir tout</button>
        </div>
        <div class="featured-grid">
          <div
            v-for="ref in featured"
            :key="ref.id"
            class="featured-card"
            @click="viewDetail(ref.id)"
          >
            <div class="featured-card-img">
              <img
                v-if="ref.cover_url"
                :src="ref.cover_url"
                :alt="ref.title"
                class="featured-card-cover"
              />
              <div v-else class="featured-card-placeholder">
                <i class="pi pi-book" style="font-size: 1.5rem; color: var(--primary)"></i>
              </div>
            </div>
            <div class="featured-card-body">
              <span class="featured-card-type">{{ ref.document_type?.label ?? ref.document_type?.name ?? ref.document_type }}</span>
              <h3 class="featured-card-title">{{ ref.title }}</h3>
              <p v-if="ref.authors?.length" class="featured-card-author">
                {{ ref.authors.map(a => a.full_name).join(', ') }}
              </p>
              <div class="featured-card-meta">
                <span v-if="ref.publication_year">{{ ref.publication_year }}</span>
                <span v-if="ref.download_count !== undefined">{{ ref.download_count }} téléchargements</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Catalog -->
      <div class="content-section">
        <div class="section-header">
          <h2 class="section-title">Toutes les références</h2>
          <button @click="go('/catalogue')" class="section-action">Voir tout</button>
        </div>
        <p class="catalog-count">
          <span class="font-semibold" style="color: var(--foreground)">{{ filtered.length }}</span>
          référence{{ filtered.length !== 1 ? 's' : '' }}
        </p>

        <div v-if="filtered.length === 0" class="empty-state">
          <i class="pi pi-book" style="font-size: 2.5rem; color: var(--muted-foreground); opacity: 0.4"></i>
          <p style="color: var(--muted-foreground); font-size: 0.875rem; margin-top: 0.75rem">Aucune référence trouvée.</p>
        </div>

        <div v-else class="catalog-grid">
          <div
            v-for="ref in filtered"
            :key="ref.id"
            class="catalog-card"
            @click="viewDetail(ref.id)"
          >
            <div class="catalog-card-img">
              <img
                v-if="ref.cover_url"
                :src="ref.cover_url"
                :alt="ref.title"
                class="catalog-card-cover"
              />
              <div v-else class="catalog-card-placeholder">
                <i class="pi pi-book" style="font-size: 1.5rem; color: var(--primary)"></i>
              </div>
            </div>
            <div class="catalog-card-body">
              <span class="catalog-card-type">{{ ref.category?.name || ref.document_type?.label || '-' }}</span>
              <h3 class="catalog-card-title">{{ ref.title }}</h3>
              <p class="catalog-card-author">{{ ref.authors?.length ? ref.authors[0].full_name : '' }}</p>
              <div class="catalog-card-stats">
                <span class="flex items-center gap-1">
                  <i class="pi pi-eye" style="font-size: 0.65rem"></i>
                  {{ ref.view_count || 0 }}
                </span>
                <span class="flex items-center gap-1">
                  <i class="pi pi-download" style="font-size: 0.65rem"></i>
                  {{ ref.download_count || 0 }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="cta-section">
        <div class="cta-card">
          <i class="pi pi-book" style="font-size: 2rem; color: rgba(255,255,255,0.6)"></i>
          <h3 class="cta-title">Accédez à l'intégralité des ressources</h3>
          <p class="cta-desc">
            Créez un compte gratuit pour lire en ligne, télécharger et soumettre vos propres références documentaires.
          </p>
          <button @click="go('/register')" class="cta-btn">
            Créer un compte gratuit
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
/* ─── Hero ─── */
.hero {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.5) 0%, rgba(20, 48, 38, 0.6) 100%),
    url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=1600&h=700&fit=crop&auto=format');
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
}

.hero-bg {
  position: absolute;
  inset: 0;
  opacity: 0; /* Désactiver le bg secondaire */
}
.hero-content {
  position: relative;
  max-width: 1024px;
  margin: 0 auto;
  padding: 4rem 1.5rem;
  text-align: center;
}

@media (min-width: 640px) {
  .hero-content {
    padding: 6rem 1.5rem;
  }
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--surface, rgba(0,0,0,0.02));
  border: 1px solid var(--border, rgba(0,0,0,0.08));
  border-radius: 999px;
  padding: 0.375rem 1rem;
  margin-bottom: 1.5rem;
}

.hero-badge-dot {
  width: 8px;
  height: 8px;
  background: #4ade80;
  border-radius: 50%;
}

.hero-badge-text {
  color: var(--muted-foreground);
  font-size: 0.75rem;
  font-weight: 500;
}

.hero-title {
  color: white;
  margin-bottom: 1.25rem;
  line-height: 1.1;
  font-size: clamp(2rem, 6vw, 4rem);
}

.hero-desc {
  color: rgba(255, 255, 255, 0.65);
  margin-bottom: 2rem;
  max-width: 560px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
  font-size: 0.875rem;
  padding: 0 1rem;
}

@media (min-width: 640px) {
  .hero-desc {
    font-size: 1rem;
  }
}

.hero-search {
  display: flex;
  align-items: center;
  background: white;
  border-radius: var(--radius-xl);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  max-width: 560px;
  margin: 0 auto;
}

.hero-search-input {
  flex: 1;
  padding: 0.875rem 0.75rem;
  font-size: 0.875rem;
  border: none;
  outline: none;
  background: transparent;
  color: var(--foreground);
  font-family: var(--font-sans);
}

@media (min-width: 640px) {
  .hero-search-input {
    padding: 1rem 0.75rem;
    font-size: 1rem;
  }
}

.hero-search-input::placeholder {
  color: var(--muted-foreground);
}

.hero-search-btn {
  padding: 0.75rem 1.25rem;
  margin: 0.25rem;
  border-radius: var(--radius-xl);
  border: none;
  background: var(--primary);
  color: white;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  flex-shrink: 0;
  transition: opacity 0.2s;
}

@media (min-width: 640px) {
  .hero-search-btn {
    padding: 0.875rem 1.5rem;
  }
}

.hero-search-btn:hover {
  opacity: 0.9;
}

.hero-numbers {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2rem;
  margin-top: 2.5rem;
}

@media (min-width: 640px) {
  .hero-numbers {
    gap: 4rem;
  }
}

.hero-number-item {
  text-align: center;
}

.hero-number-value {
  color: white;
  font-weight: 700;
  font-family: var(--font-serif);
  font-size: clamp(1.5rem, 4vw, 2rem);
}

.hero-number-label {
  color: rgba(255, 255, 255, 0.45);
  font-size: 0.75rem;
  margin-top: 0.125rem;
}

@media (min-width: 640px) {
  .hero-number-label {
    font-size: 0.875rem;
  }
}

/* ─── Loading ─── */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 1rem;
}

/* ─── Category pills ─── */
.category-bar {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem 0.75rem;
}

.category-bar-inner {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.25rem;
}

.category-bar-inner {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.category-bar-inner::-webkit-scrollbar { display: none; }

.category-pill {
  padding: 0.5rem 1rem;
  border-radius: 999px;
  font-size: 0.8125rem;
  font-weight: 500;
  white-space: nowrap;
  border: 1px solid var(--border);
  background: white;
  color: var(--muted-foreground);
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.category-pill:hover {
  color: var(--foreground);
}

.category-pill-active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
  box-shadow: 0 2px 8px rgba(27, 67, 50, 0.25);
}

/* ─── Content sections ─── */
.content-section {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--foreground);
}

.section-action {
  background: none;
  border: none;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--primary);
  cursor: pointer;
  transition: opacity 0.2s;
  padding: 0;
}

.section-action:hover {
  opacity: 0.8;
}

/* ─── Stats grid ─── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

@media (min-width: 640px) {
  .stats-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}

.stat-card {
  background: white;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border);
  padding: 1.25rem;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.stat-card-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--muted-foreground);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.375rem;
}

.stat-card-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--foreground);
  font-family: var(--font-serif);
}

/* ─── Featured grid ─── */
.featured-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
}

@media (min-width: 640px) {
  .featured-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .featured-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.featured-card {
  background: white;
  border-radius: var(--radius-xl);
  border: 2px solid var(--primary);
  overflow: hidden;
  cursor: pointer;
  transition: box-shadow 0.2s, transform 0.2s;
  display: flex;
  flex-direction: column;
}

.featured-card:hover {
  box-shadow: 0 4px 16px rgba(27, 67, 50, 0.15);
  transform: translateY(-2px);
}

.featured-card-img {
  aspect-ratio: 16/9;
  background: var(--muted);
  overflow: hidden;
}

.featured-card-cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.featured-card:hover .featured-card-cover {
  transform: scale(1.05);
}

.featured-card-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--muted);
}

.featured-card-body {
  padding: 1rem;
  flex: 1;
}

.featured-card-type {
  font-size: 0.6875rem;
  font-weight: 600;
  color: var(--primary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.featured-card-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--foreground);
  margin-top: 0.25rem;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.featured-card-author {
  font-size: 0.8125rem;
  color: var(--muted-foreground);
  margin-top: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.featured-card-meta {
  display: flex;
  gap: 0.75rem;
  font-size: 0.75rem;
  color: var(--muted-foreground);
  margin-top: 0.5rem;
}

/* ─── Catalog grid ─── */
.catalog-count {
  font-size: 0.875rem;
  color: var(--muted-foreground);
  margin-bottom: 0.75rem;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
}

.catalog-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

@media (min-width: 640px) {
  .catalog-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 768px) {
  .catalog-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (min-width: 1024px) {
  .catalog-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}

@media (min-width: 1280px) {
  .catalog-grid {
    grid-template-columns: repeat(6, 1fr);
  }
}

.catalog-card {
  background: white;
  border-radius: var(--radius-xl);
  border: 1px solid var(--border);
  overflow: hidden;
  cursor: pointer;
  transition: box-shadow 0.2s, transform 0.2s;
}

.catalog-card:hover {
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  transform: translateY(-2px);
}

.catalog-card-img {
  aspect-ratio: 3/4;
  background: var(--muted);
  overflow: hidden;
}

.catalog-card-cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.catalog-card:hover .catalog-card-cover {
  transform: scale(1.05);
}

.catalog-card-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--muted);
}

.catalog-card-body {
  padding: 0.75rem;
}

.catalog-card-type {
  font-size: 0.625rem;
  font-weight: 600;
  color: var(--muted-foreground);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.catalog-card-title {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--foreground);
  margin-top: 0.25rem;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.catalog-card-author {
  font-size: 0.6875rem;
  color: var(--muted-foreground);
  margin-top: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.catalog-card-stats {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.6875rem;
  color: var(--muted-foreground);
  margin-top: 0.5rem;
}

/* ─── CTA ─── */
.cta-section {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem 3rem;
}

.cta-card {
  border-radius: var(--radius-xl);
  padding: 2rem 1.5rem;
  text-align: center;
  background: linear-gradient(135deg, #143026 0%, #1B4332 100%);
}

@media (min-width: 640px) {
  .cta-card {
    padding: 3rem 2rem;
  }
}

.cta-title {
  color: white;
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
}

@media (min-width: 640px) {
  .cta-title {
    font-size: 1.5rem;
  }
}

.cta-desc {
  color: rgba(255, 255, 255, 0.55);
  font-size: 0.875rem;
  max-width: 480px;
  margin: 0 auto 1.5rem;
  line-height: 1.6;
}

.cta-btn {
  padding: 0.75rem 1.75rem;
  background: white;
  color: var(--primary);
  border: none;
  border-radius: var(--radius-xl);
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.2s;
}

.cta-btn:hover {
  opacity: 0.9;
}

/* ─── Utilities ─── */
.flex { display: flex; }
.items-center { align-items: center; }
.gap-1 { gap: 0.25rem; }
.font-semibold { font-weight: 600; }
</style>
