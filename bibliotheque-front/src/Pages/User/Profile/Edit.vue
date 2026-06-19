<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import { useAuthStore } from '@/stores/auth'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
})

const loading = ref(false)
const saving = ref(false)
const error = ref('')
const success = ref(false)

onMounted(() => {
  if (authStore.user) {
    form.value.first_name = authStore.user.first_name
    form.value.last_name = authStore.user.last_name
    form.value.email = authStore.user.email
    form.value.phone = authStore.user.phone ?? ''
  }
})

async function handleSubmit() {
  saving.value = true
  error.value = ''
  success.value = false

  try {
    const res = await http.put('/user/profile', form.value)
    authStore.user = res.data?.data ?? res.data ?? authStore.user
    success.value = true
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
      error.value = "Erreur lors de la mise à jour du profil."
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
      <p>Modifiez vos informations personnelles</p>
    </div>

    <div v-if="success" class="alert alert-success">Profil mis à jour avec succès.</div>
    <div v-if="error" class="alert alert-error">{{ error }}</div>

    <form @submit.prevent="handleSubmit" class="form-card">
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
        <InputText id="email" v-model="form.email" type="email" class="input" required />
      </div>

      <div class="field">
        <label for="phone">Téléphone</label>
        <InputText id="phone" v-model="form.phone" type="tel" class="input" placeholder="+221 XX XXX XX XX" />
      </div>

      <Button type="submit" :loading="saving" :disabled="saving" label="Enregistrer" icon="pi pi-check" class="submit-btn" />
    </form>
  </div>
</template>

<style scoped>
.page {
  max-width: 600px;
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
  border-radius: 0.375rem;
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
  width: 100%;
}

.submit-btn {
  align-self: flex-start;
}

@media (max-width: 480px) {
  .field-row {
    grid-template-columns: 1fr;
  }
}
</style>
