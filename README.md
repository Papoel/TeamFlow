# TEAMFLOW

<!-- WORK-TIME-BADGE -->
![Temps de travail](https://img.shields.io/badge/Temps%20de%20travail-0h%2011m-blue?style=flat-square&logo=clockify)
<!-- /WORK-TIME-BADGE -->

Application de gestion des plannings, activités, qualifications et présences des équipes.
Backend : Symfony + API Platform
Frontend : Angular
Orchestration : Makefile

---

## Sommaire

- [Description du projet](#description-du-projet)
- [Fonctionnalités principales](#fonctionnalités-principales)
- [Architecture](#architecture)
- [Prérequis](#prérequis)
- [Installation](#installation)
  - [1. Cloner le dépôt](#1-cloner-le-dépôt)
  - [2. Installation via Makefile](#2-installation-via-makefile)
- [Utilisation du Makefile](#utilisation-du-makefile)
- [API Platform](#api-platform)
- [Lancer le frontend Angular](#lancer-le-frontend-angular)
- [Tests](#tests)
  - [Backend](#backend-phpunit)
  - [Frontend](#frontend-jest--karma)
- [Contributeurs](#contributeurs)
- [Licence](#licence)

---

## Description du projet

Cette application permet de gérer :

- Le planning d'équipe
- Les présences / absences des intervenants
- L'affectation d’activités (1h, journée, plusieurs jours/semaine)
- Le suivi d'activité si nécessaire
- Les qualifications et certifications des intervenants

Le projet repose sur :

- Symfony + API Platform pour l’API
- Angular pour le frontend
- Un Makefile pour automatiser les tâches

---

## Fonctionnalités principales

- Gestion du planning par équipe
- Visualisation des disponibilités (présence/absence)
- Affectation d’activités selon leurs durées
- Suivi des activités
- Gestion des qualifications / certifications
- API REST / GraphQL automatique (API Platform)
- Interface moderne (Angular)

---

## Prérequis

- PHP >= 8.3
- Composer
- Symfony CLI (recommandé)
- Node.js >= 22
- Angular CLI
- Docker (si utilisé dans le Makefile)
- Make

---

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/Papoel/TeamFlow.git  
cd TeamFlow
```

### 2. Installation via Makefile

make install

---

## Utilisation du Makefile

Commandes principales :

make install        # Installe du projet  
make start          # Démarre le projet  
make stop           # Stoppe le projet  
make test           # Lance les tests  

---

## API Platform

Documentation Swagger :  

`http://localhost:8000/api`

API Platform fournit :  
CRUD automatique, pagination, filtres, validation, formats OpenAPI, JSON-LD, Hydra…

---

## Lancer le frontend Angular

Via Makefile :

```bash
make start-frontend
```

Manuellement :

```bash
cd frontend  
ng serve
```

`URL : http://localhost:4200`

---

## Tests

### Backend (PHPUnit)

```bash
make test-back  
```

ou :  

```bash
cd backend  
php bin/phpunit
```

### Frontend (Jest / Karma)

```bash
make test-front  
```

ou :  

```bash
cd frontend  
npm test
```

---

## Contributeurs

- Papoel

---

## Licence

MIT / Apache / Privée (à adapter)
