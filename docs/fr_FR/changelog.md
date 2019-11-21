# 21/11/2019
- Correction du problème de sauvegarde de l'objet du poêle.

# 20/11/2019
- On ne rajoute pas dans la queue une commande qui y est déjà.

# 17/11/2019
- On envoie une erreur lorsqu'il y a une alerte sur le poêle.

# 15/11/2019
- Maintenant les nouvelles tentatives de passer des commandes devraient marcher.
- Correction sur la gestion du ChronoThermostat.

# 05/11/2019
- Ajustements sur les niveaux de logs.
- Meilleure gestion du statut des poêles.
- Meilleure gestion des logs.
- Ajout de la gestion du ChronoThermostat.
- On ré-essaye de passer une commande lorsque que la réponse du service est incorrecte.

# 26/10/2019
- Gestion des messages d'alertes.

# 20/10/2019
- Logs un peu plus propres.
- Petite correction visuelle.

# 19/10/2019
- Correction d'un problème avec les version récentes de PHP.

# 16/10/2019
- Affichage correct du statut et de la puissance réelle (il faut effacer toutes les commandes et sauvegarder 2 fois chaque équipement, pour mettre à jour).
- Nouvelle tentative pour les types génériques.

# 14/10/2019
- Correction d'un bug lors de l'enregistrement d'un équipement.

# 09/10/2019
- Ajustements sur les types génériques et l'affichage.

# 21/07/2019
- Correction d'un bug sur le réglage du thermostat.
- Plus de logs en debug.

# 20/07/2019
- Ajout de nouvelles infos et mise à jour des infos lors des actions.

# 19/07/2019
- Vérification des données reçues de la commande get-state.
- Correction d'un bug lors de la mise à jour des commandes infos.
- Correction de la doc.
- Premier essai pour la mise à jour des commandes infos.
- Ajout du nom des paramètres.
- Logs moins sauvages.
- Mise en place des liens vers la doc et le changelog.
- Mise en place du cron5 et d'une fonction refresh qui marche.
- Arborescence de la doc.
- Utilisation d'une image différente pour les équipements.
- Icône du plugin respectant mieux les standards Jeedom.
- Ajout de l'appel à la commande get_state, ça sera mieux pour avoir des logs
- Conformisation temporaire de l'icône du plugin.
- Suppression doublon.
- Ajout de la licence et du début de la doc.
- Ajustement de la configuration des commandes.
- Ajout des titres dans l'entête du tableau.

# 18/07/2019
- Affinage des logs.
- Correction du fichier JSON invalide.
- Ajout de la version du plugin.
- Désactivation de la méthode toHtml.
- Ajout d'une colonne pour l'historique et l'affichage dans l'onglet commandes.
- Ajout de l'onglet commandes.

# 17/07/2019
- Initial commit