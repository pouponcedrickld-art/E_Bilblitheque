<script setup lang="ts">
import { useRouter } from 'vue-router'
import Card from 'primevue/card'
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
      <div>
        <h1>Aide et support</h1>
        <p>Réponses aux questions fréquentes et assistance</p>
      </div>
    </div>

    <Card class="mb-3">
      <template #title>Mon compte</template>
      <template #content>
        <Accordion :multiple="true">
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
      </template>
    </Card>

    <Card class="mb-3">
      <template #title>Recherche et consultation</template>
      <template #content>
        <Accordion :multiple="true">
          <AccordionPanel value="0">
            <AccordionHeader>Comment rechercher une référence ?</AccordionHeader>
            <AccordionContent>
              <p>Utilisez la barre de recherche sur la page d'accueil ou accédez à la <router-link to="/search">page de recherche</router-link> pour une recherche simple. Pour une recherche plus avancée, utilisez les filtres disponibles.</p>
            </AccordionContent>
          </AccordionPanel>
          <AccordionPanel value="1">
            <AccordionHeader>Comment consulter un document ?</AccordionHeader>
            <AccordionContent>
              <p>Une fois connecté, cliquez sur une référence dans le catalogue pour voir ses détails. Si un fichier est disponible, vous pourrez le lire en ligne ou le télécharger.</p>
            </AccordionContent>
          </AccordionPanel>
        </Accordion>
      </template>
    </Card>

    <Card class="mb-3">
      <template #title>Dépôt de documents</template>
      <template #content>
        <Accordion :multiple="true">
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
      </template>
    </Card>

    <Card>
      <template #title>Vous ne trouvez pas votre réponse ?</template>
      <template #content>
        <p>Contactez l'équipe de support à l'adresse suivante :</p>
        <p class="support-email">support@bibliotheque-numerique.com</p>
        <Button label="Retour à l'accueil" icon="pi pi-home" @click="router.push('/')" class="mt-2" />
      </template>
    </Card>
  </div>
</template>

<style scoped>
.page { max-width: 800px; margin: 0 auto; }
.page-header { margin-bottom: 1.5rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; }
.page-header p { color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem; }
.mb-3 { margin-bottom: 1rem; }
.mt-2 { margin-top: 0.5rem; }
.support-email { font-weight: 600; color: var(--primary); font-size: 1rem; }
</style>
