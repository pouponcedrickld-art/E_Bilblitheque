<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import { useAuthStore } from '@/stores/auth'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'

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

    <form @submit.prevent="handleSubmit" class="profile-card">
      <div class="profile-header">
        <div class="avatar-medallion">
          <svg viewBox="0 0 100 100" class="avatar-svg">
            <circle cx="50" cy="50" r="46" fill="none" stroke="var(--gold)" stroke-width="2" />
            <circle cx="50" cy="50" r="40" fill="none" stroke="var(--gold-dark)" stroke-width="1" />
            <text x="50" y="62" text-anchor="middle" font-size="36" fill="var(--primary)" font-family="Playfair Display, serif">
              {{ form.first_name?.charAt(0)?.toUpperCase() }}{{ form.last_name?.charAt(0)?.toUpperCase() }}
            </text>
          </svg>
        </div>
        <div>
          <h2 class="profile-name">{{ form.first_name }} {{ form.last_name }}</h2>
          <p class="profile-role">{{ authStore.user?.role ?? 'Utilisateur' }}</p>
        </div>
      </div>

      <div class="form-section">
        <div class="form-section-header">
          <span class="section-num">I</span>
          <span class="section-label">Fiche lecteur</span>
        </div>
        <div class="field-row">
          <div class="field">
            <label for="first_name">Prénom</label>
            <div class="input-wrap">
              <i class="pi pi-user input-icon"></i>
              <InputText id="first_name" v-model="form.first_name" class="input" required />
            </div>
          </div>
          <div class="field">
            <label for="last_name">Nom</label>
            <div class="input-wrap">
              <i class="pi pi-user input-icon"></i>
              <InputText id="last_name" v-model="form.last_name" class="input" required />
            </div>
          </div>
        </div>
        <div class="field">
          <label for="email">Email</label>
          <div class="input-wrap">
            <i class="pi pi-envelope input-icon"></i>
            <InputText id="email" v-model="form.email" type="email" class="input input-disabled" disabled />
          </div>
          <small class="field-hint">L'email ne peut pas être modifié.</small>
        </div>
        <div class="field">
          <label for="phone">Téléphone</label>
          <div class="input-wrap">
            <i class="pi pi-phone input-icon"></i>
            <InputText id="phone" v-model="form.phone" type="tel" class="input" placeholder="+221 XX XXX XX XX" />
          </div>
        </div>
      </div>

      <div class="form-section">
        <div class="form-section-header">
          <span class="section-num">II</span>
          <div class="section-header-row">
            <span class="section-label">Sécurité</span>
            <Button
              :icon="passwordForm.show ? 'pi pi-eye-slash' : 'pi pi-eye'"
              severity="secondary"
              text
              size="small"
              @click="passwordForm.show = !passwordForm.show"
              :label="passwordForm.show ? 'Masquer' : 'Modifier'"
              class="toggle-btn"
            />
          </div>
        </div>
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
              <label for="new_password_confirmation">Confirmer</label>
              <Password
                id="new_password_confirmation"
                v-model="passwordForm.new_password_confirmation"
                inputClass="input"
                :feedback="false"
                toggleMask
                placeholder="Retaper le mot de passe"
              />
            </div>
          </div>
        </template>
      </div>

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
  color: var(--muted-foreground);
  font-size: 0.9rem;
  margin-top: 0.25rem;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: var(--radius-lg);
  font-size: 0.85rem;
  margin-bottom: 1rem;
}
.alert-success {
  background: rgba(26, 58, 50, 0.06);
  border: 1px solid rgba(26, 58, 50, 0.2);
  color: var(--primary);
}
.alert-error {
  background: rgba(107, 45, 62, 0.08);
  border: 1px solid rgba(107, 45, 62, 0.25);
  color: var(--burgundy);
}

.profile-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
.profile-card:hover {
  border-color: var(--border-gold);
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border);
}
.avatar-medallion {
  width: 60px;
  height: 60px;
  flex-shrink: 0;
}
.avatar-svg {
  width: 100%;
  height: 100%;
}
.profile-name {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
}
.profile-role {
  font-size: 0.8rem;
  color: var(--muted-foreground);
  margin-top: 0.15rem;
  text-transform: capitalize;
}

.form-section {
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 1rem;
  transition: border-color 0.25s;
}
.form-section:hover {
  border-color: var(--border-gold);
}

.form-section-header {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--border);
}
.section-num {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--primary);
  color: var(--gold-light);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.65rem;
  font-weight: 700;
  font-family: var(--font-serif);
  flex-shrink: 0;
  border: 1.5px solid var(--gold-dark);
}
.section-label {
  font-size: 0.9rem;
  font-weight: 600;
}
.section-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex: 1;
}
.toggle-btn {
  font-size: 0.8rem !important;
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
.field-hint {
  font-size: 0.75rem;
  color: var(--muted-foreground);
}
.input-wrap {
  position: relative;
}
.input-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--gold);
  font-size: 0.8rem;
  z-index: 1;
}
.input-wrap .input {
  padding-left: 2.2rem !important;
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
  padding-top: 0.5rem;
}
.submit-btn {
  background: var(--primary) !important;
  border: none !important;
  border-radius: var(--radius-lg) !important;
  padding: 0.65rem 1.5rem !important;
  font-weight: 600 !important;
}
.submit-btn:hover {
  background: var(--primary-dark) !important;
  box-shadow: 0 4px 16px rgba(26,58,50,0.3) !important;
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
