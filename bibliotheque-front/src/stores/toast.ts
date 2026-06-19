import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface ToastMessage {
  id: number
  type: 'success' | 'error' | 'info' | 'warning'
  message: string
}

let nextId = 1

export const useToastStore = defineStore('toast', () => {
  const toasts = ref<ToastMessage[]>([])

  function add(type: ToastMessage['type'], message: string): void {
    const id = nextId++
    toasts.value.push({ id, type, message })
    setTimeout(() => remove(id), 5000)
  }

  function remove(id: number): void {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  function success(message: string): void { add('success', message) }
  function error(message: string): void { add('error', message) }
  function info(message: string): void { add('info', message) }
  function warning(message: string): void { add('warning', message) }

  return { toasts, add, remove, success, error, info, warning }
})
