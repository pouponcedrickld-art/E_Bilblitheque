<script setup lang="ts">
import { ref } from 'vue'
import http from '@/services/http'
import Button from 'primevue/button'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'

const props = defineProps<{
  userId: number
  currentStatus: string
}>()

const emit = defineEmits<{
  done: [status: string]
}>()

const loading = ref(false)
const confirm = useConfirm()
const toast = useToast()

function toggle() {
  const isActive = props.currentStatus === 'active'
  const action = isActive ? 'suspendre' : 'activer'

  confirm.require({
    message: `Voulez-vous ${action} cet utilisateur ?`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    accept: async () => {
      loading.value = true
      try {
        const endpoint = isActive ? `/users/${props.userId}/suspend` : `/users/${props.userId}/activate`
        await http.post(endpoint)
        const newStatus = isActive ? 'suspended' : 'active'
        toast.add({ severity: 'success', summary: 'Succès', detail: `Utilisateur ${action}`, life: 3000 })
        emit('done', newStatus)
      } catch {
        toast.add({ severity: 'error', summary: 'Erreur', detail: `Impossible de ${action} l'utilisateur`, life: 3000 })
      } finally {
        loading.value = false
      }
    },
  })
}
</script>

<template>
  <Button
    :icon="currentStatus === 'active' ? 'pi pi-ban' : 'pi pi-check-circle'"
    :severity="currentStatus === 'active' ? 'danger' : 'success'"
    :loading="loading"
    rounded
    text
    v-tooltip.left="currentStatus === 'active' ? 'Suspendre' : 'Activer'"
    @click="toggle"
  />
</template>
