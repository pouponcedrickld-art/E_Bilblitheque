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
        <h1>Inscription</h1>
        <p>Créez votre compte Bibliothèque Numérique</p>
      </div>

      <div v-if="errors.length" class="alert alert-error">
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
}

.auth-card {
  width: 100%;
  max-width: 460px;
  background: #fff;
  border-radius: 0.75rem;
  border: 1px solid var(--border);
  padding: 2rem;
}

.auth-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.auth-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.auth-header p {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.85rem;
  margin-bottom: 1rem;
}

.alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #b91c1c;
}

.alert p + p {
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
  color: var(--text-primary);
}

.input {
  padding: 0.6rem 0.85rem;
  border: 1px solid var(--border);
  border-radius: 0.375rem;
  font-size: 0.9rem;
  transition: border-color 0.15s;
  outline: none;
  background: #fff;
}

.input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.btn {
  padding: 0.65rem 1.25rem;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: var(--primary);
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  background: var(--primary-dark);
}

.btn-full {
  width: 100%;
}

.auth-footer {
  text-align: center;
  margin-top: 1.25rem;
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.link {
  color: var(--primary);
  font-weight: 600;
}

.link:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .field-row {
    grid-template-columns: 1fr;
  }
}
</style>
