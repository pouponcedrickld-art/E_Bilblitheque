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
import { useConfirm } from 'primevue/useconfirm'
import StatusBadge from '@/Components/Shared/StatusBadge.vue'

// Interface d'un utilisateur
interface User {
  id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  role: string
  status: string
  last_login_at: string | null
}

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

// Récupère la liste des utilisateurs
async function fetchUsers() {
  loading.value = true
  try {
    const res = await http.get('/users')
    users.value = res.data?.data ?? res.data ?? []
  } finally {
    loading.value = false
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
          <StatusBadge :status="data.status" />
        </template>
      </Column>
      <Column field="last_login_at" header="Dernière connexion" sortable>
        <template #body="{ data }">
          {{ data.last_login_at ? new Date(data.last_login_at).toLocaleDateString() : '-' }}
        </template>
      </Column>
      <Column header="Actions" style="min-width: 12rem">
        <template #body="{ data }">
          <div class="actions">
            <Button
              icon="pi pi-pencil"
              severity="info"
              text
              @click="router.push(`/admin/users/${data.id}/edit`)"
            />
            <Button
              :icon="data.status === 'active' ? 'pi pi-ban' : 'pi pi-check'"
              :severity="data.status === 'active' ? 'danger' : 'success'"
              text
              @click="toggleStatus(data)"
            />
            <Button
              icon="pi pi-key"
              severity="warn"
              text
              @click="resetPassword(data)"
            />
          </div>
        </template>
      </Column>
    </DataTable>

    <ConfirmDialog />
  </div>
</template>

<style scoped>
.page { max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.actions { display: flex; gap: 0.25rem; }
.toolbar { display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: center; }
.search-input { min-width: 260px; }
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
