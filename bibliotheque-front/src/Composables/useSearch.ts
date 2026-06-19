import { ref } from 'vue'
import http from '@/services/http'
import type { Reference } from '@/types'

export function useSearch() {
  const query = ref('')
  const results = ref<Reference[]>([])
  const loading = ref(false)
  const filters = ref({
    category_id: undefined as number | undefined,
    document_type: undefined as string | undefined,
    language: undefined as string | undefined,
    keyword: undefined as string | undefined,
  })

  async function search() {
    loading.value = true
    try {
      const params: Record<string, any> = {}
      if (query.value) params.search = query.value
      if (filters.value.category_id) params.category_id = filters.value.category_id
      if (filters.value.document_type) params.document_type = filters.value.document_type
      if (filters.value.language) params.language = filters.value.language
      if (filters.value.keyword) params.keyword = filters.value.keyword
      const res = await http.get('/references', { params })
      results.value = res.data?.data ?? res.data ?? []
    } finally {
      loading.value = false
    }
  }

  function reset() {
    query.value = ''
    filters.value = { category_id: undefined, document_type: undefined, language: undefined, keyword: undefined }
    results.value = []
  }

  return { query, results, loading, filters, search, reset }
}
