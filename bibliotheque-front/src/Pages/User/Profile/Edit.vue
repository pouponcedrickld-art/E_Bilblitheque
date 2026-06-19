<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import { useAuthStore } from '@/stores/auth'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Divider from 'primevue/divider'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
  show: false,
})

const saving = ref(false)
const error = ref('')
const success = ref('')

onMounted(() => {
  if (authStore.user) {
    form.value.first_name = authStore.user.first_name
    form.value.last_name = authStore.user.last_name
    form.value.email = authStore.user.email
    form.value.phone = authStore.user.phone ?? ''
  }
})

const passwordErrors = {
  minLength: (v: string) => v.length >= 6 || '6 caractères minimum',
  hasUpper: (v: string) => /[A-Z]/.test(v) || 'Une majuscule requise',
  hasDigit: (v: string) => /[0-9]/.test(v) || 'Un chiffre requis',
  match: (v: string) => v === passwordForm.new_password || 'Les mots de passe ne correspondent pas',
}

function validatePassword(): string[] {
  const errs: string[] = []
  if (!passwordForm.new_password) return errs
  for (const check of [passwordErrors.minLength, passwordErrors.hasUpper, passwordErrors.hasDigit]) {
    const r = check(passwordForm.new_password)
    if (r !== true) errs.push(r)
  }
  if (passwordForm.new_password_confirmation) {
    const r = passwordErrors.match(passwordForm.new_password_confirmation)
    if (r !== true) errs.push(r)
  }
  return errs
}

async function handleSubmit() {
  saving.value = true
  error.value = ''
  success.value = ''

  const payload: Record<string, any> = {
    first_name: form.value.first_name,
    last_name: form.value.last_name,
    phone: form.value.phone || null,
  }

  if (passwordForm.current_password || passwordForm.new_password) {
    const pwdErrs = validatePassword()
    if (pwdErrs.length) {
      error.value = pwdErrs.join(' • ')
      saving.value = false
      return
    }
    payload.current_password = passwordForm.current_password
    payload.new_password = passwordForm.new_password
    payload.new_password_confirmation = passwordForm.new_password_confirmation
  }

  try {
    const res = await http.put('/user/profile', payload)
    if (res.data?.user) {
      authStore.user = res.data.user
    }
    success.value = res.data?.message || 'Profil mis à jour avec succès.'
    passwordForm.current_password = ''
    passwordForm.new_password = ''
    passwordForm.new_password_confirmation = ''
  } catch (err: any) {
    if (err.response?.data?.errors) {
      const msgs: string[] = []
      Object.values(err.response.data.errors).forEach((e: any) => {
        msgs.push(...(Array.isArray(e) ? e : [e]))
      })
      error.value = msgs.join(', ')
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = "Erreur lors de la mise à jour."
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="page">
    <div class="page-header">
      <h1>Mon profil</h1>
      <p>Modifiez vos informations personnelles et votre mot de passe</p>
    </div>

    <Transition name="fade">
      <div v-if="success" class="alert alert-success">{{ success }}</div>
    </Transition>
    <Transition name="fade">
      <div v-if="error" class="alert alert-error">{{ error }}</div>
    </Transition>

    <form @submit.prevent="handleSubmit" class="form-card">
      <h2 class="section-title">Informations personnelles</h2>

      <div class="field-row">
        <div class="field">
          <label for="first_name">Prénom</label>
          <InputText id="first_name" v-model="form.first_name" class="input" required />
        </div>
        <div class="field">
          <label for="last_name">Nom</label>
          <InputText id="last_name" v-model="form.last_name" class="input" required />
        </div>
      </div>

      <div class="field">
        <label for="email">Email</label>
        <InputText id="email" v-model="form.email" type="email" class="input input-disabled" disabled />
        <small class="field-hint">L'email ne peut pas être modifié.</small>
      </div>

      <div class="field">
        <label for="phone">Téléphone</label>
        <InputText id="phone" v-model="form.phone" type="tel" class="input" placeholder="+221 XX XXX XX XX" />
      </div>

      <Divider />

      <h2 class="section-title">
        Mot de passe
        <Button
          :icon="passwordForm.show ? 'pi pi-eye-slash' : 'pi pi-eye'"
          class="p-button-text p-button-sm"
          @click="passwordForm.show = !passwordForm.show"
          :label="passwordForm.show ? 'Masquer' : 'Modifier'"
        />
      </h2>

      <template v-if="passwordForm.show">
        <div class="field">
          <label for="current_password">Mot de passe actuel</label>
          <Password
            id="current_password"
            v-model="passwordForm.current_password"
            inputClass="input"
            :feedback="false"
            toggleMask
            placeholder="Votre mot de passe actuel"
          />
        </div>

        <div class="field-row">
          <div class="field">
            <label for="new_password">Nouveau mot de passe</label>
            <Password
              id="new_password"
              v-model="passwordForm.new_password"
              inputClass="input"
              :promptLabel="'Choisissez un mot de passe'"
              :weakLabel="'Trop simple'"
              :mediumLabel="'Moyen'"
              :strongLabel="'Fort'"
              toggleMask
              placeholder="6+ car., 1 majuscule, 1 chiffre"
            />
            <small class="field-hint">Minimum 6 caractères, une majuscule, un chiffre.</small>
          </div>
          <div class="field">
            <label for="new_password_confirmation">Confirmer le mot de passe</label>
            <Password
              id="new_password_confirmation"
              v-model="passwordForm.new_password_confirmation"
              inputClass="input"
              :feedback="false"
              toggleMask
              placeholder="Retaper le nouveau mot de passe"
            />
          </div>
        </div>
      </template>

      <Divider />

      <div class="form-actions">
        <Button type="submit" :loading="saving" :disabled="saving" label="Enregistrer les modifications" icon="pi pi-check" class="submit-btn" />
      </div>
    </form>
  </div>
</template>

<style scoped>
.page {
  max-width: 680px;
}

.page-header {
  margin-bottom: 1.5rem;
}

.page-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
}

.page-header p {
  color: var(--text-secondary);
  font-size: 0.9rem;
  margin-top: 0.25rem;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  margin-bottom: 1rem;
}

.alert-success {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #166534;
}

.alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #b91c1c;
}

.form-card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 0.5rem;
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

.field-hint {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.input {
  width: 100%;
}

.input-disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-actions {
  display: flex;
  gap: 0.75rem;
}

.submit-btn {
  align-self: flex-start;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

@media (max-width: 480px) {
  .field-row { grid-template-columns: 1fr; }
}
</style>
