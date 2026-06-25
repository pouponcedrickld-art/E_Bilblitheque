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
  has_pending_suspension?: boolean
  created_at: string
  updated_at: string
}

// Demande de suspension d'un utilisateur (RH → Admin)
export interface SuspensionRequest {
  id: number
  user_id: number
  user?: User
  requested_by: number
  requester?: User
  reason: string
  status: 'pending' | 'approved' | 'rejected'
  reviewed_by?: number | null
  reviewer?: User | null
  reviewed_at?: string | null
  rejection_reason?: string | null
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

// Type de document
export interface DocumentType {
  id: number
  name: string
  label: string
  description: string | null
}

// Éditeur d'une référence
export interface Publisher {
  id: number
  name: string
  description: string | null
  country: string | null
  website: string | null
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
  document_type_id: number | null
  document_type?: DocumentType | null
  category?: Category
  publisher?: Publisher
  authors?: Author[]
  keywords?: Keyword[]
  cover_image: string | null
  cover_url: string | null
  file_path: string | null
  file_size: number | null
  pages: number | null
  download_count: number
  view_count: number
  status: 'draft' | 'published' | 'archived'
  is_featured?: boolean
  allow_download: boolean
  created_at: string
  updated_at: string
}

// Demande de dépôt de document
export interface DepositRequest {
  id: number
  title: string
  subtitle: string | null
  abstract: string | null
  description: string | null
  isbn: string | null
  publication_year: number | null
  language: string | null
  document_type_id: number | null
  document_type?: DocumentType | null
  category_id: number | null
  category?: Category
  publisher_id: number | null
  publisher?: Publisher
  pages: number | null
  proposed_file: string | null
  proposed_file_url?: string
  allow_download: boolean
  cover_image: string | null
  cover_url?: string
  status: string
  applicant?: User
  assigned_manager?: User
  reviews?: any[]
  created_at: string
  updated_at: string
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
