<script setup lang="ts">
// Liste des utilisateurs pour le responsable RH
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'
import type { User } from '@/types'
import { useToast } from 'primevue/usetoast'
import { useConfirm } from 'primevue/useconfirm'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'

const router = useRouter()
const toast = useToast()
const confirm = useConfirm()

// Utilisateurs et filtre
const users = ref<User[]>([])
const loading = ref(true)
const globalFilter = ref('')

// Filtre les utilisateurs par recherche textuelle
const filteredUsers = computed(() => {
  if (!globalFilter.value) return users.value
  const q = globalFilter.value.toLowerCase()
  return users.value.filter(u =>
    u.full_name.toLowerCase().includes(q) ||
    u.email.toLowerCase().includes(q) ||
    u.role.toLowerCase().includes(q) ||
    u.status.toLowerCase().includes(q)
  )
})

// Récupère la liste des utilisateurs
async function fetchUsers() {
  loading.value = true
  try {
    const response = await http.get('/users', { params: { per_page: 'all' } })
    users.value = response.data?.data ?? response.data ?? []
  } catch {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de charger les utilisateurs', life: 3000 })
  } finally {
    loading.value = false
  }
}

// Dialogue de demande de suspension
const suspensionDialog = ref(false)
const suspendTarget = ref<User | null>(null)
const suspendReason = ref('')
const suspending = ref(false)

function openSuspendDialog(user: User) {
  suspendTarget.value = user
  suspendReason.value = ''
  suspensionDialog.value = true
}

async function submitSuspensionRequest() {
  if (!suspendTarget.value || !suspendReason.value.trim()) return
  suspending.value = true
  try {
    await http.post('/suspension-requests', {
      user_id: suspendTarget.value.id,
      reason: suspendReason.value,
    })
    toast.add({ severity: 'success', summary: 'Demande envoyée', detail: `Demande de suspension de "${suspendTarget.value.full_name}" soumise à l'administrateur.`, life: 4000 })
    suspensionDialog.value = false
    suspendTarget.value = null
    suspendReason.value = ''
    await fetchUsers()
  } catch {
    toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible de soumettre la demande.', life: 3000 })
  } finally {
    suspending.value = false
  }
}

// Active directement un utilisateur (pas d'approbation nécessaire)
async function activateUser(user: User) {
  confirm.require({
    message: `Voulez-vous activer l'utilisateur "${user.full_name}" ?`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-success',
    rejectClass: 'p-button-secondary',
    accept: async () => {
      try {
        await http.post(`/users/${user.id}/activate`)
        user.status = 'active'
        toast.add({ severity: 'success', summary: 'Succès', detail: 'Utilisateur activé', life: 3000 })
        await fetchUsers()
      } catch {
        toast.add({ severity: 'error', summary: 'Erreur', detail: 'Impossible d\'activer l\'utilisateur', life: 3000 })
      }
    },
  })
}

// Réinitialise le mot de passe d'un utilisateur
async function resetPassword(user: User) {
  confirm.require({
    message: `Réinitialiser le mot de passe de "${user.full_name}" ? Un nouveau mot de passe lui sera envoyé par email.`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    rejectClass: 'p-button-secondary',
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

// Traduit un rôle en libellé lisible
function getRoleLabel(role: string): string {
  const labels: Record<string, string> = {
    admin: 'Administrateur',
    responsable_rh: 'Responsable RH',
    responsable_demande: 'Responsable Demande',
    user: 'Utilisateur',
  }
  return labels[role] || role
}

// Charge les utilisateurs au montage
onMounted(fetchUsers)
</script>

<template>
  <div class="users-page">
    <div class="page-header">
      <h1>Gestion des utilisateurs</h1>
      <Button label="Nouvel utilisateur" icon="pi pi-user-plus" @click="router.push('/rh/users/create')" />
    </div>

    <div class="toolbar">
      <IconField iconPosition="left">
        <InputIcon><i class="pi pi-search" /></InputIcon>
        <InputText v-model="globalFilter" placeholder="Rechercher..." class="search-input" />
      </IconField>
    </div>

    <DataTable :value="filteredUsers" :loading="loading" stripedRows paginator :rows="15" :rowsPerPageOptions="[10, 15, 25, 50]" sortField="created_at" :sortOrder="-1">
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
          <div class="status-cell">
            <StatusBadge :status="data.status" />
            <Tag v-if="data.has_pending_suspension" value="Demande de suspension" severity="warn" class="pending-tag" />
          </div>
        </template>
      </Column>
      <Column header="Actions" style="min-width: 14rem">
        <template #body="{ data }">
          <div class="actions">
            <span v-tooltip.left="'Modifier'"><Button icon="pi pi-pencil" severity="info" rounded text @click="router.push(`/rh/users/${data.id}/edit`)" /></span>
            <span v-tooltip.left="data.has_pending_suspension ? 'Demande déjà envoyée' : 'Demander la suspension'">
            <Button
              v-if="data.status === 'active'"
              icon="pi pi-ban"
              severity="danger"
              rounded text
              :disabled="data.has_pending_suspension"
              @click="openSuspendDialog(data)"
            />
            </span>
            <span v-tooltip.left="'Activer'">
            <Button
              v-if="data.status === 'suspended'"
              icon="pi pi-check-circle"
              severity="success"
              rounded text
              @click="activateUser(data)"
            />
            </span>
            <span v-tooltip.left="'Réinitialiser mot de passe'"><Button icon="pi pi-key" severity="warn" rounded text @click="resetPassword(data)" /></span>
          </div>
        </template>
      </Column>
    </DataTable>

    <Dialog
      v-model:visible="suspensionDialog"
      header="Demander la suspension"
      :modal="true"
      :closable="!suspending"
      appendTo="body"
      style="max-width: 480px"
    >
      <div class="dialog-body">
        <p class="dialog-desc">
          Vous êtes sur le point de demander la suspension de <strong>{{ suspendTarget?.full_name }}</strong>.
          Un administrateur devra approuver cette demande.
        </p>
        <div class="field">
          <label for="suspend-reason">Motif de la suspension</label>
          <Textarea
            id="suspend-reason"
            v-model="suspendReason"
            rows="4"
            :auto-resize="true"
            placeholder="Expliquez pourquoi cet utilisateur doit être suspendu..."
            :disabled="suspending"
          />
        </div>
      </div>
      <template #footer>
        <Button label="Annuler" severity="secondary" :disabled="suspending" @click="suspensionDialog = false" />
        <Button
          label="Envoyer la demande"
          icon="pi pi-send"
          severity="danger"
          :loading="suspending"
          :disabled="!suspendReason.trim() || suspendReason.trim().length < 10"
          @click="submitSuspensionRequest"
        />
      </template>
    </Dialog>
  </div>
</template>

<style scoped>
.users-page { padding: 0; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.user-name { font-weight: 500; }
.actions { display: flex; gap: 0.25rem; }
.toolbar { display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: center; }
.search-input { min-width: 260px; }
.status-cell { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.pending-tag { font-size: 0.65rem; }
.dialog-body { display: flex; flex-direction: column; gap: 1rem; }
.dialog-desc { font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; margin: 0; }
.field { display: flex; flex-direction: column; gap: 0.4rem; }
.field label { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
</style>
