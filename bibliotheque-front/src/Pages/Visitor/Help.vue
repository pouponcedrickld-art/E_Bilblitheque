<script setup lang="ts">
import { useRouter } from 'vue-router'
import Button from 'primevue/button'
import Accordion from 'primevue/accordion'
import AccordionPanel from 'primevue/accordionpanel'
import AccordionHeader from 'primevue/accordionheader'
import AccordionContent from 'primevue/accordioncontent'

const router = useRouter()
</script>

<template>
  <div class="page">
    <div class="page-header">
      <div class="page-header-icon">
        <svg viewBox="0 0 32 32" width="28" height="28">
          <circle cx="16" cy="16" r="14" fill="none" stroke="var(--gold)" stroke-width="2" />
          <text x="16" y="21" text-anchor="middle" font-size="14" fill="var(--gold)" font-family="Playfair Display, serif">?</text>
        </svg>
      </div>
      <div>
        <h1>Aide et support</h1>
        <p>Réponses aux questions fréquentes et assistance</p>
      </div>
    </div>

    <div class="faq-section">
      <div class="section-label">
        <span class="section-num">I</span>
        Mon compte
      </div>
      <Accordion :multiple="true" class="faq-accordion">
        <AccordionPanel value="0">
          <AccordionHeader>Comment créer un compte ?</AccordionHeader>
          <AccordionContent>
            <p>Cliquez sur le bouton "Inscription" en haut à droite, remplissez le formulaire avec vos informations, puis validez. Vous recevrez un email de confirmation.</p>
          </AccordionContent>
        </AccordionPanel>
        <AccordionPanel value="1">
          <AccordionHeader>J'ai oublié mon mot de passe</AccordionHeader>
          <AccordionContent>
            <p>Sur la page de connexion, cliquez sur "Mot de passe oublié". Saisissez votre adresse email pour recevoir un lien de réinitialisation.</p>
          </AccordionContent>
        </AccordionPanel>
        <AccordionPanel value="2">
          <AccordionHeader>Comment modifier mon profil ?</AccordionHeader>
          <AccordionContent>
            <p>Connectez-vous, puis cliquez sur votre nom en haut à droite, puis "Mon profil". Vous pourrez modifier vos informations personnelles.</p>
          </AccordionContent>
        </AccordionPanel>
      </Accordion>
    </div>

    <div class="faq-section">
      <div class="section-label">
        <span class="section-num">II</span>
        Recherche et consultation
      </div>
      <Accordion :multiple="true" class="faq-accordion">
        <AccordionPanel value="0">
          <AccordionHeader>Comment rechercher une référence ?</AccordionHeader>
          <AccordionContent>
            <p>Utilisez la barre de recherche sur la page d'accueil ou accédez à la <router-link to="/search">page de recherche</router-link>. Pour une recherche avancée, utilisez les filtres disponibles.</p>
          </AccordionContent>
        </AccordionPanel>
        <AccordionPanel value="1">
          <AccordionHeader>Comment consulter un document ?</AccordionHeader>
          <AccordionContent>
            <p>Une fois connecté, cliquez sur une référence dans le catalogue pour voir ses détails. Si un fichier est disponible, vous pourrez le lire en ligne ou le télécharger.</p>
          </AccordionContent>
        </AccordionPanel>
      </Accordion>
    </div>

    <div class="faq-section">
      <div class="section-label">
        <span class="section-num">III</span>
        Dépôt de documents
      </div>
      <Accordion :multiple="true" class="faq-accordion">
        <AccordionPanel value="0">
          <AccordionHeader>Comment déposer un document ?</AccordionHeader>
          <AccordionContent>
            <p>Connectez-vous avec un rôle "Utilisateur" ou "Responsable Demande", puis cliquez sur "Nouveau dépôt" dans votre tableau de bord. Remplissez le formulaire et soumettez votre demande.</p>
          </AccordionContent>
        </AccordionPanel>
        <AccordionPanel value="1">
          <AccordionHeader>Comment suivre ma demande de dépôt ?</AccordionHeader>
          <AccordionContent>
            <p>Dans votre tableau de bord, la section "Mes dépôts" vous permet de suivre l'état de vos demandes à chaque étape du processus de validation.</p>
          </AccordionContent>
        </AccordionPanel>
      </Accordion>
    </div>

    <div class="faq-section contact-section">
      <div class="section-label">
        <span class="section-num">IV</span>
        Vous ne trouvez pas votre réponse ?
      </div>
      <p class="contact-text">Contactez l'équipe de support à l'adresse suivante :</p>
      <p class="support-email">support@bibliotheque-numerique.com</p>
      <Button label="Retour à l'accueil" icon="pi pi-home" @click="router.push('/')" class="home-btn" />
    </div>
  </div>
</template>

<style scoped>
.page {
  max-width: 800px;
  margin: 0 auto;
}
.page-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}
.page-header-icon {
  flex-shrink: 0;
}
.page-header h1 {
  font-size: 1.5rem;
  font-weight: 700;
}
.page-header p {
  color: var(--muted-foreground);
  font-size: 0.9rem;
  margin-top: 0.15rem;
}

.faq-section {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius-xl);
  padding: 1rem 1.25rem;
  margin-bottom: 1rem;
  transition: border-color 0.25s;
}
.faq-section:hover {
  border-color: var(--border-gold);
}

.section-label {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.95rem;
  font-weight: 700;
  margin-bottom: 0.75rem;
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

.faq-accordion :deep(.p-accordion-header) {
  font-weight: 500;
}
.faq-accordion :deep(.p-accordion-header-link) {
  padding: 0.65rem 0.75rem;
  border-radius: var(--radius-md);
  transition: all 0.2s;
}
.faq-accordion :deep(.p-accordion-header-link:hover) {
  background: rgba(200, 164, 92, 0.06);
}
.faq-accordion :deep(.p-accordion-content) {
  padding: 0.5rem 1rem 1rem;
  font-size: 0.88rem;
  color: var(--muted-foreground);
  line-height: 1.6;
}
.faq-accordion :deep(.p-accordion-content a) {
  color: var(--gold);
  text-decoration: none;
  font-weight: 500;
}
.faq-accordion :deep(.p-accordion-content a:hover) {
  text-decoration: underline;
}

.contact-section {
  text-align: center;
}
.contact-text {
  font-size: 0.9rem;
  color: var(--muted-foreground);
  margin-bottom: 0.5rem;
}
.support-email {
  font-weight: 700;
  color: var(--primary);
  font-size: 1rem;
  font-family: var(--font-serif);
  margin-bottom: 1rem;
}
.home-btn {
  background: var(--primary) !important;
  border: none !important;
  border-radius: var(--radius-lg) !important;
  font-weight: 600 !important;
}
.home-btn:hover {
  background: var(--primary-dark) !important;
  box-shadow: 0 4px 16px rgba(26,58,50,0.3) !important;
}
</style>
