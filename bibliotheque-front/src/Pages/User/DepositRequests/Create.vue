<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import DepositRequestForm from '@/Components/User/DepositRequestForm.vue'
import Message from 'primevue/message'

const router = useRouter()
const loading = ref(false)
const error = ref('')
const submitted = ref(false)
const createdTitle = ref('')

async function handleSubmit(data: { title: string; description: string; file: File | null }) {
  loading.value = true
  error.value = ''
  submitted.value = false

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

    createdTitle.value = data.title
    submitted.value = true

    setTimeout(() => {
      router.push('/user/deposits')
    }, 2000)
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

function goToDeposits() {
  router.push('/user/deposits')
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

    <Transition name="fade">
      <div v-if="submitted" class="success-card">
        <div class="success-icon">✅</div>
        <h2>Dépôt soumis avec succès !</h2>
        <p>Votre demande <strong>"{{ createdTitle }}"</strong> a été envoyée et sera examinée par un responsable.</p>
        <p class="success-hint">Vous serez redirigé vers la liste de vos dépôts dans un instant...</p>
        <Button label="Voir mes dépôts" icon="pi pi-arrow-right" @click="goToDeposits" class="p-button-outlined" />
      </div>
    </Transition>

    <template v-if="!submitted">
      <Transition name="fade">
        <div v-if="error" class="alert alert-error">{{ error }}</div>
      </Transition>

      <div class="form-card">
        <DepositRequestForm @submit="handleSubmit" />
      </div>

      <Transition name="fade">
        <div v-if="loading" class="loading-bar">
          <i class="pi pi-spin pi-spinner" /> Envoi de votre demande en cours...
        </div>
      </Transition>
    </template>
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
  border-radius: 0.5rem;
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

.success-card {
  background: #f0fdf4;
  border: 2px solid #86efac;
  border-radius: 1rem;
  padding: 2.5rem 2rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.success-icon {
  font-size: 3rem;
}

.success-card h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #166534;
}

.success-card p {
  font-size: 0.9rem;
  color: #166534;
  max-width: 420px;
  line-height: 1.5;
}

.success-hint {
  font-size: 0.8rem;
  opacity: 0.75;
}

.loading-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
