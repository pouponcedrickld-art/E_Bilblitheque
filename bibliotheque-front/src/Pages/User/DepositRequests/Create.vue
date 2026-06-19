<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import DepositRequestForm from '@/Components/User/DepositRequestForm.vue'

const router = useRouter()
const loading = ref(false)
const error = ref('')

async function handleSubmit(data: { title: string; description: string; file: File | null }) {
  loading.value = true
  error.value = ''

  try {
    const formData = new FormData()
    formData.append('title', data.title)
    formData.append('description', data.description)
    if (data.file) {
      formData.append('proposed_file', data.file)
    }

    await http.post('/deposit-requests', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    router.push('/user/deposits')
  } catch (err: any) {
    if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else if (err.response?.data?.errors) {
      const msgs: string[] = []
      Object.values(err.response.data.errors).forEach((e: any) => {
        msgs.push(...(Array.isArray(e) ? e : [e]))
      })
      error.value = msgs.join(', ')
    } else {
      error.value = "Erreur lors de l'envoi de la demande."
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1>Nouveau dépôt</h1>
        <p>Soumettez une référence pour dépôt dans la bibliothèque</p>
      </div>
    </div>

    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <div class="form-card">
      <DepositRequestForm @submit="handleSubmit" />
    </div>

    <div v-if="loading" class="loading-overlay">
      <p>Envoi en cours...</p>
    </div>
  </div>
</template>

<style scoped>
.page {
  max-width: 700px;
}

.page-header {
  margin-bottom: 1.5rem;
}

.page-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
}

.page-header p {
  color: var(--text-secondary);
  font-size: 0.9rem;
  margin-top: 0.25rem;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  margin-bottom: 1rem;
}

.alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #b91c1c;
}

.form-card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1.5rem;
}

.loading-overlay {
  text-align: center;
  padding: 1rem;
  color: var(--text-secondary);
  font-size: 0.9rem;
}
</style>
