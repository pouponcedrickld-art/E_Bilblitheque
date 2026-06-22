<script setup lang="ts">
// Journal d'activité pour le responsable RH
import { ref, computed, onMounted } from 'vue'
import http from '@/services/http'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'

const toast = useToast()

// Entrée du journal d'activité
interface ActivityLog {
  id: number
  user: { full_name: string; email: string } | null
  action: string
  target_table: string
  target_id: number | null
  created_at: string
}

// Logs et filtre
const logs = ref<ActivityLog[]>([])
const loading = ref(true)
const globalFilter = ref('')

// Filtre les logs par recherche textuelle
const filteredLogs = computed(() => {
  if (!globalFilter.value) return logs.value
  const q = globalFilter.value.toLowerCase()
  return logs.value.filter(l =>
    (l.user?.full_name || '').toLowerCase().includes(q) ||
    l.action.toLowerCase().includes(q) ||
    l.target_table.toLowerCase().includes(q)
  )
})

// Récupère les logs d'activité
async function fetchLogs() {
  loading.value = true
  try {
    const response = await http.get('/activity-logs')
    logs.value = response.data?.data ?? response.data ?? []
  } catch {
    toast.add({ severity: 'error', summary: 'Erreur', detail: "Impossible de charger le journal d'activité", life: 3000 })
  } finally {
    loading.value = false
  }
}

// Formate une date au format français
function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

// Traduit une action en libellé lisible
function getActionLabel(action: string): string {
  const labels: Record<string, string> = {
    created: 'Création',
    updated: 'Modification',
    deleted: 'Suppression',
    login: 'Connexion',
    logout: 'Déconnexion',
    reset_password: 'Réinitialisation mot de passe',
  }
  return labels[action] || action
}

// Charge les logs au montage
onMounted(fetchLogs)
</script>

<template>
  <div class="logs-page">
    <div class="page-header">
      <h1>Journal d'activité</h1>
      <Button label="Actualiser" icon="pi pi-refresh" severity="secondary" @click="fetchLogs" :loading="loading" />
    </div>

    <div class="toolbar">
      <IconField iconPosition="left">
        <InputIcon><i class="pi pi-search" /></InputIcon>
        <InputText v-model="globalFilter" placeholder="Rechercher..." class="search-input" />
      </IconField>
    </div>

    <DataTable :value="filteredLogs" :loading="loading" stripedRows paginator :rows="25" :rowsPerPageOptions="[10, 25, 50, 100]" sortField="created_at" :sortOrder="-1">
      <Column field="user" header="Utilisateur" sortable>
        <template #body="{ data }">
          <span v-if="data.user">{{ data.user.full_name }}</span>
          <span v-else class="unknown-user">—</span>
        </template>
      </Column>
      <Column field="action" header="Action" sortable>
        <template #body="{ data }">
          <Tag :value="getActionLabel(data.action)" :severity="data.action === 'deleted' ? 'danger' : data.action === 'created' ? 'success' : 'info'" />
        </template>
      </Column>
      <Column field="target_table" header="Table cible" sortable />
      <Column field="target_id" header="ID cible" sortable />
      <Column field="created_at" header="Date" sortable>
        <template #body="{ data }">
          {{ formatDate(data.created_at) }}
        </template>
      </Column>
    </DataTable>
  </div>
</template>

<style scoped>
.logs-page { padding: 0; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.unknown-user { color: var(--text-secondary); }
.toolbar { display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: center; }
.search-input { min-width: 260px; }
</style>
