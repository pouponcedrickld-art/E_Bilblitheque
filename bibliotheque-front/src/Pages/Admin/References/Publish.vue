<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import Button from 'primevue/button'
import Message from 'primevue/message'
import Card from 'primevue/card'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'

const route = useRoute()
const router = useRouter()
const toastStore = useToastStore()

const request = ref<any>(null)
const loading = ref(true)
const submitting = ref(false)
const error = ref('')

async function load() {
  loading.value = true
  try {
    const id = route.params.id as string
    const res = await http.get(`/deposit-requests/${id}`)
    request.value = res.data?.data ?? res.data
  } catch {
    error.value = 'Impossible de charger la demande de dépôt.'
  } finally {
    loading.value = false
  }
}

async function publishRequest() {
  submitting.value = true
  error.value = ''
  try {
    const id = route.params.id as string
    await http.post(`/deposit-requests/${id}/publish`)
    toastStore.success('Référence publiée avec succès.')
    router.push('/admin/references')
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erreur lors de la publication.'
  } finally {
    submitting.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Publier une référence</h1>
      <Button icon="pi pi-arrow-left" label="Retour" severity="secondary" text @click="router.push('/admin/references')" />
    </div>

    <div v-if="loading" class="loading">Chargement...</div>

    <Message v-if="error" severity="error" :closable="false" class="mb-3">{{ error }}</Message>

    <Card v-if="request" class="mb-3">
      <template #title>{{ request.title }}</template>
      <template #content>
        <div class="info-grid">
          <div class="info-item">
            <span class="info-label">Statut</span>
            <StatusBadge :status="request.status" />
          </div>
          <div class="info-item">
            <span class="info-label">Demandeur</span>
            <span>{{ request.applicant?.full_name ?? request.applicant?.name ?? '-' }}</span>
          </div>
          <div class="info-item">
            <span class="info-label">Date de soumission</span>
            <span>{{ new Date(request.created_at).toLocaleDateString() }}</span>
          </div>
          <div v-if="request.description" class="info-item full">
            <span class="info-label">Description</span>
            <span>{{ request.description }}</span>
          </div>
        </div>
      </template>
    </Card>

    <div v-if="request" class="actions">
      <Button
        icon="pi pi-check"
        label="Confirmer la publication"
        severity="success"
        :loading="submitting"
        @click="publishRequest"
      />
    </div>
  </div>
</template>

<style scoped>
.page { max-width: 700px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.loading { text-align: center; padding: 2rem; color: var(--text-secondary); }
.mb-3 { margin-bottom: 1rem; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.info-item { display: flex; flex-direction: column; gap: 0.25rem; }
.info-item.full { grid-column: 1 / -1; }
.info-label { font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.03em; }
.actions { margin-top: 1rem; }
</style>
