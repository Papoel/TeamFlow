# User Stories — Projet MRC Planning

**Version** : 1.0  
**Dernière mise à jour** : 16/11/2025  
**Contexte** : Application de gestion de planning pour le service intervention

---

## Table des matières

1. [EPIC 1 — Gestion des intervenants](#epic-1--gestion-des-intervenants)
2. [EPIC 2 — Gestion des activités](#epic-2--gestion-des-activités)
3. [EPIC 3 — Gestion des absences](#epic-3--gestion-des-absences)
4. [EPIC 4 — Planification et visualisation](#epic-4--planification-et-visualisation)
5. [EPIC 5 — Archivage et historique](#epic-5--archivage-et-historique)
6. [EPIC 6 — Authentification et sécurité](#epic-6--authentification-et-sécurité)

---

## EPIC 1 — Gestion des intervenants

### US 1.1 — Création d'un intervenant

**En tant que** manager,  
**Je veux** créer un intervenant avec ses informations personnelles et sa spécialité,  
**Afin de** l'intégrer à l'équipe et pouvoir lui affecter des activités.

**Priorité** : Haute  
**Complexité** : 3 points

**Critères d'acceptation :**

- **GIVEN** un manager authentifié sur l'application
- **WHEN** il envoie une requête POST /api/intervenants avec :
  - Nom (obligatoire)
  - Prénom (obligatoire)
  - Email (obligatoire, format valide)
  - Spécialité(s) (au moins une, parmi : Mécanique, Robinetterie, Chaudronnerie)
- **THEN** l'intervenant est créé avec un identifiant unique
- **AND** un message de confirmation est affiché
- **AND** l'intervenant apparaît dans la liste des intervenants
- **AND** une erreur 400 est retournée si les données sont invalides

**Tests à implémenter :**

- ✓ Création avec données valides
- ✓ Validation des champs obligatoires
- ✓ Validation du format email
- ✓ Vérification unicité de l'email
- ✓ Attribution correcte des spécialités

---

### US 1.2 — Modification d'un intervenant

**En tant que** manager,  
**Je veux** modifier les informations d'un intervenant existant,  
**Afin de** maintenir les données à jour (changement de spécialité, coordonnées, etc.).

**Priorité** : Moyenne  
**Complexité** : 2 points

**Critères d'acceptation :**

- **GIVEN** un manager authentifié et un intervenant existant
- **WHEN** il envoie une requête PATCH /api/intervenants/{id}
- **THEN** seuls les champs fournis sont modifiés
- **AND** l'historique des modifications est conservé (date, auteur)
- **AND** une erreur 404 est retournée si l'intervenant n'existe pas
- **AND** une erreur 403 est retournée si l'email est déjà utilisé

**Règles métier :**

- Un intervenant doit avoir au moins une spécialité
- La modification de spécialité déclenche une vérification des activités futures assignées

---

### US 1.3 — Consultation de la liste des intervenants

**En tant que** manager,  
**Je veux** consulter la liste des intervenants avec filtres et recherche,  
**Afin de** suivre l'organisation et identifier rapidement les ressources disponibles.

**Priorité** : Haute  
**Complexité** : 3 points

**Critères d'acceptation :**

- **GIVEN** un manager authentifié
- **WHEN** il accède à GET /api/intervenants
- **THEN** la liste des intervenants actifs est affichée avec :
  - Nom, prénom
  - Spécialité(s)
  - Statut (disponible, absent, en activité)
  - Nombre d'activités en cours
- **AND** des filtres sont disponibles :
  - Par spécialité
  - Par statut de disponibilité
  - Par recherche textuelle (nom, prénom)
- **AND** la pagination est disponible (20 résultats par page)
- **AND** le tri est possible (nom, spécialité, charge de travail)

---

### US 1.4 — Désactivation d'un intervenant

**En tant que** manager,  
**Je veux** désactiver un intervenant (changement de service ou départ du site),  
**Afin de** conserver l'historique tout en l'empêchant de recevoir de nouvelles affectations.

**Priorité** : Moyenne  
**Complexité** : 2 points

**Critères d'acceptation :**

- **GIVEN** un manager authentifié et un intervenant actif
- **WHEN** il désactive l'intervenant
- **THEN** l'intervenant ne peut plus être affecté à de nouvelles activités
- **AND** ses activités planifiées futures sont signalées pour réaffectation
- **AND** son historique reste consultable
- **AND** il n'apparaît plus dans la liste des intervenants actifs (sauf filtre spécifique)

---

## EPIC 2 — Gestion des activités

### US 2.1 — Création d'une activité préventive ou corrective

**En tant que** manager,  
**Je veux** créer une activité de maintenance (préventive ou corrective),  
**Afin de** planifier les interventions nécessaires sur les équipements.

**Priorité** : Haute  
**Complexité** : 5 points

**Critères d'acceptation :**

- **GIVEN** un manager authentifié
- **WHEN** il crée une activité avec :
  - Type (Préventif / Correctif) — obligatoire
  - Titre / Description — obligatoire
  - Localisation — obligatoire
  - Spécialité requise — obligatoire
  - Date et heure de début prévues — obligatoire
  - Durée estimée — obligatoire
  - Priorité (Basse, Normale, Haute, Urgente) — obligatoire
  - Équipement concerné — optionnel
- **THEN** l'activité est créée avec le statut "À affecter"
- **AND** elle apparaît dans le planning
- **AND** une suggestion d'intervenants disponibles et qualifiés est proposée
- **AND** une erreur est retournée si la spécialité n'existe pas

**Règles métier :**

- Les activités correctives urgentes sont prioritaires
- La durée minimale est de 30 minutes
- Une activité ne peut pas être créée dans le passé

---

### US 2.2 — Affectation d'une activité à un intervenant

**En tant que** manager,  
**Je veux** affecter une activité à un intervenant disponible et qualifié,  
**Afin de** organiser le travail et garantir la qualité de l'intervention.

**Priorité** : Haute  
**Complexité** : 5 points

**Critères d'acceptation :**

- **GIVEN** un manager authentifié, une activité "À affecter" et un intervenant
- **WHEN** il affecte l'activité à l'intervenant
- **THEN** le système vérifie :
  - ✓ L'intervenant possède la spécialité requise
  - ✓ L'intervenant n'est pas absent sur le créneau
  - ✓ L'intervenant n'a pas déjà une activité sur ce créneau
  - ✓ La charge de travail reste raisonnable (max 8h/jour)
- **AND** si une condition n'est pas remplie, l'affectation est refusée avec un message explicite
- **AND** si l'intervenant est absent, le système propose automatiquement 3 alternatives qualifiées et disponibles
- **AND** le statut de l'activité passe à "Planifiée"
- **AND** l'intervenant reçoit une notification (hors scope backend)

**Règles métier :**

- Un intervenant peut avoir maximum 8 heures d'activités planifiées par jour
- Une tolérance de 15 minutes est acceptée entre deux activités (temps de déplacement)

---

### US 2.3 — Modification d'une activité planifiée

**En tant que** manager,  
**Je veux** modifier les détails d'une activité planifiée,  
**Afin de** ajuster la planification selon les contraintes.

**Priorité** : Moyenne  
**Complexité** : 3 points

**Critères d'acceptation :**

- **GIVEN** une activité planifiée
- **WHEN** le manager modifie la date, durée, localisation ou intervenant
- **THEN** les mêmes règles de validation que pour l'affectation sont appliquées
- **AND** l'historique des modifications est conservé
- **AND** si changement de date/durée, les conflits potentiels sont détectés
- **AND** l'intervenant concerné est notifié du changement

---

### US 2.4 — Consultation du détail d'une activité

**En tant que** manager ou intervenant,  
**Je veux** consulter les détails complets d'une activité,  
**Afin de** avoir toutes les informations nécessaires à sa réalisation.

**Priorité** : Moyenne  
**Complexité** : 2 points

**Critères d'acceptation :**

- **GIVEN** un utilisateur authentifié
- **WHEN** il consulte une activité
- **THEN** il voit :
  - Toutes les informations de l'activité
  - L'intervenant affecté (si applicable)
  - L'historique des modifications
  - Le statut actuel
  - Les commentaires éventuels

---

### US 2.5 — Clôture d'une activité

**En tant que** manager,  
**Je veux** clôturer une activité réalisée,  
**Afin de** libérer l'intervenant et garder l'historique des interventions.

**Priorité** : Moyenne  
**Complexité** : 2 points

**Critères d'acceptation :**

- **GIVEN** une activité avec statut "Planifiée" ou "En cours"
- **WHEN** le manager la clôture
- **THEN** le statut passe à "Terminée"
- **AND** la date/heure de clôture est enregistrée
- **AND** un commentaire de clôture peut être ajouté (optionnel)
- **AND** l'activité n'est plus modifiable (sauf réouverture)

---

### US 2.6 — Annulation d'une activité

**En tant que** manager,  
**Je veux** annuler une activité planifiée,  
**Afin de** libérer l'intervenant et maintenir un planning à jour.

**Priorité** : Basse  
**Complexité** : 2 points

**Critères d'acceptation :**

- **GIVEN** une activité planifiée
- **WHEN** le manager l'annule avec un motif
- **THEN** le statut passe à "Annulée"
- **AND** l'intervenant est libéré sur ce créneau
- **AND** l'activité reste visible dans l'historique
- **AND** une raison d'annulation est obligatoire

---

## EPIC 3 — Gestion des absences

### US 3.1 — Déclaration d'une absence

**En tant qu'** intervenant,  
**Je veux** déclarer une absence (congés, maladie, formation),  
**Afin d'** informer mon manager et éviter les affectations pendant cette période.

**Priorité** : Haute  
**Complexité** : 3 points

**Critères d'acceptation :**

- **GIVEN** un intervenant authentifié
- **WHEN** il déclare une absence avec :
  - Type (Congés payés, RTT, Maladie, Formation, Autre) — obligatoire
  - Date de début — obligatoire
  - Date de fin — obligatoire
  - Motif / Commentaire — optionnel
- **THEN** l'absence est créée avec le statut "En attente de validation"
- **AND** le manager est notifié
- **AND** le système identifie automatiquement les activités planifiées impactées
- **AND** une erreur est retournée si les dates sont invalides (début > fin, dans le passé)

**Règles métier :**

- Les absences pour maladie peuvent être créées rétroactivement (avec justificatif)
- Une absence doit être déclarée au minimum 48h à l'avance (sauf maladie)

---

### US 3.2 — Validation ou refus d'une absence

**En tant que** manager,  
**Je veux** valider ou refuser une demande d'absence,  
**Afin de** garantir la cohérence du planning et la continuité de service.

**Priorité** : Haute  
**Complexité** : 4 points

**Critères d'acceptation :**

- **GIVEN** un manager authentifié et une absence "En attente"
- **WHEN** il valide ou refuse l'absence
- **THEN** le statut passe à "Validée" ou "Refusée"
- **AND** l'intervenant est notifié de la décision
- **AND** en cas de refus, un commentaire justificatif est obligatoire
- **AND** en cas de validation :
  - Les activités impactées sont marquées "À réaffecter"
  - Des suggestions de réaffectation sont générées
  - L'intervenant n'est plus disponible sur cette période

**Règles métier :**

- Un manager peut annuler une absence validée en cas d'urgence
- Les absences pour maladie sont auto-validées (mais restent consultables)

---

### US 3.3 — Consultation des absences

**En tant que** manager,  
**Je veux** consulter les absences passées, actuelles et à venir,  
**Afin de** anticiper les besoins en ressources et gérer les plannings.

**Priorité** : Moyenne  
**Complexité** : 2 points

**Critères d'acceptation :**

- **GIVEN** un manager authentifié
- **WHEN** il consulte la liste des absences
- **THEN** il peut filtrer par :
  - Intervenant
  - Type d'absence
  - Statut (En attente, Validée, Refusée)
  - Période
- **AND** un calendrier visuel affiche les absences validées
- **AND** le taux d'absence par intervenant est calculé

---

### US 3.4 — Impact automatique des absences sur le planning

**En tant que** système,  
**Je veux** détecter automatiquement les activités impactées par une absence validée,  
**Afin de** générer des alertes et faciliter la réorganisation.

**Priorité** : Haute  
**Complexité** : 4 points

**Critères d'acceptation :**

- **GIVEN** une absence qui vient d'être validée
- **WHEN** le système analyse le planning
- **THEN** toutes les activités planifiées pendant cette période pour cet intervenant sont identifiées
- **AND** leur statut passe à "À réaffecter"
- **AND** une alerte est créée pour le manager
- **AND** des suggestions d'intervenants de remplacement sont proposées
- **AND** les critères de suggestion sont :
  - Même spécialité
  - Disponible sur le créneau
  - Charge de travail optimale

---

## EPIC 4 — Planification et visualisation

### US 4.1 — Vue planning multi-échelle

**En tant que** manager,  
**Je veux** visualiser le planning en mode jour, semaine, mois ou année,  
**Afin de** suivre les activités avec différents niveaux de détail.

**Priorité** : Haute  
**Complexité** : 5 points

**Critères d'acceptation :**

- **GIVEN** un manager authentifié
- **WHEN** il accède au planning
- **THEN** il peut basculer entre 4 vues :
  - **Jour** : détail horaire (7h-19h) avec toutes les activités
  - **Semaine** : vue sur 5 jours ouvrés
  - **Mois** : vue calendaire avec nombre d'activités par jour
  - **Année** : vue statistique (charge, absences, tendances)
- **AND** chaque vue affiche :
  - Les activités planifiées (avec code couleur par priorité/type)
  - Les absences validées (en grisé)
  - Les conflits détectés (en rouge)
  - La charge de travail par intervenant (jauge visuelle)
- **AND** la navigation entre les périodes est fluide (flèches, sélecteur de date)

**Règles d'affichage :**

- Couleurs : Vert (préventif), Orange (correctif), Rouge (urgent)
- Les activités "À réaffecter" sont en pointillés

---

### US 4.2 — Filtrage du planning

**En tant que** manager,  
**Je veux** filtrer le planning par intervenant, spécialité, type d'activité ou priorité,  
**Afin de** me concentrer sur les informations pertinentes.

**Priorité** : Moyenne  
**Complexité** : 3 points

**Critères d'acceptation :**

- **GIVEN** la vue planning
- **WHEN** le manager applique des filtres
- **THEN** seules les activités correspondantes sont affichées
- **AND** les filtres sont cumulables
- **AND** les filtres actifs sont visibles et supprimables individuellement
- **AND** un bouton "Réinitialiser" permet de tout effacer

**Filtres disponibles :**

- Par intervenant (multi-sélection)
- Par spécialité
- Par type (préventif/correctif)
- Par priorité
- Par statut
- Par localisation

---

### US 4.3 — Détection automatique des conflits

**En tant que** système,  
**Je veux** détecter automatiquement les conflits de planification,  
**Afin d'** alerter le manager et éviter les erreurs d'organisation.

**Priorité** : Haute  
**Complexité** : 5 points

**Critères d'acceptation :**

- **GIVEN** un planning avec activités et absences
- **WHEN** le système analyse la cohérence
- **THEN** il détecte et signale :
  - ✓ **Conflit de disponibilité** : intervenant affecté alors qu'il est absent
  - ✓ **Conflit de chevauchement** : deux activités simultanées pour le même intervenant
  - ✓ **Conflit de compétence** : intervenant affecté sans la spécialité requise
  - ✓ **Surcharge** : dépassement de 8h d'activités planifiées sur une journée
  - ✓ **Conflit de déplacement** : deux activités trop éloignées sans temps de trajet suffisant
- **AND** chaque conflit est affiché avec :
  - Type de conflit
  - Activités concernées
  - Gravité (Bloquant / Avertissement)
  - Suggestions de résolution
- **AND** les conflits bloquants empêchent la validation du planning

---

### US 4.4 — Suggestions de réaffectation

**En tant que** manager,  
**Je veux** recevoir des suggestions automatiques de réaffectation,  
**Afin de** réagir rapidement aux absences et conflits.

**Priorité** : Moyenne  
**Complexité** : 5 points

**Critères d'acceptation :**

- **GIVEN** une activité "À réaffecter"
- **WHEN** le manager consulte les suggestions
- **THEN** le système propose jusqu'à 5 intervenants alternatifs classés par pertinence :
  1. Même spécialité ET disponible ET charge faible
  2. Spécialité compatible ET disponible
  3. Multiservices disponible (en dernier recours)
- **AND** pour chaque suggestion, affiche :
  - Nom de l'intervenant
  - Spécialité(s)
  - Charge actuelle (% du temps)
  - Disponibilité sur le créneau
  - Distance/temps de trajet (si données GPS disponibles)
- **AND** l'affectation peut se faire en un clic

**Algorithme de scoring :**
```
Score = (Compatibilité spécialité × 40) + (Disponibilité × 30) + (Charge inversée × 20) + (Proximité × 10)
```

---

### US 4.5 — Export du planning

**En tant que** manager,  
**Je veux** exporter le planning en PDF ou Excel,  
**Afin de** le partager ou l'imprimer pour les réunions.

**Priorité** : Basse  
**Complexité** : 3 points

**Critères d'acceptation :**

- **GIVEN** une vue planning configurée (filtres, période)
- **WHEN** le manager demande un export
- **THEN** un fichier est généré avec :
  - Les activités visibles selon les filtres
  - La légende (couleurs, statuts)
  - La date de génération
- **AND** deux formats sont disponibles : PDF (lecture) et XLSX (traitement)

---

## EPIC 5 — Archivage et historique

### US 5.1 — Génération automatique de la base annuelle

**En tant que** système,  
**Je veux** générer automatiquement une nouvelle base SQLite au 1er janvier de chaque année,  
**Afin de** séparer les données et optimiser les performances.

**Priorité** : Haute  
**Complexité** : 5 points

**Critères d'acceptation :**

- **GIVEN** le 1er janvier à 00h01
- **WHEN** le système exécute le job planifié
- **THEN** une nouvelle base SQLite est créée avec le format `mrc_planning_YYYY.db`
- **AND** le schéma complet est appliqué (migrations Doctrine)
- **AND** les données de référence sont copiées (spécialités, utilisateurs actifs)
- **AND** l'application bascule automatiquement sur la nouvelle base en écriture
- **AND** la base de l'année précédente passe en lecture seule
- **AND** un log de l'opération est créé
- **AND** en cas d'échec, une alerte admin est envoyée et l'ancienne base reste active

**Règles métier :**

- Les bases sont nommées : `mrc_planning_2024.db`, `mrc_planning_2025.db`, etc.
- Seule la base de l'année en cours est en lecture/écriture
- Conservation minimale : 5 ans

---

### US 5.2 — Consultation des données historiques

**En tant que** manager,  
**Je veux** consulter les plannings et activités des années précédentes,  
**Afin d'** analyser l'historique et améliorer la planification future.

**Priorité** : Moyenne  
**Complexité** : 4 points

**Critères d'acceptation :**

- **GIVEN** un manager authentifié
- **WHEN** il sélectionne une année antérieure (via sélecteur)
- **THEN** l'application bascule en mode "Consultation historique"
- **AND** un bandeau visuel indique "Mode lecture seule - Année YYYY"
- **AND** toutes les fonctionnalités de consultation sont disponibles :
  - Vue planning
  - Détail des activités
  - Liste des absences
  - Statistiques
- **AND** toutes les fonctions de modification sont désactivées
- **AND** un bouton "Retour à l'année en cours" est visible en permanence

**Règles métier :**

- Les bases archivées sont en lecture seule au niveau SQLite
- Les tentatives d'écriture sont bloquées au niveau applicatif également

---

### US 5.3 — Statistiques et rapports annuels

**En tant que** manager,  
**Je veux** générer des statistiques sur une année complète,  
**Afin d'** évaluer la performance et l'utilisation des ressources.

**Priorité** : Basse  
**Complexité** : 5 points

**Critères d'acceptation :**

- **GIVEN** une année sélectionnée (actuelle ou archivée)
- **WHEN** le manager accède aux statistiques
- **THEN** un tableau de bord affiche :
  - **Activités** :
    - Nombre total (préventif vs correctif)
    - Taux de réalisation
    - Durée moyenne
    - Répartition par priorité
  - **Intervenants** :
    - Charge moyenne par intervenant
    - Taux de disponibilité
    - Nombre d'activités réalisées
  - **Absences** :
    - Taux d'absentéisme global et par type
    - Impact sur les plannings (activités réaffectées)
  - **Conflits** :
    - Nombre et types de conflits détectés
    - Temps moyen de résolution
- **AND** des graphiques visuels sont disponibles (camemberts, courbes)
- **AND** un export PDF du rapport est possible

---

### US 5.4 — Migration manuelle vers une nouvelle base

**En tant qu'** administrateur,  
**Je veux** pouvoir déclencher manuellement la création d'une nouvelle base annuelle,  
**Afin de** gérer les cas exceptionnels ou corriger une anomalie.

**Priorité** : Basse  
**Complexité** : 3 points

**Critères d'acceptation :**

- **GIVEN** un administrateur authentifié
- **WHEN** il déclenche la commande CLI `php bin/console app:db:create-yearly`
- **THEN** les mêmes opérations que US 5.1 sont exécutées
- **AND** une confirmation est demandée avant exécution
- **AND** un rapport détaillé est affiché en fin d'opération

---

## EPIC 6 — Authentification et sécurité

### US 6.1 — Connexion utilisateur

**En tant qu'** utilisateur (manager ou intervenant),  
**Je veux** me connecter à l'application avec mes identifiants,  
**Afin d'** accéder aux fonctionnalités selon mon rôle.

**Priorité** : Haute  
**Complexité** : 3 points

**Critères d'acceptation :**

- **GIVEN** un utilisateur avec un compte actif
- **WHEN** il saisit son email et mot de passe
- **THEN** un JWT est généré et retourné si les identifiants sont valides
- **AND** une erreur 401 est retournée si invalides
- **AND** le token a une durée de vie de 8 heures
- **AND** un refresh token est fourni (validité 30 jours)

---

### US 6.2 — Gestion des rôles et permissions

**En tant que** système,  
**Je veux** appliquer les autorisations selon le rôle de l'utilisateur,  
**Afin de** garantir la sécurité des données et des opérations.

**Priorité** : Haute  
**Complexité** : 4 points

**Rôles et permissions :**

| Fonctionnalité | Manager | Intervenant | Admin |
|---|---|---|---|
| Créer/modifier intervenants | ✓ | ✗ | ✓ |
| Créer/modifier activités | ✓ | ✗ | ✓ |
| Affecter activités | ✓ | ✗ | ✓ |
| Déclarer absence | ✓ | ✓ | ✓ |
| Valider absence | ✓ | ✗ | ✓ |
| Consulter planning complet | ✓ | ✗ | ✓ |
| Consulter ses activités | ✓ | ✓ | ✓ |
| Consulter données archivées | ✓ | ✗ | ✓ |
| Gestion bases de données | ✗ | ✗ | ✓ |

**Critères d'acceptation :**

- **GIVEN** un utilisateur authentifié
- **WHEN** il tente d'accéder à une ressource ou action
- **THEN** le système vérifie son rôle
- **AND** l'accès est accordé ou refusé (HTTP 403) selon la matrice ci-dessus
- **AND** les endpoints sont protégés par des voters Symfony

---

### US 6.3 — Déconnexion et gestion de session

**En tant qu'** utilisateur,  
**Je veux** me déconnecter de l'application,  
**Afin de** sécuriser mon accès.

**Priorité** : Moyenne  
**Complexité** : 2 points

**Critères d'acceptation :**

- **GIVEN** un utilisateur connecté
- **WHEN** il se déconnecte
- **THEN** son token JWT est invalidé (ajouté à une blacklist)
- **AND** il est redirigé vers la page de connexion
- **AND** toute tentative d'utilisation du token révoqué retourne une erreur 401

---

## Annexes

### Définition des statuts d'activité

| Statut | Description |
|---|---|
| À affecter | Activité créée mais sans intervenant assigné |
| Planifiée | Activité affectée à un intervenant disponible |
| En cours | Activité démarrée (optionnel, si tracking temps réel) |
| Terminée | Activité clôturée avec succès |
| Annulée | Activité annulée avec motif |
| À réaffecter | Activité impactée par une absence ou un conflit |

### Définition des types de spécialités

- **Électricité** : Installations électriques, dépann