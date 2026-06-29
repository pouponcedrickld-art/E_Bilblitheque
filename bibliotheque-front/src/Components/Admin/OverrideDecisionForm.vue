<script setup lang="ts">
import { ref } from 'vue'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import Button from 'primevue/button'
import Textarea from 'primevue/textarea'

const props = defineProps<{
  depositRequestId: number
}>()

const emit = defineEmits<{
  done: []
}>()

const toastStore = useToastStore()
const justification = ref('')
const submitting = ref(false)

async function submit() {
  if (!justification.value.trim()) return
  submitting.value = true
  try {
    await http.post(`/deposit-requests/${props.depositRequestId}/override`, {
      justification: justification.value,
    })
    toastStore.success('Décision annulée avec succès.')
    emit('done')
  } catch {
    toastStore.error("Erreur lors de l'annulation.")
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="override-card">
    <div class="card-header">
      <svg viewBox="0 0 20 20" width="16" height="16" class="header-icon">
        <circle cx="10" cy="10" r="8" fill="none" stroke="var(--gold)" stroke-width="1.5" />
        <text x="10" y="14" text-anchor="middle" font-size="10" fill="var(--gold)" font-family="Playfair Display, serif">!</text>
      </svg>
      <span class="header-title">Annuler la décision</span>
    </div>
    <div class="field">
      <label for="override-justification">Justification</label>
      <Textarea
        id="override-justification"
        v-model="justification"
        rows="4"
        :auto-resize="true"
        placeholder="Expliquez pourquoi vous annulez la décision..."
        :disabled="submitting"
      />
    </div>
    <div class="form-actions">
      <Button
        icon="pi pi-undo"
        label="Confirmer l'annulation"
        class="submit-btn"
        :loading="submitting"
        :disabled="!justification.trim()"
        @click="submit"
      />
    </div>
  </div>
</template>

<style scoped>
.override-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.25rem;
  transition: border-color 0.25s;
}
.override-card:hover {
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

.field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-bottom: 1rem;
}
.field label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--foreground);
}

.form-actions {
  display: flex;
  gap: 0.75rem;
}
.submit-btn {
  background: #b45309 !important;
  border: none !important;
  border-radius: var(--radius-lg) !important;
  font-weight: 600 !important;
}
.submit-btn:hover {
  filter: brightness(1.1) !important;
  box-shadow: 0 4px 16px rgba(180,83,9,0.3) !important;
}
</style>
