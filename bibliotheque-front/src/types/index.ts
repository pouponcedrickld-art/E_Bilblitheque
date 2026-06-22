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

export interface Author {
  id: number
  first_name: string
  last_name: string
  full_name: string
  biography: string | null
  nationality: string | null
  references_count?: number
}

export interface Publisher {
  id: number
  name: string
  country: string | null
  references_count?: number
}

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
  file_path: string | null
  pages: number | null
  download_count: number
  view_count: number
  status: 'draft' | 'published' | 'archived'
  is_featured?: boolean
  created_at: string
  updated_at: string
}

export interface DepositRequest {
  id: number
  title: string
  description: string | null
  status: string
  applicant?: User
  created_at: string
}

export interface Keyword {
  id: number
  name: string
  slug: string
  references_count?: number
}

export interface Notification {
  id: number
  title: string
  message: string
  type: string
  is_read: boolean
  created_at: string
}
