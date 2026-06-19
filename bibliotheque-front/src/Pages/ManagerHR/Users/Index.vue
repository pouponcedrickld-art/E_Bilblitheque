<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'
import type { User } from '@/types'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'

const router = useRouter()
const toast = useToast()
const confirm = useConfirm()

const users = ref<User[]>([])
const loading = ref(true)

async function fetchUsers() {
  loading.value = true
  try {
    const response = await http.get('/users')
    users.value = response.data?.data ?? response.data ?? []
  } catch {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les utilisateurs', life: 3000 })
  } finally {
    loading.value = false
  }
}

async function toggleStatus(user: User) {
  const newStatus = user.status === 'active' ? 'suspended' : 'active'
  const action = newStatus === 'suspended' ? 'suspendre' : 'activer'
  confirm.require({
    message: `Voulez-vous ${action} l'utilisateur "${user.full_name}" ?`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    accept: async () => {
      try {
        await http.put(`/users/${user.id}`, { status: newStatus })
        user.status = newStatus
        toast.add({ severity: 'success', summary: 'Succès', detail: `Utilisateur ${action}`, life: 3000 })
      } catch {
        toast.add({ severity: 'error', summary: 'Erreur', detail: `Impossible de ${action} l'utilisateur`, life: 3000 })
      }
    },
  })
}

async function resetPassword(user: User) {
  confirm.require({
    message: `Réinitialiser le mot de passe de "${user.full_name}" ? Un nouveau mot de passe lui sera envoyé par email.`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    accept: async () => {
      try {
        await http.post(`/users/${user.id}/reset-password`)
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Mot de passe réinitialisé', life: 3000 })
      } catch {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de réinitialiser le mot de passe', life: 3000 })
      }
    },
  })
}

function getRoleLabel(role: string): string {
  const labels: Record<string, string> = {
    admin: 'Administrateur',
    responsable_rh: 'Responsable RH',
    responsable_demande: 'Responsable Demande',
    user: 'Utilisateur',
  }
  return labels[role] || role
}

onMounted(fetchUsers)
</script>

<template>
  <div class="users-page">
    <div class="page-header">
      <h1>Gestion des utilisateurs</h1>
      <Button label="Nouvel utilisateur" icon="pi pi-user-plus" @click="router.push('/rh/users/create')" />
    </div>

    <DataTable :value="users" :loading="loading" stripedRows paginator :rows="15" :rowsPerPageOptions="[10, 15, 25, 50]" sortField="created_at" :sortOrder="-1">
      <Column field="full_name" header="Nom" sortable>
        <template #body="{ data }">
          <span class="user-name">{{ data.full_name }}</span>
        </template>
      </Column>
      <Column field="email" header="Email" sortable />
      <Column field="role" header="Rôle" sortable>
        <template #body="{ data }">
          <Tag :value="getRoleLabel(data.role)" :severity="data.role === 'admin' ? 'danger' : data.role === 'responsable_rh' ? 'info' : data.role === 'responsable_demande' ? 'warn' : 'contrast'" />
        </template>
      </Column>
      <Column field="status" header="Statut" sortable>
        <template #body="{ data }">
          <StatusBadge :status="data.status" />
        </template>
      </Column>
      <Column header="Actions" style="min-width: 12rem">
        <template #body="{ data }">
          <div class="actions">
            <Button icon="pi pi-pencil" severity="info" rounded text @click="router.push(`/rh/users/${data.id}/edit`)" v-tooltip.left="'Modifier'" />
            <Button
              :icon="data.status === 'active' ? 'pi pi-ban' : 'pi pi-check-circle'"
              :severity="data.status === 'active' ? 'danger' : 'success'"
              rounded text
              @click="toggleStatus(data)"
              v-tooltip.left="data.status === 'active' ? 'Suspendre' : 'Activer'"
            />
            <Button icon="pi pi-key" severity="warn" rounded text @click="resetPassword(data)" v-tooltip.left="'Réinitialiser mot de passe'" />
          </div>
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<style scoped>
.users-page { padding: 0; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.user-name { font-weight: 500; }
.actions { display: flex; gap: 0.25rem; }
</style>
