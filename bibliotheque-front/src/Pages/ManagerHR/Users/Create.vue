<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/services/http'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Select from 'primevue/select'

const router = useRouter()
const toast = useToast()

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  role: 'user',
  status: 'active',
})

const submitting = ref(false)
const errors = ref<Record<string, string[]>>({})

async function submit() {
  submitting.value = true
  errors.value = {}
  try {
    await http.post('/users', form.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Utilisateur créé avec succès', life: 3000 })
    router.push('/rh/users')
  } catch (err: any) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      toast.add({ severity: 'error', summary: 'Erreur', detail: "Impossible de créer l'utilisateur", life: 3000 })
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="form-page">
    <div class="page-header">
      <h1>Nouvel utilisateur</h1>
      <Button label="Retour" icon="pi pi-arrow-left" severity="secondary" @click="router.push('/rh/users')" />
    </div>

    <Card>
      <template #content>
        <form @submit.prevent="submit" class="user-form">
          <div class="form-grid">
            <div class="field">
              <label for="first_name">Prénom</label>
              <InputText id="first_name" v-model="form.first_name" :class="{ 'p-invalid': errors.first_name }" />
              <small v-if="errors.first_name" class="p-error">{{ errors.first_name[0] }}</small>
            </div>
            <div class="field">
              <label for="last_name">Nom</label>
              <InputText id="last_name" v-model="form.last_name" :class="{ 'p-invalid': errors.last_name }" />
              <small v-if="errors.last_name" class="p-error">{{ errors.last_name[0] }}</small>
            </div>
            <div class="field">
              <label for="email">Email</label>
              <InputText id="email" v-model="form.email" type="email" :class="{ 'p-invalid': errors.email }" />
              <small v-if="errors.email" class="p-error">{{ errors.email[0] }}</small>
            </div>
            <div class="field">
              <label for="phone">Téléphone</label>
              <InputText id="phone" v-model="form.phone" :class="{ 'p-invalid': errors.phone }" />
              <small v-if="errors.phone" class="p-error">{{ errors.phone[0] }}</small>
            </div>
            <div class="field">
              <label for="password">Mot de passe</label>
              <Password id="password" v-model="form.password" :feedback="false" toggleMask :class="{ 'p-invalid': errors.password }" />
              <small v-if="errors.password" class="p-error">{{ errors.password[0] }}</small>
            </div>
            <div class="field">
              <label for="password_confirmation">Confirmer le mot de passe</label>
              <Password id="password_confirmation" v-model="form.password_confirmation" :feedback="false" toggleMask :class="{ 'p-invalid': errors.password_confirmation }" />
              <small v-if="errors.password_confirmation" class="p-error">{{ errors.password_confirmation[0] }}</small>
            </div>
            <div class="field">
              <label for="role">Rôle</label>
              <Select id="role" v-model="form.role" :options="['user', 'responsable_demande']" :class="{ 'p-invalid': errors.role }" />
              <small v-if="errors.role" class="p-error">{{ errors.role[0] }}</small>
            </div>
            <div class="field">
              <label for="status">Statut</label>
              <Select id="status" v-model="form.status" :options="['active', 'inactive']" :class="{ 'p-invalid': errors.status }" />
              <small v-if="errors.status" class="p-error">{{ errors.status[0] }}</small>
            </div>
          </div>

          <div class="form-actions">
            <Button label="Annuler" severity="secondary" @click="router.push('/rh/users')" />
            <Button label="Créer l'utilisateur" icon="pi pi-check" type="submit" :loading="submitting" />
          </div>
        </form>
      </template>
    </Card>
  </div>
</template>

<style scoped>
.form-page { max-width: 720px; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field label { font-size: 0.875rem; font-weight: 500; color: var(--text-primary); }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border); }
</style>
