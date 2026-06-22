// Store Pinia pour les notifications toast
import { defineStore } from 'pinia'
import { ref } from 'vue'

// Interface d'un message toast
export interface ToastMessage {
  id: number
  type: 'success' | 'error' | 'info' | 'warning'
  message: string
}

// Compteur d'identifiants pour les toasts
let nextId = 1

export const useToastStore = defineStore('toast', () => {
  // Liste des toasts affichés
  const toasts = ref<ToastMessage[]>([])

  // Ajoute un toast et le supprime après 5 secondes
  function add(type: ToastMessage['type'], message: string): void {
    const id = nextId++
    toasts.value.push({ id, type, message })
    setTimeout(() => remove(id), 5000)
  }

  // Supprime un toast par son identifiant
  function remove(id: number): void {
    toasts.value = toasts.value.filter((t) => t.id !== id)
  }

  // Méthodes courtes pour chaque type de toast
  function success(message: string): void { add('success', message) }
  function error(message: string): void { add('error', message) }
  function info(message: string): void { add('info', message) }
  function warning(message: string): void { add('warning', message) }

  return { toasts, add, remove, success, error, info, warning }
})
