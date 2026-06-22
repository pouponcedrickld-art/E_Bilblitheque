<script setup lang="ts">
// Importation du routeur pour la navigation
import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<template>
  <div class="error-page">
    <div class="error-code">403</div>
    <h1>Accès refusé</h1>
    <p>Vous n'avez pas les autorisations nécessaires pour accéder à cette page.</p>
    <button class="btn btn-primary" @click="router.push('/')">
      Retour à l'accueil
    </button>
  </div>
</template>

<style scoped>
.error-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  min-height: calc(100vh - var(--navbar-height) - 6rem);
  padding: 2rem;
}

.error-code {
  font-size: 5rem;
  font-weight: 800;
  color: var(--destructive);
  line-height: 1;
  margin-bottom: 0.5rem;
}

h1 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

p {
  color: var(--text-secondary);
  margin-bottom: 1.5rem;
}

.btn {
  padding: 0.65rem 1.5rem;
  border: none;
  border-radius: var(--radius-sm);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary {
  background: var(--primary);
  color: #fff;
}

.btn-primary:hover {
  background: var(--primary-dark);
}
</style>
