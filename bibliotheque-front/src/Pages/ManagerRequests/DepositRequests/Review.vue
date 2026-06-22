<script setup lang="ts">
// Examen d'une demande de dépôt par le gestionnaire
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/services/http'
import { formatDate } from '@/Utils/formatters'
import ReviewDecisionForm from '@/Components/ManagerRequests/ReviewDecisionForm.vue'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'
import Button from 'primevue/button'

// Détail d'une demande de dépôt
interface DepositRequestDetail {
  id: number
  title: string
  description: string | null
  status: string
  file_path: string | null
  file_url: string | null
  applicant: { id: number; first_name: string; last_name: string; full_name: string } | null
  created_at: string
  updated_at: string
}

const route = useRoute()
const router = useRouter()

// Données de la demande
const request = ref<DepositRequestDetail | null>(null)
const loading = ref(true)
const error = ref('')

// Charge les détails de la demande
async function fetchRequest() {
  loading.value = true
  error.value = ''
  try {
    const res = await http.get(`/deposit-requests/${route.params.id}`)
    request.value = res.data?.data ?? res.data
  } catch {
    error.value = 'Impossible de charger la demande.'
  } finally {
    loading.value = false
  }
}

// Retourne à la liste après décision
function onDone() {
  router.push('/manager/requests')
}

// Charge la demande au montage
onMounted(fetchRequest)
</script>

<template>
  <div class="review-page">
    <div class="page-header">
      <Button icon="pi pi-arrow-left" text severity="secondary" label="Retour" @click="router.push('/manager/requests')" />
      <h1>Examiner la demande</h1>
    </div>

    <div v-if="loading" class="loading">Chargement...</div>
    <div v-else-if="error" class="error">{{ error }}</div>
    <div v-else-if="request" class="content">
      <div class="detail-card">
        <div class="detail-row">
          <span class="detail-label">Titre</span>
          <span class="detail-value">{{ request.title }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Statut</span>
          <StatusBadge :status="request.status" />
        </div>
        <div class="detail-row">
          <span class="detail-label">Demandeur</span>
          <span class="detail-value">{{ request.applicant?.full_name ?? 'N/A' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Date</span>
          <span class="detail-value">{{ formatDate(request.created_at) }}</span>
        </div>
        <div v-if="request.description" class="detail-row description">
          <span class="detail-label">Description</span>
          <p class="detail-value">{{ request.description }}</p>
        </div>
        <div v-if="request.file_url || request.file_path" class="detail-row">
          <span class="detail-label">Fichier</span>
          <a :href="(request.file_url ?? request.file_path) || undefined" target="_blank" class="file-link">
            <i class="pi pi-download" /> Télécharger le fichier
          </a>
        </div>
      </div>

      <ReviewDecisionForm :deposit-request-id="request.id" :show="true" :status="request.status" @done="onDone" />
    </div>
  </div>
</template>

<style scoped>
.review-page {
  max-width: 800px;
}

.page-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.page-header h1 {
  font-size: 1.4rem;
  font-weight: 700;
  margin: 0;
}

.loading,
.error {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

.error {
  color: #c62828;
}

.content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detail-card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-row {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-row.description {
  gap: 0.5rem;
}

.detail-row p {
  margin: 0;
  line-height: 1.6;
  color: var(--text-secondary);
}

.detail-label {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--text-secondary);
}

.detail-value {
  font-size: 0.95rem;
  color: var(--text-primary);
}

.file-link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: var(--primary);
  font-weight: 500;
  text-decoration: none;
}

.file-link:hover {
  text-decoration: underline;
}
</style>
