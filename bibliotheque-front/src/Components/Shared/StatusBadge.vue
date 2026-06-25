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

const config = computed(() => {
  const map: Record<string, { bg: string; text: string; dot: string }> = {
    published:       { bg: '#F0FDF4', text: '#166534', dot: '#16A34A' },
    active:          { bg: '#F0FDF4', text: '#166534', dot: '#16A34A' },
    pending:         { bg: '#FFF8ED', text: '#92400E', dot: '#F59E0B' },
    pending_validation: { bg: '#FFF8ED', text: '#92400E', dot: '#F59E0B' },
    approved_by_manager: { bg: '#F5F0E8', text: '#8B6F47', dot: '#C8A45C' },
    second_review:   { bg: '#F5F0E8', text: '#6B2D3E', dot: '#C8A45C' },
    rejected:        { bg: '#FDF2F2', text: '#A83232', dot: '#DC2626' },
    rejected_by_manager: { bg: '#FDF2F2', text: '#A83232', dot: '#DC2626' },
    suspended:       { bg: '#FDF2F2', text: '#A83232', dot: '#DC2626' },
    inactive:        { bg: '#EDE8DF', text: '#8B8178', dot: '#8B8178' },
    draft:           { bg: '#EDE8DF', text: '#8B8178', dot: '#8B8178' },
    archived:        { bg: '#EDE8DF', text: '#8B8178', dot: '#8B8178' },
  }
  return map[props.status] || map.pending
})

const label = computed(() => labels[props.status] || props.status)
</script>

<template>
  <span
    class="seal-badge"
    :style="{ background: config.bg, color: config.text, borderColor: config.dot + '40' }"
  >
    <span class="seal-dot" :style="{ background: config.dot }" />
    {{ label }}
  </span>
</template>

<style scoped>
.seal-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid;
  position: relative;
}

.seal-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}
</style>
