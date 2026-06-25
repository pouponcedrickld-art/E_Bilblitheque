<script setup lang="ts">
// Importations Vue, routeur, services et types
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/services/http'
import type { Reference } from '@/types'

// Route actuelle et routeur
const route = useRoute()
const router = useRouter()

const languageLabels: Record<string, string> = {
  fr: 'Français', en: 'Anglais', ar: 'Arabe', es: 'Espagnol',
  de: 'Allemand', pt: 'Portugais', it: 'Italien', ru: 'Russe',
  zh: 'Chinois', ja: 'Japonais', autre: 'Autre',
}

// Référence chargée depuis l'API
const reference = ref<Reference | null>(null)
const loading = ref(true)
const error = ref('')

// Charge les détails d'une référence
async function fetchReference() {
  loading.value = true
  error.value = ''
  try {
    const res = await http.get(`/references/${route.params.id}`)
    reference.value = res.data?.data ?? res.data
  } catch {
    error.value = 'Référence introuvable.'
  } finally {
    loading.value = false
  }
}

// Charge la référence au montage du composant
onMounted(fetchReference)
</script>

<template>
  <div class="detail-page">
    <button class="back-btn" @click="router.push('/catalogue')">
      ← Retour au catalogue
    </button>

    <div v-if="loading" class="loading">Chargement...</div>

    <div v-else-if="error" class="error">{{ error }}</div>

    <div v-else-if="reference" class="detail-card">
      <div v-if="reference.cover_url" class="detail-cover">
        <img :src="reference.cover_url" :alt="reference.title" />
      </div>
      <div v-else class="detail-cover-placeholder">
        <i class="pi pi-book" style="font-size: 2rem; color: var(--primary)"></i>
        <span class="cover-placeholder-text">Couverture non disponible</span>
      </div>

      <div class="detail-header">
        <span class="badge">{{ reference.document_type?.label ?? reference.document_type?.name ?? '-' }}</span>
        <span class="badge" :class="reference.status">{{ reference.status }}</span>
      </div>

      <h1 class="detail-title">{{ reference.title }}</h1>
      <p v-if="reference.subtitle" class="detail-subtitle">{{ reference.subtitle }}</p>

      <div class="detail-section">
        <strong>Auteurs :</strong>
        <p v-if="reference.authors?.length">
          {{ reference.authors.map(a => a.full_name).join(', ') }}
        </p>
        <p v-else class="muted">Non renseigné</p>
      </div>

      <div class="detail-section">
        <strong>Résumé :</strong>
        <p>{{ reference.abstract || 'Aucun résumé.' }}</p>
      </div>

      <div class="detail-grid">
        <div v-if="reference.isbn" class="detail-item">
          <span class="label">ISBN</span>
          <span>{{ reference.isbn }}</span>
        </div>
        <div v-if="reference.publication_year" class="detail-item">
          <span class="label">Année</span>
          <span>{{ reference.publication_year }}</span>
        </div>
        <div v-if="reference.language" class="detail-item">
          <span class="label">Langue</span>
          <span>{{ languageLabels[reference.language] ?? reference.language }}</span>
        </div>
        <div v-if="reference.pages" class="detail-item">
          <span class="label">Pages</span>
          <span>{{ reference.pages }}</span>
        </div>
        <div v-if="reference.category" class="detail-item">
          <span class="label">Catégorie</span>
          <span>{{ reference.category.name }}</span>
        </div>
        <div v-if="reference.publisher" class="detail-item">
          <span class="label">Éditeur</span>
          <span>{{ reference.publisher.name }}</span>
        </div>
      </div>

      <div v-if="reference.keywords?.length" class="detail-section">
        <strong>Mots-clés :</strong>
        <div class="keywords">
          <span v-for="kw in reference.keywords" :key="kw.id" class="keyword">{{ kw.name }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.detail-page {
  max-width: 800px;
  margin: 0 auto;
}

.back-btn {
  background: none;
  border: none;
  color: var(--primary);
  font-size: 0.9rem;
  cursor: pointer;
  padding: 0.5rem 0;
  margin-bottom: 1rem;
}

.back-btn:hover {
  text-decoration: underline;
}

.loading, .error {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.error {
  color: #b91c1c;
}

.detail-card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 2rem;
}

.detail-cover {
  width: 200px;
  height: 280px;
  border-radius: var(--radius-sm);
  overflow: hidden;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.detail-cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.detail-cover-placeholder {
  width: 200px;
  height: 280px;
  border-radius: var(--radius-sm);
  margin-bottom: 1.5rem;
  background: var(--muted);
  border: 1px dashed var(--border);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.cover-placeholder-text {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.detail-header {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  background: rgba(27, 67, 50, 0.08);
  color: var(--primary);
}

.badge.published { background: #ecfdf5; color: #059669; }
.badge.draft { background: #fef3c7; color: #d97706; }
.badge.archived { background: var(--muted); color: var(--muted-foreground); }

.detail-title {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.detail-subtitle {
  font-size: 1rem;
  color: var(--text-secondary);
  margin-bottom: 1.5rem;
}

.detail-section {
  margin-bottom: 1.25rem;
}

.detail-section strong {
  display: block;
  font-size: 0.85rem;
  color: var(--text-secondary);
  margin-bottom: 0.25rem;
}

.muted { color: var(--text-secondary); }

.detail-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 1rem;
  margin-bottom: 1.25rem;
  padding: 1rem;
  background: var(--bg);
  border-radius: var(--radius-sm);
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  font-weight: 600;
}

.keywords {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  margin-top: 0.35rem;
}

.keyword {
  padding: 0.2rem 0.6rem;
  background: rgba(27, 67, 50, 0.08);
  color: var(--primary);
  border-radius: 999px;
  font-size: 0.75rem;
}
</style>
