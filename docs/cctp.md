# CCTP — Cahier des Clauses Techniques Particulières  

## Projet : Application de gestion de planification des activités et des absences — Service MRC  

Backend : Symfony 7 / API Platform  
Frontend : Angular  
Base de données : SQLite (1 base par année)  
Nom de l'applicaton : TeamFlow

---

## 1. Objet du projet

Le présent document définit les spécifications techniques de l’application destinée à gérer :

- La planification des activités préventives et correctives
- La visibilité des absences sur le planing
- La spécialité des intervenants
- La vision globale du planning pour les managers

L’application inclut :

- Un backend construit en **Symfony 7.4 (attente release) + API Platform**
- Un frontend **Angular**
- Une base de données **SQLite**, avec une base distincte par année (lecture seule pour les années précédentes)
- Des exigences de **scalabilité**, **maintenabilité**, **TDD**, et respect des principes **SOLID**.

> **Rappel — Principes SOLID :**  
> Les principes SOLID sont cinq règles de conception orientée objet favorisant la maintenabilité, la testabilité et l’évolutivité du code.  
>
> - **S — Single Responsibility Principle** : une classe = une seule responsabilité.  
> - **O — Open/Closed Principle** : une classe est ouverte à l’extension mais fermée à la modification.  
> - **L — Liskov Substitution Principle** : une classe dérivée doit pouvoir remplacer sa classe parente sans altérer le fonctionnement.  
> - **I — Interface Segregation Principle** : privilégier plusieurs petites interfaces plutôt qu’une interface large non pertinente.  
> - **D — Dependency Inversion Principle** : dépendre d’abstractions et non d’implémentations.

---

## 2. Contexte

Le service MRC est composé de :

- 3 managers ;
- 70 intervenants ;
- 3 spécialités :
  - MEC (Mécanique)
  - ROB (Robinetterie)
  - CHAU (Chaudronnerie)

L’objectif est de :

- Optimiser la planification des interventions
- Réduire le temps passé a ajouter manuellement les absences (JNT, Formation etc...)
- Faciliter la gestion et la répartition des charges de travail

---

## 3. Périmètre fonctionnel

### 3.1 Gestion des intervenants

- Création, consultation, modification et suppression des intervenants.
- Affectation d’une spécialité (MEC / ROC / CHAU).
- Assignation d’un manager référent.
- Consultation des disponibilités.

### 3.2 Définition et gestion des activités

Une **activité** est une tâche de maintenance préventive ou corrective à planifier.  
Chaque activité est définie par les attributs suivants :

- **N° OTR (obligatoire)** : Numéro d’Ordre de Travail de Référence.  
  *Identifiant principal de l’activité, unique et obligatoire.*

- **N° TOT (facultatif)** :
Numéro de Tâche Opérationnelle de Travail.  
*Peut être fourni ou non.*

- **Libellé / Titre (obligatoire)** :
Description courte et explicite de l’activité.

- **Type d’activité** : Préventive | Corrective

- **Description détaillée (optionnelle)**

- **Priorité** : Basse | Normale | Haute | Critique

- **Durée estimée** (en minutes ou heures)

- **Date prévue / Semaine prévue**

- **Spécialité requise** : MEC | ROC | CHAU

- **Intervenant affecté (optionnel)**  
  *Renseigne l’intervenant si l’activité est déjà planifiée.*

### 3.3 Gestion des absences

La gestion des absences doit permettre de maintenir un planning fiable en tenant compte
des différents types d’indisponibilité des intervenants. Deux modes d’alimentation des absences
sont envisagés : une extraction Excel hebdomadaire ou une synchronisation directe avec PGI-GTA.

### 3.4 Planification

- Vue calendrier (jour / semaine / mois).
- Visualisation des activités planifiées.
- Détection automatique des conflits :
  - Intervenant absent
  - Double affectation
- Algorithme de disponibilité basé sur les absences et activités existantes.

### 3.5 Notifications

- A définir avec la ligne manégarialle.

---

## 4. Architecture technique

### 4.1 Backend

- Framework : **Symfony 7.1**
- API : **API Platform 4**
- ORM : **Doctrine ORM**
- Authentification : **JWT** (LexikJWTAuthenticationBundle)
- Architecture recommandée :
  - **Clean Architecture / Hexagonale**
  - Gestion métier dans des Services / Use Cases
  - Contrôleurs API Platform sans logique métier

### 4.2 Frontend

- Framework : **Angular**
- UI : Angular Material (ou équivalent)
- Accès API (HttpClient) via services strongly typed
- State Management : NgRx si nécessaire

> **Rappel — State Management avec NgRx :**  
> NgRx est une bibliothèque Angular basée sur les principes de Redux.  
> Elle permet de gérer l’état global de l’application de manière prévisible, centralisée et testable.  
> NgRx repose sur :
>
> - **Store** : source unique de vérité (état global).  
> - **Actions** : événements décrivant ce qui se passe dans l’application.  
> - **Reducers** : fonctions pures qui mettent à jour l’état selon les actions.  
> - **Selectors** : moyens optimisés de lire des morceaux de l’état.  
> - **Effects** : gestion des opérations asynchrones (API, timers, etc.).  
>  
> L’usage de NgRx est recommandé lorsque :
>
> - l’application manipule beaucoup de données provenant de l’API,  
> - plusieurs composants doivent partager/consommer les mêmes données,  
> - on nécessite une forte traçabilité (debug, relecture d’actions),  
> - on souhaite un environnement très favorable au TDD.

### 4.3 Base de données

- SGBD : **SQLite**
- Une base générée par année :  
  - `mrc_2025.sqlite`  
  - `mrc_2024.sqlite` (lecture seule)  
  - etc.
- Les bases des années antérieures sont verrouillées en lecture seule.
- Migration automatique à la création de chaque nouvelle base.

### 4.4 Conteneurisation

La conteneurisation n’est utilisée **que pour l’environnement de développement** afin de garantir une installation rapide, homogène et reproductible entre les différents postes des développeurs.

#### Mode développement (`dev`)

- Mise en place d’un environnement Docker reproduisant au plus proche l’infrastructure de production (si les informations techniques sont disponibles).  
- Les services concernés peuvent inclure :
  - Backend Symfony (PHP-FPM)
  - Frontend Angular (serveur de développement)
  - Serveur web local (Nginx ou Apache)
  - Outils complémentaires éventuels : Mailhog, Adminer, etc.
- Objectifs :
  - Faciliter l’onboarding des développeurs
  - Réduire les différences d’environnement
  - Permettre l'exécution des tests dans un environnement isolé
  - Assurer la compatibilité et la cohérence des versions utilisées

#### Mode production

- **Aucun conteneur Docker n'est utilisé en production.**
- Le déploiement s’effectue sur les serveurs ou VMs fournis par le client.
- L’application est installée selon les contraintes de l’infrastructure existante :
  - Installation native PHP/Symfony
  - Serveur web (ex. Apache ou Nginx)
  - Configuration spécifique de sécurité et de performances
- Cette approche permet de respecter l’architecture te

### 4.5 CI/CD

- Tests unitaires backend : PHPUnit
- Tests fonctionnels API : ApiTestCase / Panther ou Pest
- Lint + build Angular
- Pipeline de déploiement automatique (GitHub Actions, GitLab CI, etc.)

---

## 5. Exigences techniques

### 5.1 TDD

- Les tests doivent être écrits avant l’implémentation.
- Chaque fonctionnalité métier doit être couverte :
  - Disponibilité des intervenants
  - Détection de conflit
  - Transmission d’absence

### 5.2 SOLID

- Responsabilités uniques par service
- Injection de dépendances
- Interfaces pour chaque couche métier
- Repositories séparés de la logique
- Aucun traitement métier dans les entités

### 5.3 Scalabilité / Maintenabilité

- Services stateless
- Découplage fort entre couches
- API RESTful idempotente

> Une **méthode idempotente** signifie que le résultat d'une requête effectuée avec succès est indépendant du nombre de fois où elle a été exécutée.

- Pagination sur toutes les collections
- Documentation OpenAPI générée par API Platform

---

## 6. Exigences de sécurité

- Authentification par **JWT** obligatoire.
- Rôles :
  - ROLE_MANAGER
  - ROLE_INTERVENANT
- Droits :
  - Les intervenants ne peuvent modifier que leurs propres absences.
  - Les managers peuvent gérer tout le périmètre.
- Validation stricte des données côté backend et frontend.
- Journalisation :
  - Chaque activité pourra être enrichie d’entrées de journal (notes, commentaires, retours d’expérience).
  - Ces informations permettront de documenter le déroulement des activités longues, d’assurer la traçabilité des étapes réalisées et de capitaliser sur les actions menées.
  - L’objectif est de disposer d’un historique structuré facilitant le suivi, la transmission d’informations et l’amélioration continue.


---

## 7. Livrables

- CCTP (présent document)
- User Stories détaillées
- Schéma UML (classes + séquences)
- Modèle de données SQLite
- Documentation API générée (OpenAPI)
- Ensemble complet des tests automatisés
- Pipeline CI/CD

---

## 8. Planning prévisionnel

### Fin 2025 — Étude, cadrage et préparation

- Rédaction et validation du CCTP.
- Rédaction des Users Stories + scénarios TDD.
- Conception du modèle de données (intervenants, activités, absences, planning).
- Choix de l’architecture (API Platform, structure Angular, organisation code).
- Définition du système de bases SQLite annuelles.
- Mise en place du dépôt et outils (CI/CD, normes, linters).

### Janvier 2026 — Montée en compétence & socle technique

- Apprentissage Angular ciblé sur les besoins du projet (components, services, routing).
- Mise en place du projet Symfony + API Platform.
- Mise en place du projet Angular (squelette + tests).
- Mise en place des environnements de développement (Docker si nécessaire).
- Mise en place de la stratégie de tests : PHPUnit, Behat, Jasmine/Karma.

### Février 2026 — Développement du MVP

- Module Intervenants : CRUD + affectation spécialités (MEC, ROC, CHAU).
- Module Activités : CRUD + définition N°OTR, N°TOT, libellé.
- Module Absences (version simple) : déclaration + statut (en attente).
- API + intégration Angular + premières pages fonctionnelles.
- Génération planning minimal (version lecture).
- Tests automatisés continus (TDD).

### Mars 2026 — Finalisation MVP & mise en production

- Ajout des règles métiers critiques (absences → impact planning).
- Gestion des conflits simples (activité ↔ absence).
- Journalisation simple des activités.
- Export hebdomadaire Excel (intégration PGI-GTA → phase ultérieure).
- Stabilisation et corrections.
- Préparation de l’environnement de production.
- **Mise en production fin Mars 2026**.

### À partir d’Avril 2026 — Maintenance & évolutions

- Amélioration ergonomie et UI.
- Règles avancées du planning (détections complexes, réaffectations).
- Notifications managers.
- Intégration PGI-GTA si validé.
- PWA / version mobile optimisée.
- Documentation et améliorations continues.



---

## 9. Maintenance et évolutions

- Création automatique d’une nouvelle base SQLite à chaque année
- Archivage sécurisé des bases précédentes
- Possibilité d’export CSV / Excel des données
- Journal d’audit consultable via l’interface manager
