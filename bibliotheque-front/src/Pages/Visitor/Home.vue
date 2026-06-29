<script setup lang="ts">
import { ref, onMounted, computed, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import { useAuthStore } from '@/stores/auth'
import type { Reference } from '@/types'

const router = useRouter()
const authStore = useAuthStore()
const featured = ref<Reference[]>([])
const references = ref<Reference[]>([])
const categories = ref<string[]>([])
const stats = ref({ total_references: 0, total_categories: 0, total_authors: 0, total_downloads: 0, total_views: 0 })
const loading = ref(true)
const query = ref('')
const category = ref('Tout')
const animatingCount = ref(0)
const isFiltering = ref(false)

const videos = [
  '/videos/12732893_1080_1920_60fps.mp4',
  '/videos/19231459-hd_1080_1920_30fps.mp4',
  '/videos/6132484-hd_1920_1080_30fps.mp4',
  '/videos/6334247-uhd_4096_2160_25fps.mp4',
  '/videos/6550662-uhd_4096_2160_25fps.mp4',
  '/videos/8567036-uhd_2160_4096_25fps.mp4',
]
const currentIdx = ref(0)

function onVideoEnded() {
  currentIdx.value = (currentIdx.value + 1) % videos.length
}

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

watch(filtered, (val) => { animateCounter(val.length) })

function animateCounter(target: number) {
  const start = animatingCount.value
  const duration = 400
  const startTime = performance.now()
  function tick(now: number) {
    const elapsed = now - startTime
    const progress = Math.min(elapsed / duration, 1)
    const eased = 1 - Math.pow(1 - progress, 3)
    animatingCount.value = Math.round(start + (target - start) * eased)
    if (progress < 1) requestAnimationFrame(tick)
  }
  requestAnimationFrame(tick)
}



function selectCategory(cat: string) {
  if (cat === category.value) return
  isFiltering.value = true
  category.value = cat
  nextTick(() => {
    const el = document.getElementById('catalog-section')
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    setTimeout(() => { isFiltering.value = false }, 600)
  })
}

function go(path: string) { router.push(path) }

onMounted(fetchData)
</script>

<template>
  <div class="home">
    <!-- ─── Hero ─── -->
    <section class="hero">
      <Transition name="video-fade" mode="in-out">
        <video
          :key="currentIdx"
          class="hero-video hero-video--active"
          :src="videos[currentIdx]"
          muted
          playsinline
          preload="auto"
          autoplay
          @ended="onVideoEnded"
        />
      </Transition>
      <div class="hero-pattern" />
      <div class="hero-overlay" />
      <div class="hero-content">
        <div class="hero-seal">
          <span class="hero-seal-dot" />
          <span class="hero-seal-text">
            {{ loading ? 'Chargement...' : `${stats.total_references} références disponibles` }}
          </span>
        </div>

        <h1 class="hero-title">
          La connaissance,<br />
          <span class="hero-title-accent">accessible à tous</span>
        </h1>

        <p class="hero-desc">
          Explorez des milliers d'ouvrages académiques, thèses, manuels et articles scientifiques
          depuis n'importe quel appareil.
        </p>

        <div class="hero-search">
          <i class="pi pi-search search-icon" />
          <input
            v-model="query"
            placeholder="Titre, auteur, mots-clés..."
            class="hero-search-input"
          />
          <button @click="go(`/catalogue?search=${encodeURIComponent(query)}`)" class="hero-search-btn">
            Chercher
          </button>
        </div>

        <div class="hero-numbers">
          <div class="hero-number-item">
            <span class="hero-number-value">{{ stats.total_references || '0' }}</span>
            <span class="hero-number-label">Références</span>
          </div>
          <div class="hero-number-item">
            <span class="hero-number-value">{{ stats.total_categories || '0' }}</span>
            <span class="hero-number-label">Catégories</span>
          </div>
          <div class="hero-number-item">
            <span class="hero-number-value">{{ stats.total_authors || '0' }}</span>
            <span class="hero-number-label">Auteurs</span>
          </div>
        </div>
      </div>
    </section>

    <!-- ─── Loading ─── -->
    <div v-if="loading" class="loading-spinner">
      <i class="pi pi-spin pi-spinner" />
      <p>Chargement des références...</p>
    </div>

    <template v-else>
      <!-- ─── Category pills ─── -->
      <div class="category-bar">
        <div class="category-bar-inner">
          <button
            :class="['pill', { 'pill-active': category === 'Tout' }]"
            @click="selectCategory('Tout')"
          >
            Tout
          </button>
          <button
            v-for="cat in categories"
            :key="cat"
            :class="['pill', { 'pill-active': category === cat }]"
            @click="selectCategory(cat)"
          >
            {{ cat }}
          </button>
          <Transition name="pill-fade">
            <span v-if="category !== 'Tout'" class="pill-badge">
              {{ animatingCount }} résultat{{ animatingCount !== 1 ? 's' : '' }}
            </span>
          </Transition>
        </div>
      </div>

      <!-- ─── Stats section ─── -->
      <section class="section">
        <div class="section-header">
          <h2 class="section-title">La bibliothèque en chiffres</h2>
          <hr class="gold-rule-left" />
        </div>
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(200,164,92,0.12)">
              <i class="pi pi-book" style="color:var(--gold-dark)" />
            </div>
            <span class="stat-value">{{ stats.total_references }}</span>
            <span class="stat-label">Références</span>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(200,164,92,0.12)">
              <i class="pi pi-tags" style="color:var(--gold-dark)" />
            </div>
            <span class="stat-value">{{ stats.total_categories }}</span>
            <span class="stat-label">Catégories</span>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(200,164,92,0.12)">
              <i class="pi pi-pencil" style="color:var(--gold-dark)" />
            </div>
            <span class="stat-value">{{ stats.total_authors }}</span>
            <span class="stat-label">Auteurs</span>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(200,164,92,0.12)">
              <i class="pi pi-download" style="color:var(--gold-dark)" />
            </div>
            <span class="stat-value">{{ stats.total_downloads }}</span>
            <span class="stat-label">Téléchargements</span>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(200,164,92,0.12)">
              <i class="pi pi-eye" style="color:var(--gold-dark)" />
            </div>
            <span class="stat-value">{{ stats.total_views }}</span>
            <span class="stat-label">Consultations</span>
          </div>
        </div>
      </section>

      <!-- ─── Featured ─── -->
      <section v-if="featured.length" class="section" style="padding-bottom:0">
        <div class="section-header">
          <h2 class="section-title">À la une</h2>
          <button @click="go('/catalogue')" class="section-action">Voir tout →</button>
        </div>
        <hr class="gold-rule-left" />
        <div class="featured-grid">
          <router-link
            v-for="ref in featured"
            :key="ref.id"
            :to="`/references/${ref.id}`"
            class="featured-card"
          >
            <div class="featured-card-img">
              <img v-if="ref.cover_url" :src="ref.cover_url" :alt="ref.title" class="featured-card-cover" />
              <div v-else class="featured-card-placeholder">
                <i class="pi pi-book" />
              </div>
              <span class="featured-card-badge">{{ ref.document_type?.label ?? ref.document_type?.name ?? 'Document' }}</span>
            </div>
            <div class="featured-card-body">
              <h3 class="featured-card-title">{{ ref.title }}</h3>
              <p v-if="ref.authors?.length" class="featured-card-author">
                {{ ref.authors.map(a => a.full_name).join(', ') }}
              </p>
              <div class="featured-card-meta">
                <span v-if="ref.publication_year">
                  <i class="pi pi-calendar" /> {{ ref.publication_year }}
                </span>
                <span v-if="ref.download_count !== undefined">
                  <i class="pi pi-download" /> {{ ref.download_count }}
                </span>
              </div>
              <span
                v-if="authStore.isAuthenticated && authStore.user?.status === 'active' && ref.file_path"
                class="featured-card-read"
                @click.stop="go(`/user/references/${ref.id}/read`)"
              >
                Lire en ligne →
              </span>
            </div>
          </router-link>
        </div>
      </section>

      <!-- ─── Catalog ─── -->
      <section id="catalog-section" class="section">
        <div class="section-header">
          <h2 class="section-title">
            Toutes les références
            <Transition name="count-pop">
              <span v-if="category !== 'Tout'" :key="category" class="count-badge">{{ animatingCount }}</span>
            </Transition>
          </h2>
          <button @click="go('/catalogue')" class="section-action">Voir tout →</button>
        </div>
        <hr class="gold-rule-left" />

        <div class="catalog-count">
          <span class="count-num">{{ filtered.length }}</span>
          référence{{ filtered.length !== 1 ? 's' : '' }}
        </div>

        <div v-if="filtered.length === 0" class="empty-state">
          <i class="pi pi-book-open" />
          <p>Aucune référence trouvée.</p>
        </div>

        <TransitionGroup v-else name="card" tag="div" class="catalog-grid">
          <router-link
            v-for="ref in filtered"
            :key="ref.id"
            :to="`/references/${ref.id}`"
            class="catalog-card"
          >
            <div class="catalog-card-img">
              <img v-if="ref.cover_url" :src="ref.cover_url" :alt="ref.title" class="catalog-card-cover" />
              <div v-else class="catalog-card-placeholder">
                <i class="pi pi-book" />
              </div>
            </div>
            <div class="catalog-card-body">
              <span class="catalog-card-type">{{ ref.category?.name || ref.document_type?.label || '-' }}</span>
              <h3 class="catalog-card-title">{{ ref.title }}</h3>
              <p class="catalog-card-author">{{ ref.authors?.length ? ref.authors[0].full_name : '' }}</p>
              <div class="catalog-card-stats">
                <span><i class="pi pi-eye" /> {{ ref.view_count || 0 }}</span>
                <span><i class="pi pi-download" /> {{ ref.download_count || 0 }}</span>
              </div>
              <span
                v-if="authStore.isAuthenticated && authStore.user?.status === 'active' && ref.file_path"
                class="catalog-card-read"
                @click.stop="go(`/user/references/${ref.id}/read`)"
              >
                Lire →
              </span>
            </div>
          </router-link>
        </TransitionGroup>
      </section>

      <!-- ─── CTA ─── -->
      <section class="cta-section">
        <div class="cta-card">
          <div class="cta-icon">
            <i class="pi pi-book" />
          </div>
          <h3 class="cta-title">Accédez à l'intégralité des ressources</h3>
          <p class="cta-desc">
            Créez un compte gratuit pour lire en ligne, télécharger et soumettre
            vos propres références documentaires.
          </p>
          <button @click="go('/register')" class="cta-btn">
            Créer un compte gratuit
          </button>
        </div>
      </section>
    </template>
  </div>
</template>

<style scoped>
.home {
  background: var(--bg);
  min-height: 100vh;
}

/* ─── Hero ─── */
.hero {
  position: relative;
  overflow: hidden;
  background: linear-gradient(150deg, #0F2419 0%, #1A3A32 45%, #3D2B1F 100%);
}

.hero-video {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  pointer-events: none;
  z-index: 1;
}
.video-fade-enter-active,
.video-fade-leave-active {
  transition: opacity 1.5s ease;
}
.video-fade-enter-from { opacity: 0; }
.video-fade-leave-to { opacity: 0; }
.hero-pattern {
  position: absolute;
  inset: 0;
  z-index: 3;
  opacity: 0.04;
  background-image:
    repeating-linear-gradient(0deg, transparent, transparent 40px, rgba(200,164,92,0.3) 40px, rgba(200,164,92,0.3) 41px),
    repeating-linear-gradient(90deg, transparent, transparent 40px, rgba(200,164,92,0.3) 40px, rgba(200,164,92,0.3) 41px);
  pointer-events: none;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  z-index: 3;
  background:
    radial-gradient(ellipse at 20% 50%, rgba(200,164,92,0.08) 0%, transparent 60%),
    radial-gradient(ellipse at 80% 50%, rgba(26,58,50,0.4) 0%, transparent 60%);
  pointer-events: none;
}

.hero-content {
  position: relative;
  z-index: 4;
  max-width: 1024px;
  margin: 0 auto;
  padding: 4rem 1.5rem;
  text-align: center;
}
@media (min-width: 640px) {
  .hero-content { padding: 6rem 1.5rem; }
}

.hero-seal {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(200, 164, 92, 0.12);
  border: 1px solid rgba(200, 164, 92, 0.25);
  border-radius: 999px;
  padding: 0.375rem 1rem;
  margin-bottom: 1.5rem;
}

.hero-seal-dot {
  width: 7px;
  height: 7px;
  background: var(--gold-light);
  border-radius: 50%;
  box-shadow: 0 0 6px rgba(200,164,92,0.5);
}

.hero-seal-text {
  color: var(--gold-light);
  font-size: 0.75rem;
  font-weight: 500;
  opacity: 0.9;
}

.hero-title {
  color: white;
  margin-bottom: 1.25rem;
  line-height: 1.1;
  font-size: clamp(2rem, 6vw, 4rem);
}

.hero-title-accent {
  color: var(--gold-light);
}

.hero-desc {
  color: rgba(255, 255, 255, 0.6);
  margin-bottom: 2rem;
  max-width: 560px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
  font-size: 0.875rem;
  padding: 0 1rem;
}
@media (min-width: 640px) {
  .hero-desc { font-size: 1rem; }
}

.hero-search {
  display: flex;
  align-items: center;
  background: var(--bg-elevated);
  border-radius: var(--radius-xl);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  max-width: 560px;
  margin: 0 auto;
  border: 1px solid rgba(200, 164, 92, 0.2);
}

.search-icon {
  font-size: 1rem;
  color: var(--gold-dark);
  flex-shrink: 0;
  margin-left: 1.25rem;
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
.hero-search-input::placeholder {
  color: var(--muted-foreground);
  opacity: 0.6;
  font-style: italic;
}
@media (min-width: 640px) {
  .hero-search-input { padding: 1rem 0.75rem; font-size: 1rem; }
}

.hero-search-btn {
  padding: 0.75rem 1.25rem;
  margin: 0.25rem;
  border-radius: var(--radius-lg);
  border: none;
  background: var(--primary);
  color: white;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.2s;
}
.hero-search-btn:hover {
  background: var(--primary-dark);
  box-shadow: 0 0 0 3px rgba(200,164,92,0.2);
}
@media (min-width: 640px) {
  .hero-search-btn { padding: 0.875rem 1.5rem; }
}

.hero-numbers {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2rem;
  margin-top: 2.5rem;
}
@media (min-width: 640px) {
  .hero-numbers { gap: 4rem; }
}

.hero-number-item {
  text-align: center;
}

.hero-number-value {
  color: var(--gold-light);
  font-weight: 700;
  font-family: var(--font-serif);
  font-size: clamp(1.5rem, 4vw, 2rem);
  display: block;
}

.hero-number-label {
  color: rgba(255, 255, 255, 0.45);
  font-size: 0.75rem;
  margin-top: 0.125rem;
}
@media (min-width: 640px) {
  .hero-number-label { font-size: 0.875rem; }
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
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.category-bar-inner::-webkit-scrollbar { display: none; }

.pill {
  padding: 0.5rem 1rem;
  border-radius: 999px;
  font-size: 0.8125rem;
  font-weight: 500;
  white-space: nowrap;
  border: 1px solid var(--border);
  background: var(--bg-card);
  color: var(--muted-foreground);
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.pill:hover { color: var(--foreground); border-color: var(--gold); }

.pill-active {
  background: var(--primary);
  color: var(--gold-light);
  border-color: var(--primary);
  box-shadow: 0 2px 8px rgba(26, 58, 50, 0.3);
}

.pill-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  background: var(--gold);
  color: var(--primary-dark);
  white-space: nowrap;
  flex-shrink: 0;
}

/* ─── Sections ─── */
.section {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem;
}

.section-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 0;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
}

.section-action {
  background: none;
  border: none;
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--gold-dark);
  cursor: pointer;
  transition: color 0.2s;
  padding: 0;
  font-family: var(--font-sans);
}
.section-action:hover { color: var(--gold); }

/* ─── Stats ─── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  margin-top: 1rem;
}
@media (min-width: 640px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 768px) { .stats-grid { grid-template-columns: repeat(5, 1fr); } }

.stat-card {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border);
  padding: 1.25rem;
  text-align: center;
  transition: all 0.25s ease;
}
.stat-card:hover {
  border-color: var(--border-gold);
  box-shadow: 0 4px 16px rgba(200,164,92,0.1);
  transform: translateY(-2px);
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 0.75rem;
}
.stat-icon i { font-size: 1.1rem; }

.stat-value {
  display: block;
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--foreground);
  font-family: var(--font-serif);
}
.stat-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--muted-foreground);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-top: 0.25rem;
  display: block;
}

/* ─── Featured ─── */
.featured-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.75rem;
  margin-top: 0.5rem;
}
@media (min-width: 640px) { .featured-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .featured-grid { grid-template-columns: repeat(3, 1fr); } }

.featured-card {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  border: 1.5px solid var(--border-gold);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.25s ease;
  display: flex;
  flex-direction: column;
}
.featured-card:hover {
  box-shadow: 0 8px 32px rgba(200, 164, 92, 0.15);
  transform: translateY(-3px);
  border-color: var(--gold);
}

.featured-card-img {
  aspect-ratio: 16/9;
  background: var(--muted);
  overflow: hidden;
  position: relative;
}

.featured-card-cover {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}
.featured-card:hover .featured-card-cover { transform: scale(1.05); }

.featured-card-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--muted);
}
.featured-card-placeholder i {
  font-size: 2rem;
  color: var(--gold-dark);
  opacity: 0.4;
}

.featured-card-badge {
  position: absolute;
  top: 0.5rem;
  left: 0.5rem;
  font-size: 0.625rem;
  font-weight: 600;
  color: var(--primary-dark);
  background: var(--gold-light);
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.featured-card-body { padding: 1rem; flex: 1; }

.featured-card-title {
  font-size: 0.9375rem;
  font-weight: 600;
  color: var(--foreground);
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
.featured-card-meta i { font-size: 0.65rem; margin-right: 0.2rem; }

/* ─── Catalog grid ─── */
.catalog-count {
  font-size: 0.85rem;
  color: var(--muted-foreground);
  margin: 0.75rem 0;
}
.count-num { font-weight: 700; color: var(--foreground); }

.catalog-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
  position: relative;
}
@media (min-width: 640px) { .catalog-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 768px) { .catalog-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 1024px) { .catalog-grid { grid-template-columns: repeat(5, 1fr); } }
@media (min-width: 1280px) { .catalog-grid { grid-template-columns: repeat(6, 1fr); } }

.catalog-card {
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.25s ease;
}
.catalog-card:hover {
  box-shadow: 0 8px 24px rgba(44, 36, 32, 0.08);
  transform: translateY(-3px);
  border-color: var(--border-gold);
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
.catalog-card:hover .catalog-card-cover { transform: scale(1.05); }

.catalog-card-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--muted);
}
.catalog-card-placeholder i {
  font-size: 1.5rem;
  color: var(--gold-dark);
  opacity: 0.3;
}

.catalog-card-body { padding: 0.75rem; }

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
.catalog-card-stats i { font-size: 0.6rem; margin-right: 0.15rem; }

.catalog-card-read {
  display: inline-block;
  margin-top: 0.4rem;
  font-size: 0.7rem;
  font-weight: 600;
  color: var(--primary);
  cursor: pointer;
  transition: opacity 0.15s;
}
.catalog-card-read:hover {
  opacity: 0.7;
  text-decoration: underline;
}

.featured-card-read {
  display: inline-block;
  margin-top: 0.4rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--primary);
  cursor: pointer;
  transition: opacity 0.15s;
}
.featured-card-read:hover {
  opacity: 0.7;
  text-decoration: underline;
}

/* ─── CTA ─── */
.cta-section {
  max-width: 1280px;
  margin: 0 auto;
  padding: 1.5rem 1rem 3rem;
}

.cta-card {
  border-radius: var(--radius-xl);
  padding: 2.5rem 1.5rem;
  text-align: center;
  background: linear-gradient(135deg, #0F2419 0%, #1A3A32 50%, #3D2B1F 100%);
  border: 1px solid rgba(200, 164, 92, 0.15);
  position: relative;
  overflow: hidden;
}
.cta-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--gold), transparent);
}
@media (min-width: 640px) {
  .cta-card { padding: 3.5rem 2rem; }
}

.cta-icon { margin-bottom: 1rem; }
.cta-icon i {
  font-size: 2rem;
  color: var(--gold-dark);
  opacity: 0.6;
}

.cta-title {
  color: var(--gold-light);
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}
@media (min-width: 640px) {
  .cta-title { font-size: 1.5rem; }
}

.cta-desc {
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.875rem;
  max-width: 480px;
  margin: 0 auto 1.5rem;
  line-height: 1.6;
}

.cta-btn {
  padding: 0.8rem 2rem;
  background: var(--gold);
  color: var(--primary-dark);
  border: none;
  border-radius: var(--radius-lg);
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.cta-btn:hover {
  background: var(--gold-light);
  box-shadow: 0 4px 16px rgba(200, 164, 92, 0.3);
  transform: translateY(-1px);
}

/* ─── Count badge ─── */
.count-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 28px;
  padding: 0 0.5rem;
  margin-left: 0.5rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  font-family: var(--font-sans);
  background: var(--gold);
  color: var(--primary-dark);
  vertical-align: middle;
}

/* ─── Animations ─── */
.card-enter-active { transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
.card-leave-active { transition: all 0.25s ease-in; position: absolute; }
.card-enter-from { opacity: 0; transform: translateY(24px) scale(0.95); }
.card-leave-to { opacity: 0; transform: translateY(-12px) scale(0.95); }
.card-move { transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }

.count-pop-enter-active { animation: popIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
.count-pop-leave-active { animation: popIn 0.2s ease-in reverse; }
@keyframes popIn {
  0% { transform: scale(0); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

.pill-fade-enter-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.pill-fade-leave-active { transition: all 0.2s ease; }
.pill-fade-enter-from { opacity: 0; transform: scale(0.8) translateX(-10px); }
.pill-fade-leave-to { opacity: 0; transform: scale(0.8); }
</style>
