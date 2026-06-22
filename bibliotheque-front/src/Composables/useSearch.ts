import { ref } from 'vue'
import http from '@/services/http'
import type { Reference } from '@/types'

export function useSearch() {
  const query = ref('')
  const results = ref<Reference[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const filters = ref({
    category_id: undefined as number | undefined,
    document_type: undefined as string | undefined,
    language: undefined as string | undefined,
    keyword: undefined as string | undefined,
    author: undefined as string | undefined,
  })

  async function search() {
    loading.value = true
    error.value = null
    try {
      const params: Record<string, any> = {}
      if (query.value) params.search = query.value
      if (filters.value.category_id) params.category_id = filters.value.category_id
      if (filters.value.document_type) params.document_type = filters.value.document_type
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

  function reset() {
    query.value = ''
    filters.value = { category_id: undefined, document_type: undefined, language: undefined, keyword: undefined, author: undefined }
    results.value = []
    error.value = null
  }

  return { query, results, loading, error, filters, search, reset }
}
