// Utilisateur du système
export interface User {
  id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  phone: string | null
  role: 'admin' | 'responsable_rh' | 'responsable_demande' | 'user'
  status: 'active' | 'inactive' | 'suspended'
  email_verified_at: string | null
  last_login_at: string | null
  references_count?: number
  created_at: string
  updated_at: string
}

// Catégorie de référence
export interface Category {
  id: number
  name: string
  slug: string
  description: string | null
  status: string
  references_count?: number
  created_at: string
  updated_at: string
}

// Auteur d'une référence
export interface Author {
  id: number
  first_name: string
  last_name: string
  full_name: string
  biography: string | null
  nationality: string | null
  references_count?: number
}

// Éditeur d'une référence
export interface Publisher {
  id: number
  name: string
  country: string | null
  references_count?: number
}

// Référence documentaire
export interface Reference {
  id: number
  title: string
  subtitle: string | null
  abstract: string | null
  isbn: string | null
  publication_year: number | null
  language: string | null
  document_type: string
  category?: Category
  publisher?: Publisher
  authors?: Author[]
  keywords?: Keyword[]
  cover_image: string | null
  cover_url: string | null
  file_path: string | null
  pages: number | null
  download_count: number
  view_count: number
  status: 'draft' | 'published' | 'archived'
  is_featured?: boolean
  created_at: string
  updated_at: string
}

// Demande de dépôt de document
export interface DepositRequest {
  id: number
  title: string
  description: string | null
  status: string
  applicant?: User
  created_at: string
}

// Mot-clé associé à une référence
export interface Keyword {
  id: number
  name: string
  slug: string
  references_count?: number
}

// Notification utilisateur
export interface Notification {
  id: number
  title: string
  message: string
  type: string
  is_read: boolean
  created_at: string
}
