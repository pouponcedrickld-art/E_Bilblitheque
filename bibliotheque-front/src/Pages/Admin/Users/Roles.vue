<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Select from 'primevue/select'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'

interface User {
  id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  role: string
}

const toastStore = useToastStore()

const users = ref<User[]>([])
const loading = ref(false)
const globalFilter = ref('')

const filteredUsers = computed(() => {
  if (!globalFilter.value) return users.value
  const q = globalFilter.value.toLowerCase()
  return users.value.filter(u =>
    u.full_name.toLowerCase().includes(q) ||
    u.email.toLowerCase().includes(q) ||
    u.role.toLowerCase().includes(q)
  )
})
const saving = ref<Record<number, boolean>>({})

const roleOptions = [
  { label: 'Administrateur', value: 'admin' },
  { label: 'Responsable RH', value: 'responsable_rh' },
  { label: 'Responsable Demande', value: 'responsable_demande' },
  { label: 'Utilisateur', value: 'user' },
]

const roleSeverity: Record<string, string> = {
  admin: 'danger',
  responsable_rh: 'info',
  responsable_demande: 'warn',
  user: 'success',
}

async function fetchUsers() {
  loading.value = true
  try {
    const res = await http.get('/users')
    users.value = res.data?.data ?? res.data ?? []
  } finally {
    loading.value = false
  }
}

async function changeRole(user: User) {
  saving.value[user.id] = true
  try {
    await http.put(`/users/${user.id}`, { role: user.role })
    toastStore.success(`Rôle de ${user.full_name} mis à jour.`)
  } catch {
    toastStore.error('Erreur lors de la mise à jour du rôle.')
    await fetchUsers()
  } finally {
    saving.value[user.id] = false
  }
}

onMounted(fetchUsers)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Gestion des rôles</h1>
      <Button icon="pi pi-refresh" label="Actualiser" severity="secondary" @click="fetchUsers" />
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
    >
      <Column header="Nom" sortable>
        <template #body="{ data }">
          {{ data.full_name }}
        </template>
      </Column>
      <Column field="email" header="Email" sortable />
      <Column field="role" header="Rôle actuel" sortable>
        <template #body="{ data }">
          <Tag :value="data.role" :severity="roleSeverity[data.role] || 'contrast'" />
        </template>
      </Column>
      <Column header="Nouveau rôle">
        <template #body="{ data }">
          <div class="role-editor">
            <Select
              v-model="data.role"
              :options="roleOptions"
              option-label="label"
              option-value="value"
              :loading="saving[data.id]"
              @change="changeRole(data)"
            />
          </div>
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<style scoped>
.page { max-width: 1000px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.role-editor { max-width: 14rem; }
.toolbar { display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: center; }
.search-input { min-width: 260px; }
@media (max-width: 640px) {
  .page { padding: 1rem; }
  .page-header { flex-wrap: wrap; gap: 0.5rem; }
  .page-header h1 { font-size: 1.1rem; }
  .toolbar { flex-direction: column; align-items: stretch; }
  .search-input { min-width: 0; width: 100%; }
}
</style>
