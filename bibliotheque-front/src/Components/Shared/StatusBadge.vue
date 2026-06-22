// Badge de statut avec label et couleur dynamiques selon la valeur
<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ status: string }>()

const labels: Record<string, string> = {
  pending: 'En attente',
  pending_validation: 'En attente de validation',
  approved_by_manager: 'Validé (resp.)',
  rejected_by_manager: 'Refusé (resp.)',
  second_review: 'Second avis',
  published: 'Publié',
  rejected: 'Rejeté',
  draft: 'Brouillon',
  archived: 'Archivé',
  active: 'Actif',
  suspended: 'Suspendu',
  inactive: 'Inactif',
}

// Couleur du badge selon le statut
const severity = computed(() => {
  const map: Record<string, string> = {
    published: 'success',
    active: 'success',
    pending: 'warn',
    pending_validation: 'warn',
    approved_by_manager: 'info',
    second_review: 'info',
    rejected: 'danger',
    rejected_by_manager: 'danger',
    suspended: 'danger',
    inactive: 'danger',
    draft: 'contrast',
    archived: 'secondary',
  }
  return map[props.status] || 'contrast'
})

// Libellé traduit du statut
const label = computed(() => labels[props.status] || props.status)
</script>

<template>
  <span :class="'badge badge-' + severity">{{ label }}</span>
</template>

<style scoped>
.badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
}
.badge-success { background: #dcfce7; color: #166534; }
.badge-warn { background: #fef3c7; color: #92400e; }
.badge-info { background: #e0f2fe; color: #0369a1; }
.badge-danger { background: #fce4ec; color: #c62828; }
.badge-contrast { background: var(--muted); color: var(--foreground); }
.badge-secondary { background: var(--muted); color: var(--muted-foreground); }
</style>
