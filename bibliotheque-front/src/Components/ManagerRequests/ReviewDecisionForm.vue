<script setup lang="ts">
import { ref, computed } from 'vue'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import Button from 'primevue/button'
import Textarea from 'primevue/textarea'

const props = defineProps<{
  depositRequestId: number
  show: boolean
  status?: string
}>()

const actionableStatuses = ['pending', 'second_review']
const canAct = computed(() => props.status ? actionableStatuses.includes(props.status) : true)

const emit = defineEmits<{
  done: []
}>()

const toastStore = useToastStore()
const showRejectForm = ref(false)
const justification = ref('')
const submitting = ref(false)

async function approve() {
  submitting.value = true
  try {
    await http.post(`/deposit-requests/${props.depositRequestId}/approve-manager`)
    toastStore.success('Demande approuvée avec succès.')
    emit('done')
  } catch (err: any) {
    const msg = err?.response?.data?.message ?? "Erreur lors de l'approbation."
    toastStore.error(msg)
  } finally {
    submitting.value = false
  }
}

async function reject() {
  if (!justification.value.trim() || justification.value.trim().length < 10) return
  submitting.value = true
  try {
    await http.post(`/deposit-requests/${props.depositRequestId}/reject-manager`, {
      justification: justification.value,
    })
    toastStore.success('Demande refusée.')
    emit('done')
  } catch (err: any) {
    const msg = err?.response?.data?.errors?.justification?.[0] ?? 'Erreur lors du refus.'
    toastStore.error(msg)
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div v-if="show" class="decision-card">
    <div class="card-header">
      <svg viewBox="0 0 20 20" width="16" height="16" class="header-icon">
        <circle cx="10" cy="10" r="8" fill="none" stroke="var(--gold)" stroke-width="1.5" />
        <text x="10" y="14" text-anchor="middle" font-size="10" fill="var(--gold)" font-family="Playfair Display, serif">✓</text>
      </svg>
      <span class="header-title">Décision</span>
    </div>

    <div v-if="!canAct" class="no-action">
      Cette demande ne peut plus être modifiée.
    </div>

    <div v-else-if="!showRejectForm" class="actions">
      <Button
        icon="pi pi-check"
        label="Approuver"
        severity="success"
        :loading="submitting"
        @click="approve"
        class="action-btn approve-btn"
      />
      <Button
        icon="pi pi-times"
        label="Refuser"
        severity="danger"
        :disabled="submitting"
        @click="showRejectForm = true"
        class="action-btn reject-btn"
      />
    </div>

    <div v-else class="reject-form">
      <div class="field">
        <label for="justification">Justification du refus</label>
        <Textarea
          id="justification"
          v-model="justification"
          rows="4"
          :auto-resize="true"
          placeholder="Expliquez la raison du refus..."
          :disabled="submitting"
        />
        <small class="hint">Minimum 10 caractères requis</small>
      </div>
      <div class="reject-actions">
        <Button
          icon="pi pi-times"
          label="Confirmer le refus"
          class="confirm-reject-btn"
          :loading="submitting"
          :disabled="!justification.trim() || justification.trim().length < 10"
          @click="reject"
        />
        <Button
          icon="pi pi-undo"
          label="Annuler"
          severity="secondary"
          text
          :disabled="submitting"
          @click="showRejectForm = false; justification = ''"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.decision-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.25rem;
  transition: border-color 0.25s;
}
.decision-card:hover {
  border-color: var(--border-gold);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding-bottom: 0.65rem;
  border-bottom: 1px solid var(--border);
}
.header-icon {
  flex-shrink: 0;
}
.header-title {
  font-size: 0.95rem;
  font-weight: 700;
}

.no-action {
  color: var(--muted-foreground);
  font-style: italic;
  padding: 0.5rem 0;
  font-size: 0.85rem;
}

.actions {
  display: flex;
  gap: 0.75rem;
}
.action-btn {
  border-radius: var(--radius-lg) !important;
  font-weight: 600 !important;
}
.approve-btn:hover {
  box-shadow: 0 4px 16px rgba(22,101,52,0.3) !important;
}
.reject-btn:hover {
  box-shadow: 0 4px 16px rgba(185,28,28,0.3) !important;
}

.reject-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.field label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--foreground);
}
.hint {
  color: var(--muted-foreground);
  font-size: 0.75rem;
}

.reject-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}
.confirm-reject-btn {
  background: #b91c1c !important;
  border: none !important;
  border-radius: var(--radius-lg) !important;
  font-weight: 600 !important;
}
.confirm-reject-btn:hover {
  filter: brightness(1.1) !important;
  box-shadow: 0 4px 16px rgba(185,28,28,0.3) !important;
}
</style>
