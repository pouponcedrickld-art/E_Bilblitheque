<script setup lang="ts">
import { ref, onMounted } from 'vue'
import http from '@/services/http'
import { useToast } from 'primevue/usetoast'

const toast = useToast()

interface ActivityLog {
  id: number
  user: { full_name: string; email: string } | null
  action: string
  target_table: string
  target_id: number | null
  created_at: string
}

const logs = ref<ActivityLog[]>([])
const loading = ref(true)

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

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

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

onMounted(fetchLogs)
</script>

<template>
  <div class="logs-page">
    <div class="page-header">
      <h1>Journal d'activité</h1>
      <Button label="Actualiser" icon="pi pi-refresh" severity="secondary" @click="fetchLogs" :loading="loading" />
    </div>

    <DataTable :value="logs" :loading="loading" stripedRows paginator :rows="25" :rowsPerPageOptions="[10, 25, 50, 100]" sortField="created_at" :sortOrder="-1">
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
</style>
