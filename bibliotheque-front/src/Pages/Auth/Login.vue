<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const justRegistered = computed(() => route.query.registered === '1')

const form = ref({
  email: '',
  password: '',
})

const loading = ref(false)
const errors = ref<string[]>([])

async function handleSubmit() {
  loading.value = true
  errors.value = []

  try {
    await authStore.login({
      email: form.value.email,
      password: form.value.password,
    })

    const redirect = (route.query.redirect as string) || authStore.getDashboardPath()
    router.push(redirect)
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
      errors.value = ['Erreur de connexion au serveur.']
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
          <i class="pi pi-book" />
        </div>
      </div>

      <div class="auth-header">
        <h1 class="auth-title">Connexion</h1>
        <hr class="gold-rule-left" />
        <p class="auth-subtitle">Accédez à votre espace Bibliothèque Numérique</p>
      </div>

      <div v-if="errors.length" class="alert alert-error">
        <p v-for="(msg, i) in errors" :key="i">{{ msg }}</p>
      </div>

      <div v-if="justRegistered" class="alert alert-success">
        Votre compte a été créé avec succès. Un administrateur doit valider votre compte avant que vous puissiez faire une demande de dépôt.
      </div>

      <form @submit.prevent="handleSubmit" class="auth-form">
        <div class="field">
          <label for="email">Adresse email</label>
          <div class="input-icon">
            <i class="pi pi-envelope" />
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
        </div>

        <div class="field">
          <label for="password">Mot de passe</label>
          <div class="input-icon">
            <i class="pi pi-lock" />
            <input
              id="password"
              v-model="form.password"
              type="password"
              class="input"
              placeholder="••••••••"
              required
              autocomplete="current-password"
            />
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full" :disabled="loading">
          <i v-if="loading" class="pi pi-spin pi-spinner" />
          <i v-else class="pi pi-sign-in" />
          {{ loading ? 'Connexion en cours...' : 'Se connecter' }}
        </button>
      </form>

      <hr class="gold-rule" />

      <p class="auth-footer">
        Pas encore de compte ?
        <router-link to="/register" class="auth-link">S'inscrire</router-link>
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
  max-width: 420px;
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
  gap: 1.15rem;
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

.btn-full {
  width: 100%;
  padding: 0.8rem 1.25rem;
  justify-content: center;
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
}
</style>
