<script setup lang="ts">
import type { Category } from '@/types'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'

interface Filters {
  category_id?: number | null
  document_type_id?: number | null
  language?: string | null
  keyword?: string | null
}

const props = defineProps<{
  modelValue: Filters
  categories: Category[]
  documentTypes: { id: number; name: string; label: string }[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: Filters]
}>()

const languages = [
  { label: 'Français', value: 'fr' },
  { label: 'Anglais', value: 'en' },
  { label: 'Arabe', value: 'ar' },
  { label: 'Espagnol', value: 'es' },
  { label: 'Allemand', value: 'de' },
  { label: 'Portugais', value: 'pt' },
  { label: 'Italien', value: 'it' },
  { label: 'Russe', value: 'ru' },
  { label: 'Chinois', value: 'zh' },
  { label: 'Japonais', value: 'ja' },
]

function update(key: keyof Filters, value: any) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}
</script>

<template>
  <div class="catalog-filters">
    <div class="filters-header">
      <i class="pi pi-filter" />
      <span>Filtres</span>
    </div>
    <hr class="gold-rule" />

    <div class="filter-group">
      <label>Catégorie</label>
      <Select
        :modelValue="modelValue.category_id"
        @update:modelValue="update('category_id', $event)"
        :options="categories"
        optionLabel="name"
        optionValue="id"
        placeholder="Toutes les catégories"
        clearable
        class="filter-control"
      />
    </div>

    <div class="filter-group">
      <label>Type de document</label>
      <Select
        :modelValue="modelValue.document_type_id"
        @update:modelValue="update('document_type_id', $event)"
        :options="documentTypes"
        optionLabel="label"
        optionValue="id"
        placeholder="Tous les types"
        clearable
        class="filter-control"
      />
    </div>

    <div class="filter-group">
      <label>Langue</label>
      <Select
        :modelValue="modelValue.language"
        @update:modelValue="update('language', $event)"
        :options="languages"
        optionLabel="label"
        optionValue="value"
        placeholder="Toutes les langues"
        clearable
        class="filter-control"
      />
    </div>

    <div class="filter-group">
      <label>Mot-clé</label>
      <div class="input-icon">
        <i class="pi pi-search" />
        <InputText
          :modelValue="modelValue.keyword"
          @update:modelValue="update('keyword', $event)"
          placeholder="Rechercher par mot-clé"
          class="filter-control input"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.catalog-filters {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.filters-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-family: var(--font-serif);
  font-size: 1rem;
  font-weight: 600;
  color: var(--foreground);
}
.filters-header i {
  color: var(--gold-dark);
  font-size: 0.9rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.filter-group label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--muted-foreground);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.filter-control {
  width: 100%;
}

:deep(.p-select) {
  border-radius: var(--radius-lg);
  border-color: var(--border);
  background: var(--input-background);
}

:deep(.p-select:focus-within) {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px var(--gold-glow);
}
</style>
