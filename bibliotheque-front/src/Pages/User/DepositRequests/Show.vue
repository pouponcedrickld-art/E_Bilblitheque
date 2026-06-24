<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/services/http'
import type { DepositRequest } from '@/types'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import { useConfirm } from 'primevue/useconfirm'
import ConfirmDialog from 'primevue/confirmdialog'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import { formatDate } from '@/Utils/formatters'

const route = useRoute()
const router = useRouter()
const confirm = useConfirm()
const authStore = useAuthStore()
const toastStore = useToastStore()

const request = ref<DepositRequest | null>(null)
const reviews = ref<any[]>([])
const loading = ref(true)
const error = ref('')

const languageLabels: Record<string, string> = { fr: 'Français', en: 'Anglais', autre: 'Autre' }

const canDelete = computed(() => {
  if (!request.value) return false
  if (authStore.user?.role === 'admin') return true
  return request.value.status === 'pending'
})

async function fetchRequest() {
  loading.value = true
  error.value = ''
  try {
    const id = Number(route.params.id)
    const res = await http.get(`/deposit-requests/${id}`)
    const data = res.data?.data ?? res.data
    request.value = data
    reviews.value = data.reviews ?? []
  } catch {
    error.value = 'Impossible de charger la demande.'
  } finally {
    loading.value = false
  }
}

function downloadFile() {
  if (!request.value?.proposed_file_url) return
  window.open(request.value.proposed_file_url, '_blank')
}

function goBack() {
  router.push('/user/deposits')
}

function confirmDelete() {
  if (!request.value) return
  confirm.require({
    message: 'Voulez-vous vraiment supprimer ce dépôt ? Cette action est irréversible.',
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    accept: async () => {
      try {
        await http.delete(`/deposit-requests/${request.value!.id}`)
        toastStore.success('Dépôt supprimé avec succès.')
        router.push('/user/deposits')
      } catch {
        toastStore.error('Erreur lors de la suppression.')
      }
    },
  })
}

onMounted(fetchRequest)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <Button icon="pi pi-arrow-left" class="p-button-rounded p-button-text" @click="goBack" />
      <div><h1>Détail du dépôt</h1></div>
      <Button v-if="canDelete" label="Supprimer" icon="pi pi-trash" severity="danger" class="p-button-sm" @click="confirmDelete" />
    </div>
    <div v-if="loading" class="loading">Chargement...</div>
    <div v-else-if="error" class="alert alert-error">{{ error }}</div>

    <template v-else-if="request">
      <div class="detail-card">
        <div class="detail-row">
          <span class="detail-label">Titre</span>
          <span class="detail-value">{{ request.title }}</span>
        </div>
        <div v-if="request.subtitle" class="detail-row">
          <span class="detail-label">Sous-titre</span>
          <span class="detail-value">{{ request.subtitle }}</span>
        </div>
        <div v-if="request.abstract" class="detail-row">
          <span class="detail-label">Résumé</span>
          <span class="detail-value">{{ request.abstract }}</span>
        </div>
        <div v-if="request.description" class="detail-row">
          <span class="detail-label">Description</span>
          <span class="detail-value">{{ request.description }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Statut</span>
          <StatusBadge :status="request.status" />
        </div>
        <div class="detail-row">
          <span class="detail-label">Type</span>
          <Tag :value="request.document_type?.label ?? request.document_type?.name ?? '-'" />
        </div>
        <div class="detail-row">
          <span class="detail-label">Langue</span>
          <span class="detail-value">{{ languageLabels[request.language ?? ''] || request.language || '-' }}</span>
        </div>
        <div v-if="request.category" class="detail-row">
          <span class="detail-label">Catégorie</span>
          <span class="detail-value">{{ request.category.name }}</span>
        </div>
        <div v-if="request.publisher" class="detail-row">
          <span class="detail-label">Éditeur</span>
          <span class="detail-value">{{ request.publisher.name }}</span>
        </div>
        <div v-if="request.isbn" class="detail-row">
          <span class="detail-label">ISBN</span>
          <span class="detail-value">{{ request.isbn }}</span>
        </div>
        <div v-if="request.publication_year" class="detail-row">
          <span class="detail-label">Année</span>
          <span class="detail-value">{{ request.publication_year }}</span>
        </div>
        <div v-if="request.pages" class="detail-row">
          <span class="detail-label">Pages</span>
          <span class="detail-value">{{ request.pages }}</span>
        </div>
        <div v-if="request.cover_url || request.cover_image" class="detail-row">
          <span class="detail-label">Couverture</span>
          <img :src="request.cover_url || `/storage/${request.cover_image}`" alt="Couverture" class="cover-thumb" />
        </div>
        <div class="detail-row">
          <span class="detail-label">Date de soumission</span>
          <span class="detail-value">{{ formatDate(request.created_at) }}</span>
        </div>
        <div v-if="request.proposed_file_url || request.proposed_file" class="detail-row">
          <span class="detail-label">Fichier</span>
          <Button label="Télécharger" icon="pi pi-download" class="p-button-outlined p-button-sm" @click="downloadFile" />
        </div>
      </div>

      <div v-if="reviews.length" class="reviews-section">
        <h2>Avis</h2>
        <div v-for="review in reviews" :key="review.id" class="review-card">
          <div class="review-header">
            <strong>{{ review.reviewer?.full_name || 'Examinateur' }}</strong>
            <StatusBadge :status="review.status" />
          </div>
          <p v-if="review.comment" class="review-comment">{{ review.comment }}</p>
          <small class="review-date">{{ formatDate(review.created_at) }}</small>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.page { max-width: 800px; }
.page-header { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; flex: 1; }
.loading { text-align: center; padding: 2rem; color: var(--text-secondary); }
.alert { padding: 0.75rem 1rem; border-radius: 0.375rem; font-size: 0.85rem; }
.alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
.detail-card { background: #fff; border: 1px solid var(--border); border-radius: 0.75rem; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.detail-row { display: flex; flex-direction: column; gap: 0.2rem; }
.detail-label { font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.03em; }
.detail-value { font-size: 0.95rem; color: var(--text-primary); }
.cover-thumb { width: 100px; height: 140px; object-fit: cover; border-radius: 0.5rem; border: 1px solid var(--border); }
.reviews-section { margin-top: 2rem; }
.reviews-section h2 { font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; }
.review-card { background: #fff; border: 1px solid var(--border); border-radius: 0.5rem; padding: 1rem; margin-bottom: 0.75rem; }
.review-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; }
.review-comment { font-size: 0.9rem; color: var(--text-primary); line-height: 1.5; }
.review-date { display: block; margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-secondary); }
</style>
