<script setup lang="ts">
// Gestion des utilisateurs pour l'admin
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import ConfirmDialog from 'primevue/confirmdialog'
import Dialog from 'primevue/dialog'
import Textarea from 'primevue/textarea'
import { useConfirm } from 'primevue/useconfirm'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'
import type { User, SuspensionRequest } from '@/types'

const router = useRouter()
const toastStore = useToastStore()
const confirm = useConfirm()

// Utilisateurs et filtre
const users = ref<User[]>([])
const loading = ref(false)
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

// Couleurs des badges par rôle
const roleSeverity: Record<string, string> = {
  admin: 'danger',
  responsable_rh: 'info',
  responsable_demande: 'warn',
  user: 'success',
}

// Demandes de suspension en attente
const pendingRequests = ref<SuspensionRequest[]>([])

// Dialogue de rejet
const rejectDialog = ref(false)
const rejectTarget = ref<SuspensionRequest | null>(null)
const rejectionReason = ref('')
const rejecting = ref(false)

// Récupère la liste des utilisateurs et les demandes en attente
async function fetchUsers() {
  loading.value = true
  try {
    const [usersRes, pendingRes] = await Promise.all([
      http.get('/users'),
      http.get('/suspension-requests', { params: { status: 'pending', per_page: 100 } }),
    ])
    users.value = usersRes.data?.data ?? usersRes.data ?? []
    pendingRequests.value = pendingRes.data?.data ?? []
  } finally {
    loading.value = false
  }
}

// Approuve une demande de suspension
async function approveSuspension(user: User) {
  const req = pendingRequests.value.find(r => r.user_id === user.id)
  if (!req) return

  confirm.require({
    message: `Approuver la suspension de "${user.full_name}" ?`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    rejectClass: 'p-button-secondary',
    accept: async () => {
      try {
        await http.post(`/suspension-requests/${req.id}/approve`)
        toastStore.success('Suspension approuvée.')
        await fetchUsers()
      } catch {
        toastStore.error("Erreur lors de l'approbation.")
      }
    },
  })
}

// Ouvre le dialogue de rejet
function openRejectDialog(user: User) {
  const req = pendingRequests.value.find(r => r.user_id === user.id)
  if (!req) return
  rejectTarget.value = req
  rejectionReason.value = ''
  rejectDialog.value = true
}

// Rejette une demande de suspension
async function submitRejection() {
  if (!rejectTarget.value || !rejectionReason.value.trim()) return
  rejecting.value = true
  try {
    await http.post(`/suspension-requests/${rejectTarget.value.id}/reject`, {
      rejection_reason: rejectionReason.value,
    })
    toastStore.success('Demande de suspension rejetée.')
    rejectDialog.value = false
    rejectTarget.value = null
    rejectionReason.value = ''
    await fetchUsers()
  } catch {
    toastStore.error("Erreur lors du rejet.")
  } finally {
    rejecting.value = false
  }
}

// Active ou suspend un utilisateur
async function toggleStatus(user: User) {
  const action = user.status === 'active' ? 'suspend' : 'activate'
  confirm.require({
    message: `${action === 'suspend' ? 'Suspendre' : 'Activer'} ${user.full_name} ?`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: action === 'suspend' ? 'p-button-danger' : 'p-button-success',
    rejectClass: 'p-button-secondary',
    accept: async () => {
      try {
        await http.post(`/users/${user.id}/${action}`)
        toastStore.success(`Utilisateur ${action === 'suspend' ? 'suspendu' : 'activé'}.`)
        await fetchUsers()
      } catch {
        toastStore.error("Erreur lors de l'opération.")
      }
    },
  })
}

// Réinitialise le mot de passe d'un utilisateur
async function resetPassword(user: User) {
  confirm.require({
    message: `Réinitialiser le mot de passe de ${user.full_name} ?`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    rejectClass: 'p-button-secondary',
    accept: async () => {
      try {
        await http.post(`/users/${user.id}/reset-password`)
        toastStore.success('Mot de passe réinitialisé.')
      } catch {
        toastStore.error('Erreur lors de la réinitialisation.')
      }
    },
  })
}

// Charge les utilisateurs au montage
onMounted(fetchUsers)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Gestion des utilisateurs</h1>
      <div class="header-actions">
        <Button icon="pi pi-user-plus" label="Nouvel utilisateur" @click="router.push('/admin/users/create')" />
        <Button icon="pi pi-refresh" label="Actualiser" severity="secondary" @click="fetchUsers" />
      </div>
    </div>

    <div class="toolbar">
      <IconField iconPosition="left">
        <InputIcon><i class="pi pi-search" /></InputIcon>
        <InputText v-model="globalFilter" placeholder="Rechercher..." class="search-input" />
      </IconField>
    </div>

    <DataTable
      :value="filteredUsers"
      :loading="loading"
      striped-rows
      paginator
      :rows="20"
      :rows-per-page-options="[10, 20, 50]"
      sort-field="last_name"
      :sort-order="1"
    >
      <Column header="Nom" sortable>
        <template #body="{ data }">
          {{ data.full_name }}
        </template>
      </Column>
      <Column field="email" header="Email" sortable />
      <Column field="role" header="Rôle" sortable>
        <template #body="{ data }">
          <Tag :value="data.role" :severity="roleSeverity[data.role] || 'contrast'" />
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
      <Column field="last_login_at" header="Dernière connexion" sortable>
        <template #body="{ data }">
          {{ data.last_login_at ? new Date(data.last_login_at).toLocaleDateString() : '-' }}
        </template>
      </Column>
      <Column header="Actions" style="min-width: 14rem">
        <template #body="{ data }">
          <div class="actions">
            <template v-if="data.has_pending_suspension">
              <Button icon="pi pi-check" severity="danger" text v-tooltip.left="'Approuver la suspension'" @click="approveSuspension(data)" />
              <Button icon="pi pi-times" severity="warn" text v-tooltip.left="'Rejeter la demande'" @click="openRejectDialog(data)" />
            </template>
            <template v-else>
              <Button
                icon="pi pi-pencil"
                severity="info"
                text
                @click="router.push(`/admin/users/${data.id}/edit`)"
                v-tooltip.left="'Modifier'"
              />
              <Button
                :icon="data.status === 'active' ? 'pi pi-ban' : 'pi pi-check'"
                :severity="data.status === 'active' ? 'danger' : 'success'"
                text
                @click="toggleStatus(data)"
                v-tooltip.left="data.status === 'active' ? 'Suspendre' : 'Activer'"
              />
              <Button
                icon="pi pi-key"
                severity="warn"
                text
                @click="resetPassword(data)"
                v-tooltip.left="'Réinitialiser mot de passe'"
              />
            </template>
          </div>
        </template>
      </Column>
    </DataTable>

    <ConfirmDialog />

    <Dialog
      v-model:visible="rejectDialog"
      header="Rejeter la demande de suspension"
      :modal="true"
      :closable="!rejecting"
      style="max-width: 480px"
    >
      <div class="dialog-body">
        <p class="dialog-desc">
          Vous allez rejeter la demande de suspension de <strong>{{ rejectTarget?.user?.full_name }}</strong>.
          Veuillez indiquer le motif du rejet.
        </p>
        <div class="field">
          <label for="rejection-reason">Motif du rejet</label>
          <Textarea
            id="rejection-reason"
            v-model="rejectionReason"
            rows="4"
            :auto-resize="true"
            placeholder="Expliquez pourquoi la suspension est refusée..."
            :disabled="rejecting"
          />
        </div>
      </div>
      <template #footer>
        <Button label="Annuler" severity="secondary" :disabled="rejecting" @click="rejectDialog = false" />
        <Button
          label="Confirmer le rejet"
          icon="pi pi-times"
          severity="warn"
          :loading="rejecting"
          :disabled="!rejectionReason.trim() || rejectionReason.trim().length < 5"
          @click="submitRejection"
        />
      </template>
    </Dialog>
  </div>
</template>

<style scoped>
.page { max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.actions { display: flex; gap: 0.25rem; }
.toolbar { display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: center; }
.search-input { min-width: 260px; }
.status-cell { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.pending-tag { font-size: 0.65rem; }
.dialog-body { display: flex; flex-direction: column; gap: 1rem; }
.dialog-desc { font-size: 0.9rem; color: var(--text-secondary); line-height: 1.5; margin: 0; }
.field { display: flex; flex-direction: column; gap: 0.4rem; }
.field label { font-size: 0.85rem; font-weight: 600; color: var(--text-primary); }
@media (max-width: 640px) {
  .page { padding: 1rem; }
  .page-header { flex-wrap: wrap; gap: 0.5rem; }
  .page-header h1 { font-size: 1.1rem; }
  .header-actions { width: 100%; display: flex; gap: 0.5rem; }
  .header-actions .p-button { flex: 1; }
  .toolbar { flex-direction: column; align-items: stretch; }
  .search-input { min-width: 0; width: 100%; }
}
</style>
