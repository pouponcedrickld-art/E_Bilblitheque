import { ref } from 'vue'
import http from '@/services/http'
import type { Reference } from '@/types'

/**
 * Composable de recherche – gère la requête textuelle, les filtres avancés,
 * l'état de chargement et les résultats pour les références.
 */
// Composable de recherche – requête textuelle, filtres avancés et résultats
export function useSearch() {
  /** Terme de recherche saisi par l'utilisateur */
  const query = ref('')

  /** Liste des résultats retournés par l'API */
  const results = ref<Reference[]>([])

  /** Indique si une requête est en cours */
  const loading = ref(false)

  /** Message d'erreur éventuel */
  const error = ref<string | null>(null)

  /** Filtres avancés (catégorie, type, langue, mot-clé, auteur) */
  const filters = ref({
    category_id: undefined as number | undefined,
    document_type_id: undefined as number | undefined,
    language: undefined as string | undefined,
    keyword: undefined as string | undefined,
    author: undefined as string | undefined,
  })

  /**
   * Lance une recherche avec les paramètres et filtres actuels.
   * Appelle l'API GET /references avec les paramètres non vides.
   */
  // Lance la recherche via l'API avec les paramètres et filtres actuels
  async function search() {
    loading.value = true
    error.value = null
    try {
      // Construit les paramètres de requête en omettant les valeurs vides
      const params: Record<string, any> = {}
      if (query.value) params.search = query.value
      if (filters.value.category_id) params.category_id = filters.value.category_id
      if (filters.value.document_type_id) params.document_type_id = filters.value.document_type_id
      if (filters.value.language) params.language = filters.value.language
      if (filters.value.keyword) params.keyword = filters.value.keyword
      if (filters.value.author) params.author = filters.value.author
      const res = await http.get('/references', { params })
      results.value = res.data?.data ?? res.data ?? []
    } catch (e: any) {
      error.value = e?.response?.data?.message || 'Une erreur est survenue lors de la recherche.'
      results.value = []
    } finally {
      loading.value = false
    }
  }

  /**
   * Réinitialise tous les champs de recherche, filtres et résultats.
   */
  // Réinitialise tous les champs, filtres et résultats
  function reset() {
    query.value = ''
    filters.value = { category_id: undefined, document_type_id: undefined, language: undefined, keyword: undefined, author: undefined }
    results.value = []
    error.value = null
  }

  return { query, results, loading, error, filters, search, reset }
}
