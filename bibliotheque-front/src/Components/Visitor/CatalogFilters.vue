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

const languages = ['fr', 'en', 'ar', 'es', 'de', 'pt', 'it', 'ru', 'zh', 'ja']

function update(key: keyof Filters, value: any) {
  emit('update:modelValue', { ...props.modelValue, [key]: value })
}
</script>

<template>
  <div class="catalog-filters">
    <h3 class="filter-title">Filtres</h3>

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
        placeholder="Toutes les langues"
        clearable
        class="filter-control"
      />
    </div>

    <div class="filter-group">
      <label>Mot-clé</label>
      <InputText
        :modelValue="modelValue.keyword"
        @update:modelValue="update('keyword', $event)"
        placeholder="Rechercher par mot-clé"
        class="filter-control"
      />
    </div>
  </div>
</template>

<style scoped>
.catalog-filters {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
.filter-title {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.filter-group label {
  font-size: 0.8rem;
  font-weight: 500;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.filter-control {
  width: 100%;
}
</style>
