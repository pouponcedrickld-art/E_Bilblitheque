// Composable de libellés de statut
import { DEPOSIT_STATUS } from '@/Utils/constants'

const depositStatusLabels: Record<string, string> = {
  [DEPOSIT_STATUS.PENDING]: 'En attente',
  [DEPOSIT_STATUS.APPROVED_BY_MANAGER]: 'Validé par le responsable',
  [DEPOSIT_STATUS.REJECTED_BY_MANAGER]: 'Refusé par le responsable',
  [DEPOSIT_STATUS.SECOND_REVIEW]: 'Second avis demandé',
  [DEPOSIT_STATUS.APPROVED]: 'Approuvé',
  [DEPOSIT_STATUS.REJECTED]: 'Rejeté',
  [DEPOSIT_STATUS.PUBLISHED]: 'Publié',
}

const referenceStatusLabels: Record<string, string> = {
  draft: 'Brouillon',
  published: 'Publié',
  archived: 'Archivé',
}

const userStatusLabels: Record<string, string> = {
  active: 'Actif',
  inactive: 'Inactif',
  suspended: 'Suspendu',
  pending_validation: 'En attente de validation',
}

export function useStatusLabel() {
  function depositRequest(status: string): string {
    return depositStatusLabels[status] ?? status
  }

  function reference(status: string): string {
    return referenceStatusLabels[status] ?? status
  }

  function user(status: string): string {
    return userStatusLabels[status] ?? status
  }

  function severity(status: string): 'success' | 'warn' | 'danger' | 'info' | 'secondary' {
    const successStatuses = ['active', 'published', 'approved', 'approved_by_manager']
    const warnStatuses = ['pending', 'pending_validation', 'second_review', 'draft']
    const dangerStatuses = ['suspended', 'rejected', 'rejected_by_manager', 'archived', 'inactive']

    if (successStatuses.includes(status)) return 'success'
    if (warnStatuses.includes(status)) return 'warn'
    if (dangerStatuses.includes(status)) return 'danger'
    return 'info'
  }

  return { depositRequest, reference, user, severity }
}
