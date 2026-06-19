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
  <div class="override-form">
    <h3 class="form-title">Annuler la décision</h3>
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
        severity="warn"
        :loading="submitting"
        :disabled="!justification.trim()"
        @click="submit"
      />
    </div>
  </div>
</template>

<style scoped>
.override-form {
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
.field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-bottom: 1rem;
}
.field label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-primary);
}
.form-actions {
  display: flex;
  gap: 0.75rem;
}
</style>
