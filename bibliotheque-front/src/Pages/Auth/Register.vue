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
    router.push('/login?registered=1')
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
    <div class="auth-bg-ornament" />
    <div class="auth-card">
      <div class="auth-seal">
        <div class="auth-seal-inner">
          <i class="pi pi-pencil" />
        </div>
      </div>

      <div class="auth-header">
        <h1 class="auth-title">Inscription</h1>
        <hr class="gold-rule-left" />
        <p class="auth-subtitle">Créez votre compte Bibliothèque Numérique</p>
      </div>

      <div v-if="errors.length" class="alert alert-error">
        <p v-for="(msg, i) in errors" :key="i">{{ msg }}</p>
      </div>

      <form @submit.prevent="handleSubmit" class="auth-form">
        <div class="field-row">
          <div class="field">
            <label for="first_name">Prénom</label>
            <div class="input-icon">
              <i class="pi pi-user" />
              <input id="first_name" v-model="form.first_name" type="text" class="input" required autocomplete="given-name" />
            </div>
          </div>
          <div class="field">
            <label for="last_name">Nom</label>
            <input id="last_name" v-model="form.last_name" type="text" class="input" required autocomplete="family-name" />
          </div>
        </div>

        <div class="field">
          <label for="email">Adresse email</label>
          <div class="input-icon">
            <i class="pi pi-envelope" />
            <input id="email" v-model="form.email" type="email" class="input" placeholder="vous@exemple.com" required autocomplete="email" />
          </div>
        </div>

        <div class="field">
          <label for="phone">Téléphone <span class="optional">(optionnel)</span></label>
          <div class="input-icon">
            <i class="pi pi-phone" />
            <input id="phone" v-model="form.phone" type="tel" class="input" placeholder="+221 XX XXX XX XX" autocomplete="tel" />
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="password">Mot de passe</label>
            <div class="input-icon">
              <i class="pi pi-lock" />
              <input id="password" v-model="form.password" type="password" class="input" placeholder="8 caractères min." required autocomplete="new-password" />
            </div>
          </div>
          <div class="field">
            <label for="password_confirmation">Confirmer</label>
            <input id="password_confirmation" v-model="form.password_confirmation" type="password" class="input" placeholder="Répétez" required autocomplete="new-password" />
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
          <i v-if="loading" class="pi pi-spin pi-spinner" />
          <i v-else class="pi pi-check" />
          {{ loading ? 'Inscription en cours...' : "S'inscrire" }}
        </button>
      </form>

      <hr class="gold-rule" />

      <p class="auth-footer">
        Déjà un compte ?
        <router-link to="/login" class="auth-link">Se connecter</router-link>
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
  background: var(--background);
  position: relative;
  overflow: hidden;
}

.auth-bg-ornament {
  position: absolute;
  inset: 0;
  opacity: 0.03;
  background-image:
    radial-gradient(circle at 20% 50%, var(--gold) 0%, transparent 50%),
    radial-gradient(circle at 80% 50%, var(--primary) 0%, transparent 50%);
  pointer-events: none;
}

.auth-card {
  width: 100%;
  max-width: 480px;
  background: var(--bg-card);
  border-radius: var(--radius-xl);
  border: 1px solid var(--border-gold);
  padding: 2.5rem 2rem;
  box-shadow:
    0 4px 24px rgba(44, 36, 32, 0.06),
    0 1px 4px rgba(44, 36, 32, 0.04);
  position: relative;
}

.auth-seal {
  display: flex;
  justify-content: center;
  margin-bottom: 1.25rem;
}

.auth-seal-inner {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--gold);
  box-shadow: 0 0 0 4px rgba(200, 164, 92, 0.15);
}
.auth-seal-inner i {
  font-size: 1.5rem;
  color: var(--gold-light);
}

.auth-header {
  text-align: center;
  margin-bottom: 1.5rem;
}

.auth-title {
  font-family: var(--font-serif);
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--primary);
  margin-bottom: 0.35rem;
}

.auth-subtitle {
  font-size: 0.85rem;
  color: var(--muted-foreground);
  line-height: 1.5;
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
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--foreground);
}

.optional {
  font-weight: 400;
  color: var(--muted-foreground);
  font-size: 0.75rem;
}

.btn-full {
  width: 100%;
  padding: 0.8rem 1.25rem;
  justify-content: center;
  margin-top: 0.5rem;
}

.auth-footer {
  text-align: center;
  font-size: 0.85rem;
  color: var(--muted-foreground);
}

.auth-link {
  color: var(--gold-dark);
  font-weight: 600;
  transition: color 0.2s;
}
.auth-link:hover {
  color: var(--gold);
  text-decoration: underline;
}

@media (max-width: 480px) {
  .auth-card {
    max-width: 100%;
    padding: 1.75rem 1.25rem;
  }
  .field-row {
    grid-template-columns: 1fr;
  }
}
</style>
