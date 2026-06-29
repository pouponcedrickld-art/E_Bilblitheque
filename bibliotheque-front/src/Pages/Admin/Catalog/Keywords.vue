<script setup lang="ts">
// Gestion des mots-clés du catalogue
import { ref, onMounted } from 'vue'
import http from '@/services/http'
import { useToastStore } from '@/stores/toast'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Chip from 'primevue/chip'
import { useConfirm } from 'primevue/useconfirm'
import Message from 'primevue/message'

const toastStore = useToastStore()
const confirm = useConfirm()

// Interface représentant un mot-clé
interface Keyword {
  id: number
  name: string
  slug: string
  references_count?: number
}

// Liste des mots-clés et état
const keywords = ref<Keyword[]>([])
const loading = ref(false)
const newKeyword = ref('')

// Récupère tous les mots-clés
async function fetchKeywords() {
  loading.value = true
  try {
    const res = await http.get('/keywords')
    keywords.value = res.data?.data ?? res.data ?? []
  } catch {
    keywords.value = []
  } finally {
    loading.value = false
  }
}

// Ajoute un nouveau mot-clé
async function addKeyword() {
  const name = newKeyword.value.trim()
  if (!name) return

  if (keywords.value.some((k) => k.name.toLowerCase() === name.toLowerCase())) {
    toastStore.warning('Ce mot-clé existe déjà.')
    newKeyword.value = ''
    return
  }

  try {
    const res = await http.post('/keywords', { name })
    keywords.value.push(res.data)
    keywords.value.sort((a, b) => a.name.localeCompare(b.name))
    newKeyword.value = ''
    toastStore.success(`Mot-clé "${name}" ajouté.`)
  } catch (err: any) {
    toastStore.error(err.response?.data?.message || 'Erreur lors de l\'ajout.')
  }
}

// Demande confirmation avant suppression
function confirmRemove(kw: Keyword) {
  confirm.require({
    message: `Supprimer le mot-clé "${kw.name}" ?${kw.references_count ? ` (${kw.references_count} référence(s) liée(s))` : ''}`,
    header: 'Confirmation',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    accept: () => removeKeyword(kw),
  })
}

// Supprime un mot-clé
async function removeKeyword(kw: Keyword) {
  try {
    await http.delete(`/keywords/${kw.id}`)
    keywords.value = keywords.value.filter((k) => k.id !== kw.id)
    toastStore.success(`Mot-clé "${kw.name}" supprimé.`)
  } catch {
    toastStore.error('Erreur lors de la suppression.')
  }
}

// Charge les mots-clés au montage
onMounted(fetchKeywords)
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Gestion des mots-clés</h1>
      <Button icon="pi pi-refresh" label="Actualiser" severity="secondary" @click="fetchKeywords" />
    </div>

    <Message severity="info" :closable="false" class="mb-3">
      Gérez la liste des mots-clés utilisés dans le catalogue. Les mots-clés peuvent être attachés aux références lors de leur création ou modification.
    </Message>

    <div class="add-form">
      <InputText
        v-model="newKeyword"
        placeholder="Nouveau mot-clé..."
        class="keyword-input"
        @keyup.enter="addKeyword"
      />
      <Button icon="pi pi-plus" label="Ajouter" @click="addKeyword" :disabled="!newKeyword.trim()" />
    </div>

    <div v-if="loading" class="loading">Chargement des mots-clés...</div>

    <div v-else-if="!keywords.length" class="empty">Aucun mot-clé trouvé.</div>

    <div v-else class="keywords-list">
      <div v-for="kw in keywords" :key="kw.id" class="keyword-item">
        <Chip :label="kw.name" />
        <span v-if="kw.references_count" class="ref-count">{{ kw.references_count }} réf.</span>
        <Button icon="pi pi-times" severity="danger" text size="small" @click="confirmRemove(kw)" />
      </div>
    </div>
  </div>

</template>

<style scoped>
.page { max-width: 800px; margin: 0 auto; padding: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.4rem; font-weight: 700; }
.mb-3 { margin-bottom: 1rem; }
.add-form { display: flex; gap: 0.75rem; margin-bottom: 1.5rem; }
.keyword-input { flex: 1; }
.loading { text-align: center; padding: 2rem; color: var(--text-secondary); }
.empty { text-align: center; padding: 2rem; color: var(--text-secondary); }
.keywords-list { display: flex; flex-wrap: wrap; gap: 0.75rem; }
.keyword-item { display: flex; align-items: center; gap: 0.35rem; }
.ref-count { font-size: 0.75rem; color: var(--text-secondary); }
</style>
