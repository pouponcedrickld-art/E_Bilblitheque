<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import http from '@/services/http'
import DepositRequestForm from '@/Components/User/DepositRequestForm.vue'
import Message from 'primevue/message'
import Button from 'primevue/button'

const router = useRouter()
const authStore = useAuthStore()
const loading = ref(false)
const error = ref('')
const submitted = ref(false)
const createdTitle = ref('')

const canCreate = computed(() => authStore.user?.status === 'active')

async function handleSubmit(formData: FormData) {
  loading.value = true
  error.value = ''
  submitted.value = false

  try {
    const res = await http.post('/deposit-requests', formData)
    createdTitle.value = res.data?.title ?? res.data?.data?.title ?? ''
    submitted.value = true
    setTimeout(() => {
      router.push('/user/deposits')
    }, 4000)
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
      <div v-if="submitted" class="seal-success">
        <div class="seal-icon">
          <div class="seal-circle">
            <svg viewBox="0 0 100 100" class="seal-svg">
              <circle cx="50" cy="50" r="45" fill="none" stroke="var(--gold)" stroke-width="3" stroke-dasharray="283" class="seal-ring" />
              <circle cx="50" cy="50" r="38" fill="none" stroke="var(--gold-dark)" stroke-width="1.5" stroke-dasharray="239" class="seal-ring-inner" />
              <polygon points="50,22 58,42 78,44 63,57 67,78 50,66 33,78 37,57 22,44 42,42" fill="var(--gold)" opacity="0.15" />
              <text x="50" y="55" text-anchor="middle" font-size="20" fill="var(--gold)" font-family="Playfair Display, serif">✓</text>
            </svg>
          </div>
          <div class="seal-glow"></div>
        </div>
        <h2 class="seal-heading">Dépôt soumis avec succès</h2>
        <p class="seal-body">Votre demande <strong>"{{ createdTitle }}"</strong> a été enregistrée et sera examinée par un responsable.</p>
        <div class="seal-footer">
          <p class="seal-hint">Vous serez redirigé vers vos dépôts dans quelques instants...</p>
          <Button label="Voir mes dépôts" icon="pi pi-arrow-right" icon-pos="right" @click="goToDeposits" class="seal-btn" />
        </div>
      </div>
    </Transition>

    <template v-if="!submitted">
      <Transition name="fade">
        <div v-if="error" class="alert alert-error">{{ error }}</div>
      </Transition>

      <div v-if="!canCreate" class="alert alert-info">
        Votre compte est en attente de validation par un administrateur. Vous ne pouvez pas faire de demande de dépôt pour le moment.
      </div>

      <div v-else class="form-card">
        <DepositRequestForm @submit="handleSubmit" />
      </div>

      <Transition name="fade">
        <div v-if="loading" class="loading-bar">
          <span class="spinner"></span>
          Envoi de votre demande en cours...
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
  color: var(--muted-foreground);
  font-size: 0.9rem;
  margin-top: 0.25rem;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: var(--radius-lg);
  font-size: 0.85rem;
  margin-bottom: 1rem;
}
.alert-error {
  background: rgba(107, 45, 62, 0.08);
  border: 1px solid rgba(107, 45, 62, 0.25);
  color: var(--burgundy);
}
.alert-info {
  background: rgba(26, 58, 50, 0.06);
  border: 1px solid rgba(26, 58, 50, 0.2);
  color: var(--primary);
}

.form-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
}

.loading-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem;
  color: var(--muted-foreground);
  font-size: 0.9rem;
}
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid var(--border);
  border-top-color: var(--gold);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.seal-success {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  background: var(--bg-card);
  border: 1.5px solid var(--border-gold);
  border-radius: var(--radius-xl);
  padding: 2.5rem 2rem;
  gap: 0.75rem;
}
.seal-icon {
  position: relative;
  margin-bottom: 0.5rem;
}
.seal-circle {
  width: 80px;
  height: 80px;
  position: relative;
}
.seal-svg {
  width: 100%;
  height: 100%;
}
.seal-ring {
  animation: draw-circle 1.2s ease-out forwards;
}
.seal-ring-inner {
  animation: draw-circle-inner 1s ease-out 0.3s forwards;
  opacity: 0;
  animation-fill-mode: forwards;
}
@keyframes draw-circle {
  from { stroke-dashoffset: 283; }
  to { stroke-dashoffset: 0; }
}
@keyframes draw-circle-inner {
  from { stroke-dashoffset: 239; opacity: 0; }
  to { stroke-dashoffset: 0; opacity: 1; }
}
.seal-glow {
  position: absolute;
  inset: -8px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(200,164,92,0.15) 0%, transparent 70%);
  animation: pulse-glow 2s ease-in-out infinite;
}
@keyframes pulse-glow {
  50% { transform: scale(1.1); opacity: 0.6; }
}
.seal-heading {
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--foreground);
  margin: 0;
}
.seal-body {
  font-size: 0.9rem;
  color: var(--muted-foreground);
  max-width: 420px;
  line-height: 1.6;
}
.seal-footer {
  margin-top: 0.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}
.seal-hint {
  font-size: 0.8rem;
  color: var(--muted-foreground);
  opacity: 0.7;
}
.seal-btn {
  background: var(--primary) !important;
  border: none !important;
  border-radius: var(--radius-lg) !important;
  padding: 0.6rem 1.25rem !important;
  font-weight: 600 !important;
}
.seal-btn:hover {
  background: var(--primary-dark) !important;
  box-shadow: 0 4px 16px rgba(26,58,50,0.3) !important;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
