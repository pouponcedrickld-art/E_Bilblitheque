<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import Button from 'primevue/button'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import Message from 'primevue/message'
import Card from 'primevue/card'
import Divider from 'primevue/divider'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'

const route = useRoute()
const router = useRouter()
const toastStore = useToastStore()

const request = ref<any>(null)
const reviews = ref<any[]>([])
const users = ref<any[]>([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')

const showRejectForm = ref(false)
const rejectJustification = ref('')

const showOverrideForm = ref(false)
const overrideJustification = ref('')

const showSecondReviewForm = ref(false)
const secondReviewManagerId = ref<number | null>(null)

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

async function publishRequest() {
  submitting.value = true
  try {
    const id = route.params.id as string
    await http.post(`/deposit-requests/${id}/publish`)
    toastStore.success('Demande publiée.')
    await load()
  } catch (err: any) {
    toastStore.error(err.response?.data?.message || 'Erreur.')
  } finally {
    submitting.value = false
  }
}

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

async function overrideRequest() {
  if (!overrideJustification.value.trim()) return
  submitting.value = true
  try {
    const id = route.params.id as string
    await http.post(`/deposit-requests/${id}/override`, {
      justification: overrideJustification.value,
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

async function requestSecondReview() {
  if (!secondReviewManagerId.value) return
  submitting.value = true
  try {
    const id = route.params.id as string
    await http.post(`/deposit-requests/${id}/second-review`, {
      manager_id: secondReviewManagerId.value,
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
              <span>{{ request.manager?.full_name ?? request.manager?.name ?? '-' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Date</span>
              <span>{{ new Date(request.created_at).toLocaleDateString() }}</span>
            </div>
            <div v-if="request.description" class="info-item full">
              <span class="info-label">Description</span>
              <p>{{ request.description }}</p>
            </div>
          </div>
        </template>
      </Card>

      <div class="actions-section">
        <Button
          v-if="request.status === 'approved_by_manager'"
          icon="pi pi-check"
          label="Publier"
          severity="success"
          :loading="submitting"
          @click="publishRequest"
        />

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
          label="Demander un second avis"
          severity="info"
          :disabled="showSecondReviewForm"
          @click="showSecondReviewForm = true"
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
          <div class="form-actions">
            <Button icon="pi pi-undo" label="Confirmer l'annulation" severity="warn" :loading="submitting" :disabled="!overrideJustification.trim()" @click="overrideRequest" />
            <Button icon="pi pi-times" label="Annuler" severity="secondary" text @click="showOverrideForm = false; overrideJustification = ''" />
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
            <Button icon="pi pi-sync" label="Demander le second avis" severity="info" :loading="submitting" :disabled="!secondReviewManagerId" @click="requestSecondReview" />
            <Button icon="pi pi-times" label="Annuler" severity="secondary" text @click="showSecondReviewForm = false; secondReviewManagerId = null" />
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
.field { display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 1rem; }
.field label { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
.form-actions { display: flex; gap: 0.75rem; margin-top: 0.5rem; }
.actions-section { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.review-item { padding: 0.75rem 0; border-bottom: 1px solid var(--border); }
.review-item:last-child { border-bottom: none; }
.review-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.35rem; }
.review-date { font-size: 0.8rem; color: var(--text-secondary); margin-left: auto; }
.review-justification { font-size: 0.9rem; color: var(--text-primary); margin: 0; }
</style>
