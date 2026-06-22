<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const errors = ref<string[]>([])

async function handleSubmit() {
  loading.value = true
  errors.value = []

  try {
    await authStore.register({ ...form.value })
    router.push(authStore.getDashboardPath())
  } catch (err: any) {
    if (err.response?.data?.errors) {
      const msgs: string[] = []
      Object.values(err.response.data.errors).forEach((e: any) => {
        msgs.push(...(Array.isArray(e) ? e : [e]))
      })
      errors.value = msgs
    } else if (err.response?.data?.message) {
      errors.value = [err.response.data.message]
    } else {
      errors.value = ['Erreur d\'inscription.']
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-icon">
          <i class="pi pi-book"></i>
        </div>
        <h1 class="auth-title">Inscription</h1>
        <p class="auth-subtitle">Créez votre compte Bibliothèque Numérique</p>
      </div>

      <div v-if="errors.length" class="auth-alert">
        <p v-for="(msg, i) in errors" :key="i">{{ msg }}</p>
      </div>

      <form @submit.prevent="handleSubmit" class="auth-form">
        <div class="field-row">
          <div class="field">
            <label for="first_name">Prénom</label>
            <input
              id="first_name"
              v-model="form.first_name"
              type="text"
              class="input"
              required
              autocomplete="given-name"
            />
          </div>
          <div class="field">
            <label for="last_name">Nom</label>
            <input
              id="last_name"
              v-model="form.last_name"
              type="text"
              class="input"
              required
              autocomplete="family-name"
            />
          </div>
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="input"
            placeholder="vous@exemple.com"
            required
            autocomplete="email"
          />
        </div>

        <div class="field">
          <label for="phone">Téléphone (optionnel)</label>
          <input
            id="phone"
            v-model="form.phone"
            type="tel"
            class="input"
            placeholder="+221 XX XXX XX XX"
            autocomplete="tel"
          />
        </div>

        <div class="field">
          <label for="password">Mot de passe</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="input"
            placeholder="8 caractères minimum"
            required
            autocomplete="new-password"
          />
        </div>

        <div class="field">
          <label for="password_confirmation">Confirmer le mot de passe</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="input"
            placeholder="Répétez le mot de passe"
            required
            autocomplete="new-password"
          />
        </div>

        <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
          {{ loading ? 'Inscription en cours...' : 'S\'inscrire' }}
        </button>
      </form>

      <p class="auth-footer">
        Déjà un compte ?
        <router-link to="/login" class="link">Se connecter</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: calc(100vh - var(--navbar-height) - 3rem);
  padding: 1rem;
  background: var(--background, #F2F2F7);
}

.auth-card {
  width: 100%;
  max-width: 460px;
  background: #fff;
  border-radius: var(--radius-xl, 1rem);
  border: 1px solid var(--border);
  padding: 2rem;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
}

.auth-icon {
  display: flex;
  justify-content: center;
  margin-bottom: 1rem;
}

.auth-icon i {
  font-size: 2.5rem;
  color: var(--primary, #1B4332);
}

.auth-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.auth-title {
  font-family: var(--font-serif);
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--primary, #1B4332);
  margin-bottom: 0.35rem;
}

.auth-subtitle {
  font-size: 0.875rem;
  color: var(--foreground, #666);
}

.auth-alert {
  padding: 0.75rem 1rem;
  border-radius: var(--radius-xl, 1rem);
  font-size: 0.85rem;
  margin-bottom: 1rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #b91c1c;
}

.auth-alert p + p {
  margin-top: 0.25rem;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.field-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.field label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--foreground, #333);
}

.input {
  padding: 0.75rem 1rem;
  border: 1px solid var(--border);
  border-radius: var(--radius-xl, 1rem);
  font-size: 0.9rem;
  transition: border-color 0.2s;
  outline: none;
  background: var(--muted, #F2F2F7);
  font-family: inherit;
}

.input::placeholder {
  color: #999;
}

.input:focus {
  border-color: var(--primary, #1B4332);
  box-shadow: 0 0 0 3px rgba(27, 67, 50, 0.12);
}

.btn {
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: var(--radius-xl, 1rem);
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--primary, #1B4332);
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  background: #143026;
}

.btn-full {
  width: 100%;
}

.auth-footer {
  text-align: center;
  margin-top: 1.25rem;
  font-size: 0.875rem;
  color: var(--foreground, #666);
}

.link {
  color: var(--primary, #1B4332);
  font-weight: 600;
}

.link:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .auth-card {
    max-width: 100%;
  }

  .field-row {
    grid-template-columns: 1fr;
  }
}
</style>
