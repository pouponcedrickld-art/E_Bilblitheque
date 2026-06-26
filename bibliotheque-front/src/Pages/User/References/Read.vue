<script setup lang="ts">
// Importations Vue, routeur, services et types
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/services/http'
import { useAuthStore } from '@/stores/auth'
import type { Reference } from '@/types'

// Route actuelle et routeur
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

// Référence chargée depuis l'API
const reference = ref<Reference | null>(null)
const loading = ref(true)
const error = ref('')

// Telechargement autorisé si admin, propriétaire ou allow_download est vrai
const canDownload = computed(() => {
  if (!reference.value) return false
  return authStore.isAdmin
    || authStore.user?.id === reference.value.uploaded_by
    || reference.value.allow_download === true
})

// Charge les détails de la référence
async function fetchReference() {
  loading.value = true
  error.value = ''

  try {
    const id = Number(route.params.id)
    const res = await http.get(`/references/${id}`)
    reference.value = res.data?.data ?? res.data
  } catch {
    error.value = 'Impossible de charger le document.'
  } finally {
    loading.value = false
  }
}

// Construit l'URL de lecture du document
function readUrl() {
  if (!reference.value) return ''
  return reference.value.file_path || `/api/references/${reference.value.id}/read`
}

function download() {
  if (!reference.value) return
  router.push(`/user/references/${reference.value.id}/download`)
}

// Redirige si le compte n'est pas actif
onMounted(() => {
  if (authStore.user?.status !== 'active') {
    router.push('/forbidden')
    return
  }
  fetchReference()
})
</script>

<template>
  <div class="page">
    <div class="page-header">
      <button class="back-btn" @click="router.push('/user/dashboard')">
        <i class="pi pi-arrow-left" /> Retour
      </button>
      <h1 v-if="reference">{{ reference.title }}</h1>
      <div v-if="reference" class="header-actions">
        <button v-if="canDownload" class="download-btn" @click="download">
          <i class="pi pi-download" /> Télécharger la fiche
        </button>
        <span v-else class="download-blocked">
          <i class="pi pi-lock" /> Téléchargement bloqué
        </span>
      </div>
    </div>

    <div v-if="loading" class="loading">Chargement du document...</div>
    <div v-else-if="error" class="alert alert-error">{{ error }}</div>

    <iframe
      v-else-if="reference"
      :src="readUrl()"
      class="reader"
      title="Lecteur de document"
    />
  </div>
</template>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  height: calc(100vh - var(--navbar-height) - 3rem);
}

.page-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-shrink: 0;
}

.page-header h1 {
  font-size: 1.15rem;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
}

.header-actions {
  flex-shrink: 0;
}

.download-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 1rem;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 0.85rem;
  font-weight: 600;
  transition: opacity 0.15s;
  white-space: nowrap;
}

.download-btn:hover {
  opacity: 0.85;
}

.download-blocked {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: var(--text-secondary);
  white-space: nowrap;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: 0.375rem;
  background: #fff;
  cursor: pointer;
  font-size: 0.85rem;
  color: var(--text-secondary);
  transition: all 0.15s;
  white-space: nowrap;
}

.back-btn:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.loading {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
}

.alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #b91c1c;
}

.reader {
  flex: 1;
  width: 100%;
  border: 1px solid var(--border);
  border-radius: 0.375rem;
  background: #fff;
}
</style>
