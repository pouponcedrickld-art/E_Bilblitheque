<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/services/http'
import type { Reference } from '@/types'

const route = useRoute()
const router = useRouter()

const reference = ref<Reference | null>(null)
const loading = ref(true)
const error = ref('')

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

function readUrl() {
  if (!reference.value) return ''
  return reference.value.file_path || `/api/references/${reference.value.id}/read`
}

onMounted(fetchReference)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <button class="back-btn" @click="router.push('/user/dashboard')">
        <i class="pi pi-arrow-left" /> Retour
      </button>
      <h1 v-if="reference">{{ reference.title }}</h1>
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
