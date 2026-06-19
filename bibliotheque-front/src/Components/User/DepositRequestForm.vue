<script setup lang="ts">
import { ref, onMounted } from 'vue'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import FileUpload from 'primevue/fileupload'
import Button from 'primevue/button'

const props = defineProps<{
  initial?: { title?: string; description?: string | null }
}>()

const emit = defineEmits<{
  (e: 'submit', data: { title: string; description: string; file: File | null }): void
}>()

const title = ref(props.initial?.title ?? '')
const description = ref(props.initial?.description ?? '')
const file = ref<File | null>(null)
const submitted = ref(false)

function onFileSelect(event: { files: File[] }) {
  file.value = event.files[0] || null
}

function handleSubmit() {
  submitted.value = true
  if (!title.value.trim()) return
  emit('submit', {
    title: title.value.trim(),
    description: description.value.trim(),
    file: file.value,
  })
}
</script>

<template>
  <form @submit.prevent="handleSubmit" class="deposit-form">
    <div class="field">
      <label for="title">Titre</label>
      <InputText
        id="title"
        v-model="title"
        :class="['input', { 'input-invalid': submitted && !title.trim() }]"
        placeholder="Titre de la référence"
        aria-required="true"
      />
      <small v-if="submitted && !title.trim()" class="error">Le titre est requis.</small>
    </div>

    <div class="field">
      <label for="description">Description</label>
      <Textarea
        id="description"
        v-model="description"
        rows="5"
        class="input"
        placeholder="Description du document..."
      />
    </div>

    <div class="field">
      <label>Fichier proposé</label>
      <FileUpload
        mode="basic"
        name="proposed_file"
        accept=".pdf,.doc,.docx,.odt,.txt"
        :auto="false"
        @select="onFileSelect"
        choose-label="Choisir un fichier"
      />
      <small v-if="file" class="file-name">{{ file.name }}</small>
    </div>

    <Button type="submit" label="Soumettre" icon="pi pi-send" class="submit-btn" />
  </form>
</template>

<style scoped>
.deposit-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
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

.input-invalid {
  border-color: #e53e3e !important;
}

.error {
  font-size: 0.8rem;
  color: #e53e3e;
}

.file-name {
  font-size: 0.8rem;
  color: var(--text-secondary);
  margin-top: 0.15rem;
}

.submit-btn {
  align-self: flex-start;
}
</style>
