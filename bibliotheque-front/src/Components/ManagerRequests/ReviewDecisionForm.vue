<script setup lang="ts">
import { ref } from 'vue'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import Button from 'primevue/button'
import Textarea from 'primevue/textarea'

const props = defineProps<{
  depositRequestId: number
  show: boolean
}>()

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
  } catch {
    toastStore.error("Erreur lors de l'approbation.")
  } finally {
    submitting.value = false
  }
}

async function reject() {
  if (!justification.value.trim()) return
  submitting.value = true
  try {
    await http.post(`/deposit-requests/${props.depositRequestId}/reject-manager`, {
      justification: justification.value,
    })
    toastStore.success('Demande refusée.')
    emit('done')
  } catch {
    toastStore.error('Erreur lors du refus.')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div v-if="show" class="decision-form">
    <h2 class="form-title">Décision</h2>

    <div v-if="!showRejectForm" class="actions">
      <Button
        icon="pi pi-check"
        label="Approuver"
        severity="success"
        :loading="submitting"
        @click="approve"
      />
      <Button
        icon="pi pi-times"
        label="Refuser"
        severity="danger"
        :disabled="submitting"
        @click="showRejectForm = true"
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
      </div>
      <div class="reject-actions">
        <Button
          icon="pi pi-times"
          label="Confirmer le refus"
          severity="danger"
          :loading="submitting"
          :disabled="!justification.trim()"
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
.decision-form {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1.25rem;
}

.form-title {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 1rem;
}

.actions {
  display: flex;
  gap: 0.75rem;
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
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-primary);
}

.reject-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}
</style>
