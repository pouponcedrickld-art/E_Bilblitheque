<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useSearch } from '@/composables/useSearch'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Message from 'primevue/message'

const router = useRouter()
const { query, results, loading, search } = useSearch()

function getTypeIcon(type: string): string {
  const icons: Record<string, string> = {
    livre: '📖', memoire: '📄', these: '🎓', article: '📰',
    revue: '📓', rapport: '📑', guide: '📋',
  }
  return icons[type] || '📄'
}

function viewDetail(id: number) {
  router.push(`/references/${id}`)
}
</script>

<template>
  <div class="simple-search">
    <div class="search-bar">
      <InputText v-model="query" placeholder="Rechercher un titre, un auteur..." class="search-input" @keyup.enter="search" />
      <Button label="Rechercher" icon="pi pi-search" @click="search" :loading="loading" />
    </div>

    <div v-if="loading" class="message-box">
      <Message severity="info">Recherche en cours...</Message>
    </div>

    <div v-else-if="results.length === 0 && query" class="message-box">
      <Message severity="warn">Aucun résultat trouvé pour "{{ query }}".</Message>
    </div>

    <div v-else-if="results.length > 0" class="result-count">
      {{ results.length }} résultat(s) trouvé(s)
    </div>

    <div v-if="results.length > 0" class="grid">
      <div
        v-for="ref in results"
        :key="ref.id"
        class="card"
        @click="viewDetail(ref.id)"
      >
        <div class="card-icon">{{ getTypeIcon(ref.document_type) }}</div>
        <div class="card-body">
          <span class="card-badge">{{ ref.document_type }}</span>
          <h3 class="card-title">{{ ref.title }}</h3>
          <p v-if="ref.authors?.length" class="card-authors">
            {{ ref.authors.map(a => a.full_name).join(', ') }}
          </p>
          <div class="card-meta">
            <span v-if="ref.publication_year">{{ ref.publication_year }}</span>
            <span v-if="ref.language">{{ ref.language }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.simple-search {
  max-width: 1200px;
  margin: 0 auto;
}

.search-bar {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.search-input {
  flex: 1;
}

.message-box {
  margin-bottom: 1rem;
}

.result-count {
  font-size: 0.85rem;
  color: var(--text-secondary);
  margin-bottom: 1rem;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1.25rem;
  display: flex;
  gap: 1rem;
  cursor: pointer;
  transition: all 0.15s;
}

.card:hover {
  border-color: var(--primary);
  box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
  transform: translateY(-1px);
}

.card-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.card-body {
  flex: 1;
  min-width: 0;
}

.card-badge {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  background: #eff6ff;
  color: var(--primary);
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  margin-bottom: 0.35rem;
}

.card-title {
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.35;
  margin-bottom: 0.35rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-authors {
  font-size: 0.8rem;
  color: var(--text-secondary);
  margin-bottom: 0.35rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card-meta {
  display: flex;
  gap: 0.75rem;
  font-size: 0.75rem;
  color: var(--text-secondary);
}
</style>
