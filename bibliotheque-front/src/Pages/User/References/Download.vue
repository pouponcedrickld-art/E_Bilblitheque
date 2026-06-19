<script setup lang="ts">
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

onMounted(() => {
  const id = route.params.id
  const url = `/api/references/${id}/download`

  const anchor = document.createElement('a')
  anchor.href = url
  anchor.download = ''
  anchor.style.display = 'none'
  document.body.appendChild(anchor)
  anchor.click()
  document.body.removeChild(anchor)

  router.replace('/user/dashboard')
})
</script>

<template>
  <div class="page">
    <p>Téléchargement en cours...</p>
  </div>
</template>

<style scoped>
.page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  color: var(--text-secondary);
  font-size: 0.95rem;
}
</style>
