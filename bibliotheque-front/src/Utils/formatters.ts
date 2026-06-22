// Formate une date en locale française
export function formatDate(date: string | Date) {
  return new Date(date).toLocaleDateString('fr-FR')
}
