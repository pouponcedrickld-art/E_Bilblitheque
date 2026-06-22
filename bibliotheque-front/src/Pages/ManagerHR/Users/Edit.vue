<script setup lang="ts">
// Modification d'un utilisateur (RH / Admin)
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import http from '@/services/http'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'

const router = useRouter()
const route = useRoute()
const toast = useToast()
const authStore = useAuthStore()

// Chemin de redirection selon le rôle
const usersPath = authStore.isAdmin ? '/admin/users' : '/rh/users'

// Options de rôle selon les permissions
const roleOptions = computed(() => {
  if (authStore.isAdmin) {
    return [
      { label: 'Utilisateur', value: 'user' },
      { label: 'Responsable RH', value: 'responsable_rh' },
      { label: 'Responsable Demande', value: 'responsable_demande' },
      { label: 'Administrateur', value: 'admin' },
    ]
  }
  return [
    { label: 'Utilisateur', value: 'user' },
    { label: 'Responsable RH', value: 'responsable_rh' },
    { label: 'Responsable Demande', value: 'responsable_demande' },
  ]
})

// Formulaire de modification
const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  role: 'user',
  status: 'active',
})

const submitting = ref(false)
const loading = ref(true)
const errors = ref<Record<string, string[]>>({})

// Charge les données de l'utilisateur
async function fetchUser() {
  loading.value = true
  try {
    const response = await http.get(`/users/${route.params.id}`)
    const user = response.data?.data ?? response.data
    form.value = {
      first_name: user.first_name ?? '',
      last_name: user.last_name ?? '',
      email: user.email ?? '',
      phone: user.phone ?? '',
      role: user.role ?? 'user',
      status: user.status ?? 'active',
    }
  } catch {
    toast.add({ severity: 'error', summary: 'Erreur', detail: "Impossible de charger l'utilisateur", life: 3000 })
    router.push(usersPath)
  } finally {
    loading.value = false
  }
}

// Met à jour l'utilisateur
async function submit() {
  submitting.value = true
  errors.value = {}
  try {
    await http.put(`/users/${route.params.id}`, form.value)
    toast.add({ severity: 'success', summary: 'Succès', detail: 'Utilisateur modifié avec succès', life: 3000 })
    router.push(usersPath)
  } catch (err: any) {
    if (err.response?.status === 422 && err.response.data?.errors) {
      errors.value = err.response.data.errors
    } else {
      toast.add({ severity: 'error', summary: 'Erreur', detail: "Impossible de modifier l'utilisateur", life: 3000 })
    }
  } finally {
    submitting.value = false
  }
}

// Charge l'utilisateur au montage
onMounted(fetchUser)
</script>

<template>
  <div class="form-page">
    <div class="page-header">
      <h1>Modifier l'utilisateur</h1>
      <Button label="Retour" icon="pi pi-arrow-left" severity="secondary" @click="router.push(usersPath)" />
    </div>

    <Card>
      <template #content>
        <div v-if="loading" class="loading">Chargement...</div>

        <form v-else @submit.prevent="submit" class="user-form">
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
              <label for="role">Rôle</label>
              <Select id="role" v-model="form.role" :options="roleOptions" optionLabel="label" optionValue="value" :class="{ 'p-invalid': errors.role }" />
              <small v-if="errors.role" class="p-error">{{ errors.role[0] }}</small>
            </div>
            <div class="field">
              <label for="status">Statut</label>
              <Select id="status" v-model="form.status" :options="['active', 'inactive', 'suspended']" :class="{ 'p-invalid': errors.status }" />
              <small v-if="errors.status" class="p-error">{{ errors.status[0] }}</small>
            </div>
          </div>

          <div class="form-actions">
            <Button label="Annuler" severity="secondary" @click="router.push(usersPath)" />
            <Button label="Enregistrer" icon="pi pi-check" type="submit" :loading="submitting" />
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
.loading { text-align: center; padding: 2rem; color: var(--text-secondary); }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.field { display: flex; flex-direction: column; gap: 0.35rem; }
.field label { font-size: 0.875rem; font-weight: 500; color: var(--text-primary); }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border); }
</style>
