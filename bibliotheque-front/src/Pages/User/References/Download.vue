<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/services/http'

const route = useRoute()
const router = useRouter()
const error = ref('')

onMounted(async () => {
  const id = route.params.id

  try {
    const response = await http.get(`/references/${id}/download`, {
      responseType: 'blob',
    })

    const contentDisposition = response.headers['content-disposition']
    const filename = contentDisposition
      ? contentDisposition.split('filename=')[1]?.replace(/['"]/g, '') || `document-${id}`
      : `document-${id}`

    const url = window.URL.createObjectURL(new Blob([response.data]))
    const anchor = document.createElement('a')
    anchor.href = url
    anchor.setAttribute('download', filename)
    anchor.style.display = 'none'
    document.body.appendChild(anchor)
    anchor.click()
    anchor.remove()
    window.URL.revokeObjectURL(url)

    router.replace('/user/dashboard')
  } catch (err: any) {
    if (err.response?.status === 403) {
      error.value = 'Téléchargement non autorisé. Le propriétaire a bloqué le téléchargement de ce document.'
    } else if (err.response?.status === 404) {
      error.value = 'Fichier non disponible.'
    } else if (err.response?.status === 429) {
      error.value = 'Trop de téléchargements. Veuillez réessayer plus tard.'
    } else {
      error.value = 'Erreur lors du téléchargement.'
    }
  }
})
</script>

<template>
  <div class="page">
    <p v-if="!error">Téléchargement en cours...</p>
    <p v-else class="error-msg">{{ error }}</p>
    <button v-if="error" class="back-btn" @click="router.push('/user/dashboard')">Retour au tableau de bord</button>
  </div>
</template>

<style scoped>
.page {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  gap: 1rem;
  color: var(--text-secondary);
  font-size: 0.95rem;
}
.error-msg {
  color: var(--destructive, #b91c1c);
  text-align: center;
  max-width: 400px;
}
.back-btn {
  background: var(--primary);
  color: #fff;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-lg);
  cursor: pointer;
}
</style>
