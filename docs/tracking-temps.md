# Système de tracking du temps - Guide complet

## Vue d'ensemble

Le projet inclut un système de tracking de temps de travail intégré dans le Makefile, avec un système de **garde-fou automatique** pour protéger vos données.

## Commandes disponibles

### Commandes principales

```bash
make work-start              # Démarre une session de tracking
make work-stop               # Arrête la session et enregistre le temps
make work-status             # Affiche le temps écoulé de la session en cours
make work-stats              # Affiche toutes les statistiques
```

### Commandes de sauvegarde et restauration

```bash
make work-backup             # Sauvegarde manuelle dans .backups/
make work-restore-from-badge # Restaure l'historique depuis le badge README
make work-check-and-restore  # Vérifie et restaure automatiquement si nécessaire
```

### Autres commandes

```bash
make work-today              # Statistiques du jour
make work-week               # Statistiques de la semaine
make work-export             # Exporte les données en CSV
make work-badge              # Met à jour le badge dans le README
```

## Système de garde-fou automatique

### Protection triple

Le système protège vos données de 3 façons :

1. **Badge README** : Le temps total est sauvegardé dans le badge du README
2. **Sauvegardes .backups/** : Copies horodatées de l'historique
3. **Restauration automatique** : Détection et restauration au démarrage

### Comment ça marche ?

#### 1. Sauvegarde automatique

À chaque `make work-stop`, le système :

- Enregistre la session dans `var/time-tracking/history.csv`
- Met à jour automatiquement le badge dans le README
- Le README devient une sauvegarde persistante

#### 2. Détection de perte

Quand vous lancez `make work-start`, le système :

- Vérifie si `var/time-tracking/history.csv` existe
- Si absent MAIS badge présent dans README → restauration automatique
- Extrait le temps du badge et recrée l'historique

#### 3. Restauration manuelle

Si vous avez perdu vos données, vous pouvez :

```bash
# Restaurer depuis le badge README
make work-restore-from-badge
```

Le système extrait automatiquement le temps (ex: "8h 13m") et recrée l'historique.

## Exemples d'utilisation

### Scénario 1 : Utilisation normale

```bash
# Démarrer le tracking
make work-start
# ⏱️ Session de travail démarrée à 09:00:00

# Travailler...

# Arrêter le tracking
make work-stop
# ⏹️ SESSION DE TRAVAIL TERMINÉE
# Durée: 2h 30m 15s
# ✅ Session enregistrée !
# 💾 Badge README mis à jour
```

### Scénario 2 : Suppression accidentelle de var/

```bash
# Oups, vous avez supprimé le dossier var/
rm -rf var/

# Pas de panique ! Relancez simplement :
make work-start
# ⚠️ Historique manquant mais badge trouvé dans README
# 🔄 Tentative de restauration automatique...
# 🔍 Recherche du temps dans le README...
# 📊 Temps trouvé dans le badge: 8h 13m
# ✅ Historique restauré avec succès !
# ⏱️ Session de travail démarrée...

# Votre temps est récupéré automatiquement !
```

### Scénario 3 : Sauvegarde manuelle avant opération risquée

```bash
# Avant de faire quelque chose de risqué
make work-backup
# ✅ Historique sauvegardé dans .backups/
# ✅ Badge README également mis à jour

# Maintenant vous pouvez travailler en toute sécurité
```

## Structure des données

### Fichier history.csv

Format : `Date Début,Date Fin,Durée (secondes),Durée formatée`

```csv
2025-11-21 09:00:00,2025-11-21 11:30:15,9015,2h 30m
2025-11-21 14:00:00,2025-11-21 17:45:30,13530,3h 45m
```

### Badge README

Le badge est automatiquement mis à jour dans le README entre les marqueurs :

```markdown
<!-- WORK-TIME-BADGE -->
![Temps de travail](https://img.shields.io/badge/Temps%20de%20travail-8h%2013m-blue?style=flat-square&logo=clockify)
<!-- /WORK-TIME-BADGE -->
```

## Bonnes pratiques

### ✅ À faire

- Toujours utiliser `make work-stop` pour arrêter une session
- Vérifier régulièrement avec `make work-stats`
- Commiter le README régulièrement (il contient votre sauvegarde)
- Faire des sauvegardes manuelles avant des opérations risquées

### ❌ À éviter

- Ne pas supprimer manuellement les fichiers de tracking
- Ne pas modifier directement `history.csv`
- Ne pas oublier de stopper une session avec `make work-stop`

## Dépannage

### Problème : Le badge n'est pas trouvé dans le README

**Solution** : Ajoutez les marqueurs dans votre README.md :

```markdown
<!-- WORK-TIME-BADGE -->
![Temps de travail](https://img.shields.io/badge/Temps%20de%20travail-0h%200m-blue?style=flat-square&logo=clockify)
<!-- /WORK-TIME-BADGE -->
```

### Problème : L'historique ne se restaure pas

**Vérification** :
1. Le README contient-il le badge ?
2. Le badge contient-il un temps valide ?

**Restauration manuelle** :
```bash
make work-restore-from-badge
```

### Problème : Session bloquée

Si une session semble bloquée :

```bash
# Supprimer le fichier de session
rm var/time-tracking/work-start.txt

# Relancer
make work-start
```

## Intégration Git

Le système est conçu pour fonctionner avec Git :

- **Commitez le README** : Il contient votre temps total
- **Ne commitez pas** :
  - `var/` (déjà dans .gitignore)
  - `.backups/` (déjà dans .gitignore)

## Export des données

Pour exporter toutes vos données :

```bash
make work-export
# ✅ Données exportées dans: work-tracking-export-20251121-180000.csv
```

Le fichier CSV peut être ouvert avec Excel, Google Sheets, etc.

## Questions fréquentes

**Q : Que se passe-t-il si je perds mon README ?**
R : Vous perdrez le temps total, mais si vous avez des sauvegardes dans `.backups/`, vous pouvez les restaurer manuellement.

**Q : Puis-je modifier manuellement le badge pour corriger le temps ?**  
R : Oui ! Modifiez simplement les valeurs (ex: `8h%2013m`) dans le badge, puis lancez `make work-restore-from-badge`.

**Q : Le système fonctionne-t-il hors ligne ?**  
R : Oui, tout est local. Le badge utilise shields.io pour l'affichage, mais les données sont stockées localement.

**Q : Puis-je utiliser ce système sur plusieurs machines ?**  
R : Oui, en committant régulièrement le README. Sur chaque machine, la restauration automatique récupérera le temps du badge.

## Conclusion

Ce système de garde-fou vous protège contre la perte de données tout en restant simple et automatique. Le README devient votre sauvegarde persistente, et la restauration est automatique.

**Règle d'or** : Committez régulièrement votre README ! 🚀
