<?php

// Point d'entrée des routes API
// Chaque groupe de routes est isolé dans un fichier dédié sous routes/api/

require __DIR__.'/api/auth.php';//tous ce qui concerne la connexion 
require __DIR__.'/api/public.php';//toutes les routes qui sont publique
require __DIR__.'/api/authenticated.php';//toutes les routes lier  a une personnne déjà connecter 
require __DIR__.'/api/manager.php';//toutes les routes lier au module de des responsables de demandes 
require __DIR__.'/api/rh.php';//toutes les routes lier au rh 
require __DIR__.'/api/admin.php';//toutes les routes lier a l'admin 
