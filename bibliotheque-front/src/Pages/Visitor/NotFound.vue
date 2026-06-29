<script setup lang="ts">
import { useRouter } from 'vue-router'
import Button from 'primevue/button'

const router = useRouter()
</script>

<template>
  <div class="error-page">
    <div class="error-medallion">
      <svg viewBox="0 0 120 120" class="medallion-svg">
        <circle cx="60" cy="60" r="56" fill="none" stroke="var(--gold)" stroke-width="2.5" class="ring-outer" />
        <circle cx="60" cy="60" r="48" fill="none" stroke="var(--gold-dark)" stroke-width="1.5" class="ring-inner" />
        <circle cx="60" cy="60" r="42" fill="none" stroke="var(--gold)" stroke-width="1" stroke-dasharray="4 4" class="ring-dashed" />
        <text x="60" y="52" text-anchor="middle" font-size="40" font-weight="800" fill="var(--primary)" font-family="Playfair Display, serif">4</text>
        <text x="60" y="85" text-anchor="middle" font-size="40" font-weight="800" fill="var(--primary)" font-family="Playfair Display, serif">0 4</text>
        <text x="60" y="106" text-anchor="middle" font-size="8" fill="var(--gold)" font-family="Playfair Display, serif" font-style="italic" opacity="0.6">pagina non inventa</text>
      </svg>
    </div>
    <h1 class="error-heading">Page introuvable</h1>
    <p class="error-text">La page que vous cherchez n'existe pas ou a été déplacée.</p>
    <Button label="Retour à l'accueil" icon="pi pi-home" @click="router.push('/')" class="home-btn" />
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

.error-medallion {
  width: 160px;
  height: 160px;
  margin-bottom: 1.5rem;
}
.medallion-svg {
  width: 100%;
  height: 100%;
}
.ring-outer {
  animation: draw-outer 1s ease-out forwards;
  stroke-dasharray: 352;
  stroke-dashoffset: 352;
}
.ring-inner {
  animation: draw-inner 0.8s ease-out 0.3s forwards;
  stroke-dasharray: 302;
  stroke-dashoffset: 302;
}
.ring-dashed {
  opacity: 0;
  animation: fade-in 0.6s ease-out 0.7s forwards;
}
@keyframes draw-outer {
  to { stroke-dashoffset: 0; }
}
@keyframes draw-inner {
  to { stroke-dashoffset: 0; }
}
@keyframes fade-in {
  to { opacity: 1; }
}

.error-heading {
  font-size: 1.6rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}
.error-text {
  color: var(--muted-foreground);
  font-size: 0.95rem;
  margin-bottom: 1.5rem;
}
.home-btn {
  background: var(--primary) !important;
  border: none !important;
  border-radius: var(--radius-lg) !important;
  padding: 0.65rem 1.5rem !important;
  font-weight: 600 !important;
}
.home-btn:hover {
  background: var(--primary-dark) !important;
  box-shadow: 0 4px 16px rgba(26,58,50,0.3) !important;
}
</style>
