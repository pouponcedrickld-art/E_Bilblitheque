<script setup lang="ts">
// Examen détaillé d'une demande de dépôt par l'admin
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import Button from 'primevue/button'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import Message from 'primevue/message'
import Card from 'primevue/card'
import Tag from 'primevue/tag'
import Divider from 'primevue/divider'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'

const languageLabels: Record<string, string> = { fr: 'Français', en: 'Anglais', autre: 'Autre' }

const route = useRoute()
const router = useRouter()
const toastStore = useToastStore()

// Données de la demande et des avis
const request = ref<any>(null)
const reviews = ref<any[]>([])
const users = ref<any[]>([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')

// État des formulaires de rejet, annulation et second avis
const showRejectForm = ref(false)
const rejectJustification = ref('')
const showOverrideForm = ref(false)
const overrideJustification = ref('')
const showSecondReviewForm = ref(false)
const secondReviewManagerId = ref<number | null>(null)
const forceDownload = ref(false)

// Charge la demande et les utilisateurs
async function load() {
  loading.value = true
  try {
    const id = route.params.id as string
    const [reqRes, usersRes] = await Promise.all([
      http.get(`/deposit-requests/${id}`),
      http.get('/users'),
    ])
    request.value = reqRes.data?.data ?? reqRes.data
    reviews.value = request.value.reviews ?? request.value.history ?? []
    users.value = (usersRes.data?.data ?? usersRes.data ?? []).filter(
      (u: any) => u.role === 'responsable_demande'
    )
  } catch {
    error.value = 'Impossible de charger la demande.'
  } finally {
    loading.value = false
  }
}

// Publie la demande de dépôt
async function publishRequest() {
  submitting.value = true
  try {
    const id = route.params.id as string
    await http.post(`/deposit-requests/${id}/publish`, {
      force_allow_download: forceDownload.value ? '1' : '0',
    })
    toastStore.success('Demande publiée.')
    await load()
  } catch (err: any) {
    toastStore.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

// Rejette la demande avec justification
async function rejectRequest() {
  if (!rejectJustification.value.trim()) return
  submitting.value = true
  try {
    const id = route.params.id as string
    await http.post(`/deposit-requests/${id}/reject-admin`, {
      justification: rejectJustification.value,
    })
    toastStore.success('Demande rejetée.')
    showRejectForm.value = false
    rejectJustification.value = ''
    await load()
  } catch (err: any) {
    toastStore.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

// État du formulaire de réassignation
const showReassignForm = ref(false)
const reassignManagerId = ref<number | null>(null)

// Réassigne la demande à un autre responsable
async function reassignManager() {
  if (!reassignManagerId.value) return
  submitting.value = true
  try {
    const id = route.params.id as string
    await http.post(`/deposit-requests/${id}/reassign`, {
      new_manager_id: reassignManagerId.value,
    })
    toastStore.success('Demande réassignée.')
    showReassignForm.value = false
    reassignManagerId.value = null
    await load()
  } catch (err: any) {
    toastStore.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

// Annule la décision précédente
async function overrideRequest() {
  if (!overrideJustification.value.trim()) return
  submitting.value = true
  try {
    const id = route.params.id as string
    await http.post(`/deposit-requests/${id}/override`, {
      justification: overrideJustification.value,
      force_allow_download: forceDownload.value ? '1' : '0',
    })
    toastStore.success('Décision annulée.')
    showOverrideForm.value = false
    overrideJustification.value = ''
    await load()
  } catch (err: any) {
    toastStore.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

// Demande un second avis à un autre gestionnaire
async function requestSecondReview() {
  if (!secondReviewManagerId.value) return
  submitting.value = true
  try {
    const id = route.params.id as string
    await http.post(`/deposit-requests/${id}/second-review`, {
      new_manager_id: secondReviewManagerId.value,
    })
    toastStore.success('Second avis demandé.')
    showSecondReviewForm.value = false
    secondReviewManagerId.value = null
    await load()
  } catch (err: any) {
    toastStore.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

// Charge les données au montage
onMounted(load)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Examiner la demande</h1>
      <Button icon="pi pi-arrow-left" label="Retour" severity="secondary" text @click="router.push('/admin/deposit-requests')" />
    </div>

    <div v-if="loading" class="loading">Chargement...</div>

    <Message v-if="error" severity="error" :closable="false" class="mb-3">{{ error }}</Message>

    <template v-if="request">
      <Card class="mb-3">
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
              <span class="info-label">Gestionnaire</span>
              <span>{{ request.assigned_manager?.full_name ?? request.assigned_manager?.name ?? '-' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Type</span>
              <Tag :value="request.document_type?.label ?? request.document_type?.name ?? '-'" />
            </div>
            <div class="info-item">
              <span class="info-label">Langue</span>
              <span>{{ languageLabels[request.language ?? ''] || request.language || '-' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Catégorie</span>
              <span>{{ request.category?.name ?? '-' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Éditeur</span>
              <span>{{ request.publisher?.name ?? '-' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">ISBN</span>
              <span>{{ request.isbn || '-' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Année</span>
              <span>{{ request.publication_year || '-' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Pages</span>
              <span>{{ request.pages || '-' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Téléchargement</span>
              <Tag v-if="request.allow_download" value="Autorisé" severity="success" />
              <Tag v-else value="Bloqué" severity="danger" />
            </div>
            <div class="info-item">
              <span class="info-label">Date</span>
              <span>{{ new Date(request.created_at).toLocaleDateString() }}</span>
            </div>
            <div v-if="request.subtitle" class="info-item full">
              <span class="info-label">Sous-titre</span>
              <span>{{ request.subtitle }}</span>
            </div>
            <div v-if="request.abstract" class="info-item full">
              <span class="info-label">Résumé</span>
              <p>{{ request.abstract }}</p>
            </div>
            <div v-if="request.description" class="info-item full">
              <span class="info-label">Description</span>
              <p>{{ request.description }}</p>
            </div>
            <div v-if="request.cover_url || request.cover_image" class="info-item full">
              <span class="info-label">Couverture</span>
              <img :src="request.cover_url || `/storage/${request.cover_image}`" alt="Couverture" class="cover-thumb" />
            </div>
          </div>
        </template>
      </Card>

      <div class="actions-section">
        <div v-if="request.status === 'approved_by_manager'" class="action-block">
          <Button
            icon="pi pi-check"
            label="Publier"
            severity="success"
            :loading="submitting"
            @click="publishRequest"
          />
          <div v-if="!request.allow_download" class="checkbox-field">
            <input id="force-publish" type="checkbox" v-model="forceDownload" />
            <label for="force-publish">Forcer le téléchargement (outrepasser le choix du demandeur)</label>
          </div>
        </div>

        <Button
          v-if="request.status === 'rejected_by_manager'"
          icon="pi pi-undo"
          label="Annuler la décision"
          severity="warn"
          :disabled="showOverrideForm"
          @click="showOverrideForm = true"
        />

        <Button
          v-if="request.status !== 'published' && request.status !== 'rejected'"
          icon="pi pi-times"
          label="Rejeter"
          severity="danger"
          :disabled="showRejectForm"
          @click="showRejectForm = true"
        />

        <Button
          v-if="request.status !== 'second_review' && request.status !== 'published' && request.status !== 'rejected'"
          icon="pi pi-sync"
          label="Second avis"
          severity="secondary"
          outlined
          :disabled="showSecondReviewForm"
          @click="showSecondReviewForm = true"
        />

        <Button
          v-if="request.assigned_manager"
          icon="pi pi-user-edit"
          label="Réassigner"
          severity="secondary"
          outlined
          :disabled="showReassignForm"
          @click="showReassignForm = true"
        />
      </div>

      <Card v-if="showRejectForm" class="mb-3">
        <template #title>Rejeter la demande</template>
        <template #content>
          <div class="field">
            <label for="reject-justification">Justification</label>
            <Textarea id="reject-justification" v-model="rejectJustification" rows="4" :auto-resize="true" placeholder="Expliquez la raison du rejet..." />
          </div>
          <div class="form-actions">
            <Button icon="pi pi-times" label="Confirmer le rejet" severity="danger" :loading="submitting" :disabled="!rejectJustification.trim()" @click="rejectRequest" />
            <Button icon="pi pi-undo" label="Annuler" severity="secondary" text @click="showRejectForm = false; rejectJustification = ''" />
          </div>
        </template>
      </Card>

      <Card v-if="showOverrideForm" class="mb-3">
        <template #title>Annuler la décision</template>
        <template #content>
          <div class="field">
            <label for="override-justification">Justification</label>
            <Textarea id="override-justification" v-model="overrideJustification" rows="4" :auto-resize="true" placeholder="Expliquez pourquoi vous annulez la décision..." />
          </div>
          <div v-if="!request.allow_download" class="checkbox-field">
            <input id="force-override" type="checkbox" v-model="forceDownload" />
            <label for="force-override">Forcer le téléchargement (outrepasser le choix du demandeur)</label>
          </div>
          <div class="form-actions">
            <Button icon="pi pi-undo" label="Confirmer l'annulation" severity="warn" :loading="submitting" :disabled="!overrideJustification.trim()" @click="overrideRequest" />
            <Button icon="pi pi-times" label="Annuler" severity="secondary" text @click="showOverrideForm = false; overrideJustification = ''; forceDownload = false" />
          </div>
        </template>
      </Card>

      <Card v-if="showSecondReviewForm" class="mb-3">
        <template #title>Demander un second avis</template>
        <template #content>
          <div class="field">
            <label for="second-review-manager">Gestionnaire</label>
            <Select id="second-review-manager" v-model="secondReviewManagerId" :options="users" option-label="full_name" option-value="id" placeholder="Sélectionner un gestionnaire" />
          </div>
          <div class="form-actions">
            <Button icon="pi pi-sync" label="Demander le second avis" severity="secondary" :loading="submitting" :disabled="!secondReviewManagerId" @click="requestSecondReview" />
            <Button icon="pi pi-times" label="Annuler" severity="secondary" text @click="showSecondReviewForm = false; secondReviewManagerId = null" />
          </div>
        </template>
      </Card>

      <Card v-if="showReassignForm" class="mb-3">
        <template #title>Réassigner à un autre responsable</template>
        <template #content>
          <div class="field">
            <label for="reassign-manager">Nouveau gestionnaire</label>
            <Select id="reassign-manager" v-model="reassignManagerId" :options="users" option-label="full_name" option-value="id" placeholder="Sélectionner un responsable" />
          </div>
          <div class="form-actions">
            <Button icon="pi pi-user-edit" label="Confirmer la réassignation" severity="secondary" :loading="submitting" :disabled="!reassignManagerId" @click="reassignManager" />
            <Button icon="pi pi-times" label="Annuler" severity="secondary" text @click="showReassignForm = false; reassignManagerId = null" />
          </div>
        </template>
      </Card>

      <Divider />

      <Card v-if="reviews.length">
        <template #title>Historique des avis</template>
        <template #content>
          <div v-for="(review, i) in reviews" :key="i" class="review-item">
            <div class="review-header">
              <strong>{{ review.reviewer?.full_name ?? review.reviewer?.name ?? 'Système' }}</strong>
              <StatusBadge :status="review.status ?? review.decision" />
              <span class="review-date">{{ new Date(review.created_at).toLocaleString() }}</span>
            </div>
            <p v-if="review.justification" class="review-justification">{{ review.justification }}</p>
          </div>
        </template>
      </Card>
    </template>
  </div>
</template>

<style scoped>
.page { max-width: 800px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.loading { text-align: center; padding: 2rem; color: var(--text-secondary); }
.mb-3 { margin-bottom: 1rem; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.info-item { display: flex; flex-direction: column; gap: 0.25rem; }
.info-item.full { grid-column: 1 / -1; }
.info-label { font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.03em; }
.cover-thumb { width: 100px; height: 140px; object-fit: cover; border-radius: 0.5rem; border: 1px solid var(--border); }
@media (max-width: 640px) { .info-grid { grid-template-columns: 1fr; } }
.field { display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 1rem; }
.field label { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
.form-actions { display: flex; gap: 0.75rem; margin-top: 0.5rem; }
.actions-section { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.review-item { padding: 0.75rem 0; border-bottom: 1px solid var(--border); }
.review-item:last-child { border-bottom: none; }
.review-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.35rem; }
.review-date { font-size: 0.8rem; color: var(--text-secondary); margin-left: auto; }
.review-justification { font-size: 0.9rem; color: var(--text-primary); margin: 0; }
.action-block { display: flex; flex-direction: column; gap: 0.75rem; }
.checkbox-field { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; color: var(--text-secondary); }
.checkbox-field input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; }
</style>
