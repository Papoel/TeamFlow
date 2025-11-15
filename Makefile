# =============================================================================
# STARTER - SYMFONY 7 PROJECT - MAKEFILE
# =============================================================================
# Description: Makefile pour simplifier les commandes du projet Symfony 7
# Auteur: Papoel
# Date: 2025
# =============================================================================

# Variables de couleurs pour l'affichage
GREEN := \033[0;32m
YELLOW := \033[0;33m
CYAN := \033[0;36m
BLUE := \033[0;34m
MAGENTA := \033[0;35m
RED := \033[0;31m
BOLD := \033[1m
NC := \033[0m

# Variable du projet
PROJECT_NAME := starter

# Variables Symfony
SYMFONY_BIN := symfony
CONSOLE := php bin/console
PHPUNIT := php bin/phpunit
COMPOSER := composer

# Variables Docker
DOCKER_COMPOSE := docker compose
DOCKER_EXEC := docker compose exec
DOCKER_RUN := docker run
DOCKER_PROFILES := --profile tools --profile mail --profile cache --profile queue --profile search

# Variables jakzal/phpqa pour l'analyse de qualité
PHP_VERSION := 8.4
PHPQA := jakzal/phpqa:php$(PHP_VERSION)
PHPQA_RUN := $(DOCKER_RUN) --init --rm -v $(PWD):/project -w /project $(PHPQA)

# Variables de base de données
DB_NAME := app
DB_HOST := database
DB_PORT := 5432
DB_USER := app

# Variables du serveur
SERVER_HOST := localhost
SERVER_PORT := 8000

# Variables Angular
FRONTEND_ANGULAR := ng

# Déclaration des targets comme PHONY
.PHONY: help install start stop reset env-local \
        work-start work-stop work-status work-stats work-today work-week work-reset work-export \
        git-stats wakatime-today wakatime-week wakatime-status \
        composer-install composer-update composer-validate composer-audit composer-require composer-remove \
        docker-start docker-up docker-down docker-restart docker-logs docker-ps docker-build docker-clean docker-shell \
        docker-all docker-tools docker-mail docker-cache docker-queue docker-search docker-dev \
        db-create db-drop db-migrate db-diff db-rollback db-validate db-fixtures db-reset db-backup db-restore \
        cache-clear cache-clear-prod cc cache-warmup \
        assets-install assets-compile assets-watch \
        test test-unit test-functional test-coverage test-watch \
        lint lint-yaml lint-twig lint-php lint-container fix-php \
        phpstan phpstan-baseline phpmd phpcpd phpcs phpcbf phpmetrics deptrac phpinsights qa qa-full \
        serve console routes router-match debug-container debug-events debug-env debug-config messenger-consume \
        make-controller make-entity make-form make-crud make-command make-migration make-test make-voter make-subscriber make-auth \
        clean clean-vendor clean-all clean-logs clean-cache-all \
        security-check security-audit \
        deploy-prod optimize-prod \
        about env status version stats

# Target par défaut
.DEFAULT_GOAL := help

# =============================================================================
# AIDE ET DOCUMENTATION
# =============================================================================

help: ## 🚀 Affiche cette aide
	@echo ""
	@echo "$(BOLD)$(CYAN)╔═══════════════════════════════════════════════════════════╗$(NC)"
	@echo "$(BOLD)$(CYAN)║                                                           ║$(NC)"
	@echo "$(BOLD)$(CYAN)║           SYMFONY 7 - $(PROJECT_NAME) MAKEFILE                    ║$(NC)"
	@echo "$(BOLD)$(CYAN)║                                                           ║$(NC)"
	@echo "$(BOLD)$(CYAN)╚═══════════════════════════════════════════════════════════╝$(NC)"
	@echo ""
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | \
	awk 'BEGIN {FS = ":.*?## "}; \
	/^##/ {printf "\n$(YELLOW)%s$(NC)\n", substr($$0, 4)}; \
	!/^##/ {printf "  $(GREEN)%-25s$(NC) %s\n", $$1, $$2}'
	@echo ""
	@echo "$(CYAN)💡 Astuce:$(NC) Utilisez $(BOLD)make <target>$(NC) pour exécuter une commande"
	@echo ""

# =============================================================================
# GESTION DU PROJET
# =============================================================================

## —— 🎯 Projet ————————————————————————————————————————————————————————————————

install: composer-install db-create db-migrate assets-install ## 📦 Installation complète du projet
	@echo "$(GREEN)✅ Installation terminée !$(NC)"

start: docker-start serve work-start ## 🚀 Démarre le projet (Docker + SGBD + serveur Symfony + tracking)
	@echo ""
	@echo "$(BOLD)$(GREEN)╔═══════════════════════════════════════════════════════════╗$(NC)"
	@echo "$(BOLD)$(GREEN)║                  🚀 PROJET DÉMARRÉ !                      ║$(NC)"
	@echo "$(BOLD)$(GREEN)╚═══════════════════════════════════════════════════════════╝$(NC)"
	@echo ""
	@echo "$(CYAN)✅ Docker démarré$(NC)"
	@echo "$(CYAN)✅ Serveur Symfony démarré$(NC)"
	@echo "$(CYAN)✅ Tracking du temps activé$(NC)"
	@echo ""
	@echo "$(YELLOW)💡 Utilisez 'make work-status' pour voir le temps écoulé$(NC)"
	@echo "$(YELLOW)💡 Utilisez 'make stop' pour tout arrêter$(NC)"
	@echo ""

stop: work-stop-silent ## 🛑 Arrête tous les services (Docker + Symfony + tracking)
	@echo ""
	@echo "$(YELLOW)🛑 Arrêt de tous les services...$(NC)"
	@$(DOCKER_COMPOSE) $(DOCKER_PROFILES) down --remove-orphans 2>/dev/null
	@echo "$(RED)  • Conteneurs Docker arrêtés$(NC)"
	@$(SYMFONY_BIN) server:stop 2>/dev/null || true
	@echo "$(RED)  • Serveur Symfony arrêté$(NC)"
	@echo ""
	@echo "$(GREEN)✅ Tous les services sont arrêtés$(NC)"
	@echo ""

work-stop-silent: ## 🔇 Arrête le tracking sans affichage (usage interne)
	@if [ -f var/time-tracking/work-start.txt ]; then \
		START=$$(cat var/time-tracking/work-start.txt); \
		END=$$(date +%s); \
		DURATION=$$((END - START)); \
		HOURS=$$((DURATION / 3600)); \
		MINUTES=$$(((DURATION % 3600) / 60)); \
		SECONDS=$$((DURATION % 60)); \
		echo ""; \
		echo "$(BOLD)$(CYAN)╔═══════════════════════════════════════════════════════════╗$(NC)"; \
		echo "$(BOLD)$(CYAN)║           ⏹️  SESSION DE TRAVAIL TERMINÉE                  ║$(NC)"; \
		echo "$(BOLD)$(CYAN)╚═══════════════════════════════════════════════════════════╝$(NC)"; \
		echo ""; \
		echo "$(GREEN)🕐 Début:$(NC)      $$(date -r $$START '+%Y-%m-%d %H:%M:%S')"; \
		echo "$(GREEN)🕐 Fin:$(NC)        $$(date '+%Y-%m-%d %H:%M:%S')"; \
		echo "$(BOLD)$(YELLOW)⏱️  Durée:$(NC)      $${HOURS}h $${MINUTES}m $${SECONDS}s$(NC)"; \
		echo ""; \
		mkdir -p var/time-tracking; \
		echo "$$(date -r $$START '+%Y-%m-%d %H:%M:%S'),$$(date '+%Y-%m-%d %H:%M:%S'),$${DURATION},$${HOURS}h $${MINUTES}m" >> var/time-tracking/history.csv; \
		rm var/time-tracking/work-start.txt; \
		echo "$(GREEN)✅ Session enregistrée !$(NC)"; \
		echo "$(CYAN)💡 Utilisez 'make work-stats' pour voir vos statistiques$(NC)"; \
	fi

reset: stop cache-clear ## 🔄 Reset du projet (cache, arrêt services)
	@echo "$(YELLOW)🔄 Projet réinitialisé$(NC)"

env-local: ## 📝 Configure .env pour Docker et crée .env.local pour les variables sensibles
	@echo "$(YELLOW)📝 Configuration de l'environnement local...$(NC)"
	@echo ""
	
	@# 1. Mettre à jour DATABASE_URL dans .env
	@echo "$(CYAN)1️⃣  Configuration de DATABASE_URL dans .env$(NC)"
	@DB_USER=$$(grep "^POSTGRES_USER=" .env | cut -d'=' -f2); \
	DB_PASS=$$(grep "^POSTGRES_PASSWORD=" .env | cut -d'=' -f2); \
	DB_NAME=$$(grep "^POSTGRES_DB=" .env | cut -d'=' -f2); \
	NEW_DB_URL="postgresql://$$DB_USER:$$DB_PASS@127.0.0.1:5432/$$DB_NAME?serverVersion=16\&charset=utf8"; \
	sed -i '' "s|^DATABASE_URL=.*|DATABASE_URL=\"$$NEW_DB_URL\"|g" .env; \
	echo "   $(GREEN)✓$(NC) DATABASE_URL → postgresql://$$DB_USER:***@127.0.0.1:5432/$$DB_NAME"
	
	@# 2. Mettre à jour MAILER_DSN dans .env
	@echo "$(CYAN)2️⃣  Configuration de MAILER_DSN dans .env$(NC)"
	@sed -i '' 's|^MAILER_DSN=.*|MAILER_DSN=smtp://127.0.0.1:1025|g' .env
	@echo "   $(GREEN)✓$(NC) MAILER_DSN → smtp://127.0.0.1:1025"
	
	@# 3. Créer .env.local avec variables sensibles
	@echo ""
	@echo "$(CYAN)3️⃣  Création de .env.local (variables sensibles)$(NC)"
	@if [ -f .env.local ]; then \
		echo "$(YELLOW)   ⚠️  Le fichier .env.local existe déjà.$(NC)"; \
		read -p "   Voulez-vous le remplacer ? [y/N] " -n 1 -r; \
		echo; \
		if [[ ! $$REPLY =~ ^[Yy]$$ ]]; then \
			echo "$(RED)   ❌ Création annulée$(NC)"; \
			exit 1; \
		fi; \
	fi
	@APP_SECRET=$$(openssl rand -hex 16); \
	echo "# .env.local - Variables sensibles (non commitées)" > .env.local; \
	echo "# Ce fichier surcharge .env pour votre environnement local" >> .env.local; \
	echo "" >> .env.local; \
	echo "APP_ENV=dev" >> .env.local; \
	echo "APP_SECRET=$$APP_SECRET" >> .env.local; \
	echo "" >> .env.local; \
	echo "# Ajoutez ici vos clés API, tokens, et autres secrets" >> .env.local; \
	echo "# Exemples:" >> .env.local; \
	echo "# GITHUB_TOKEN=your_token_here" >> .env.local; \
	echo "# STRIPE_SECRET_KEY=sk_test_..." >> .env.local
	@echo "   $(GREEN)✓$(NC) .env.local créé avec APP_SECRET généré"
	
	@echo ""
	@echo "$(GREEN)✅ Configuration terminée !$(NC)"
	@echo ""
	@echo "$(BOLD)$(CYAN)Résumé :$(NC)"
	@echo "  $(YELLOW)•$(NC) .env        → Variables partagées (DATABASE_URL, ports, etc.) - $(GREEN)commité$(NC)"
	@echo "  $(YELLOW)•$(NC) .env.local  → Variables sensibles (APP_SECRET, clés API) - $(RED)non commité$(NC)"
	@echo ""
	@echo "$(CYAN)💡 Docker Compose lit .env$(NC)"
	@echo "$(CYAN)💡 Symfony lit .env puis .env.local (qui surcharge .env)$(NC)"

# =============================================================================
# TRACKING DU TEMPS
# =============================================================================

## —— ⏱️  Tracking du temps ——————————————————————————————————————————————————

work-start: ## ⏱️  Démarre le tracking du temps de travail
	@mkdir -p var/time-tracking
	@if [ -f var/time-tracking/work-start.txt ]; then \
		echo "$(YELLOW)⚠️  Une session est déjà en cours !$(NC)"; \
		START=$$(cat var/time-tracking/work-start.txt); \
		NOW=$$(date +%s); \
		DURATION=$$((NOW - START)); \
		HOURS=$$((DURATION / 3600)); \
		MINUTES=$$(((DURATION % 3600) / 60)); \
		echo "$(CYAN)📊 Session en cours depuis: $${HOURS}h $${MINUTES}m$(NC)"; \
		echo "$(CYAN)💡 Utilisez 'make work-stop' pour terminer la session actuelle$(NC)"; \
		exit 1; \
	fi
	@echo "$$(date +%s)" > var/time-tracking/work-start.txt
	@echo "$(GREEN)⏱️  Session de travail démarrée à $$(date '+%H:%M:%S')$(NC)"
	@echo "$(CYAN)💡 Commandes disponibles:$(NC)"
	@echo "  • make work-status  - Voir le temps écoulé"
	@echo "  • make work-stop    - Terminer la session"
	@echo "  • make work-stats   - Voir toutes les statistiques"

work-stop: ## ⏹️  Arrête le tracking et affiche le temps écoulé
	@if [ ! -f var/time-tracking/work-start.txt ]; then \
		echo "$(YELLOW)⏸️  Aucune session de tracking en cours$(NC)"; \
		exit 0; \
	fi
	@START=$(cat var/time-tracking/work-start.txt); \
	END=$(date +%s); \
	DURATION=$((END - START)); \
	HOURS=$((DURATION / 3600)); \
	MINUTES=$(((DURATION % 3600) / 60)); \
	SECONDS=$((DURATION % 60)); \
	echo ""; \
	echo "$(BOLD)$(CYAN)╔═══════════════════════════════════════════════════════════╗$(NC)"; \
	echo "$(BOLD)$(CYAN)║           ⏹️  SESSION DE TRAVAIL TERMINÉE                 ║$(NC)"; \
	echo "$(BOLD)$(CYAN)╚═══════════════════════════════════════════════════════════╝$(NC)"; \
	echo ""; \
	echo "$(GREEN)🕐 Début:$(NC)      $(date -r $START '+%Y-%m-%d %H:%M:%S')"; \
	echo "$(GREEN)🕐 Fin:$(NC)        $(date '+%Y-%m-%d %H:%M:%S')"; \
	echo "$(BOLD)$(YELLOW)⏱️  Durée:$(NC)      ${HOURS}h ${MINUTES}m ${SECONDS}s$(NC)"; \
	echo ""; \
	mkdir -p var/time-tracking; \
	echo "$(date -r $START '+%Y-%m-%d %H:%M:%S'),$(date '+%Y-%m-%d %H:%M:%S'),${DURATION},${HOURS}h ${MINUTES}m" >> var/time-tracking/history.csv; \
	rm var/time-tracking/work-start.txt; \
	echo "$(GREEN)✅ Session enregistrée !$(NC)"; \
	echo "$(CYAN)💡 Utilisez 'make work-stats' pour voir vos statistiques globales$(NC)"; \
	echo ""

work-status: ## 📊 Affiche le statut de la session en cours
	@if [ ! -f var/time-tracking/work-start.txt ]; then \
		echo "$(YELLOW)⏸️  Aucune session en cours$(NC)"; \
		echo "$(CYAN)💡 Démarrez une session avec: make work-start$(NC)"; \
		exit 0; \
	fi
	@START=$$(cat var/time-tracking/work-start.txt); \
	NOW=$$(date +%s); \
	DURATION=$$((NOW - START)); \
	HOURS=$$((DURATION / 3600)); \
	MINUTES=$$(((DURATION % 3600) / 60)); \
	SECONDS=$$((DURATION % 60)); \
	echo ""; \
	echo "$(BOLD)$(GREEN)⏱️  SESSION EN COURS$(NC)"; \
	echo ""; \
	echo "$(CYAN)🕐 Démarrée à:$(NC) $$(date -r $$START '+%H:%M:%S')"; \
	echo "$(BOLD)$(YELLOW)⏱️  Temps écoulé:$(NC) $${HOURS}h $${MINUTES}m $${SECONDS}s$(NC)"; \
	echo ""; \
	echo "$(CYAN)💡 Terminez avec: make work-stop$(NC)"; \
	echo ""

work-stats: ## 📈 Affiche les statistiques de temps complètes
	@if [ ! -f var/time-tracking/history.csv ]; then \
		echo "$(YELLOW)⚠️  Aucune donnée disponible$(NC)"; \
		echo "$(CYAN)💡 Démarrez votre première session avec: make work-start$(NC)"; \
		exit 0; \
	fi
	@echo ""
	@echo "$(BOLD)$(CYAN)╔═══════════════════════════════════════════════════════════╗$(NC)"
	@echo "$(BOLD)$(CYAN)║         📈 STATISTIQUES DE TEMPS DE TRAVAIL               ║$(NC)"
	@echo "$(BOLD)$(CYAN)╚═══════════════════════════════════════════════════════════╝$(NC)"
	@echo ""
	@TOTAL_SECONDS=$$(awk -F',' '{sum+=$$3} END {print sum}' var/time-tracking/history.csv 2>/dev/null || echo 0); \
	TOTAL_HOURS=$$((TOTAL_SECONDS / 3600)); \
	TOTAL_MINUTES=$$(((TOTAL_SECONDS % 3600) / 60)); \
	SESSION_COUNT=$$(wc -l < var/time-tracking/history.csv | tr -d ' '); \
	AVG_MINUTES=$$((TOTAL_SECONDS / SESSION_COUNT / 60)); \
	AVG_HOURS=$$((AVG_MINUTES / 60)); \
	AVG_MINS=$$((AVG_MINUTES % 60)); \
	echo "$(YELLOW)📊 Sessions totales:$(NC)        $$SESSION_COUNT"; \
	echo "$(YELLOW)⏱️  Temps total:$(NC)             $${TOTAL_HOURS}h $${TOTAL_MINUTES}m"; \
	echo "$(YELLOW)📅 Moyenne par session:$(NC)     $${AVG_HOURS}h $${AVG_MINS}m"; \
	echo ""; \
	echo "$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(NC)"; \
	echo "$(BOLD)$(CYAN)📋 Dernières sessions:$(NC)"; \
	echo "$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(NC)"; \
	echo ""; \
	tail -n 10 var/time-tracking/history.csv | while IFS=',' read -r start_date end_date duration formatted; do \
		echo "  $(GREEN)•$(NC) $$start_date → $$formatted"; \
	done; \
	echo ""
	@if [ -f var/time-tracking/work-start.txt ]; then \
		echo "$(YELLOW)⚠️  Une session est actuellement en cours$(NC)"; \
		echo "$(CYAN)💡 Utilisez 'make work-status' pour voir les détails$(NC)"; \
		echo ""; \
	fi

work-today: ## 📅 Affiche les statistiques du jour
	@if [ ! -f var/time-tracking/history.csv ]; then \
		echo "$(YELLOW)⚠️  Aucune donnée disponible$(NC)"; \
		exit 0; \
	fi
	@TODAY=$$(date '+%Y-%m-%d'); \
	echo ""; \
	echo "$(BOLD)$(CYAN)📅 Statistiques du $${TODAY}$(NC)"; \
	echo ""; \
	TOTAL_TODAY=0; \
	COUNT=0; \
	while IFS=',' read -r start_date end_date duration formatted; do \
		if echo "$$start_date" | grep -q "$$TODAY"; then \
			echo "  $(GREEN)•$(NC) $$start_date → $$formatted"; \
			TOTAL_TODAY=$$((TOTAL_TODAY + duration)); \
			COUNT=$$((COUNT + 1)); \
		fi; \
	done < var/time-tracking/history.csv; \
	if [ $$COUNT -eq 0 ]; then \
		echo "$(YELLOW)  Aucune session aujourd'hui$(NC)"; \
	else \
		HOURS=$$((TOTAL_TODAY / 3600)); \
		MINUTES=$$(((TOTAL_TODAY % 3600) / 60)); \
		echo ""; \
		echo "$(YELLOW)📊 Total aujourd'hui:$(NC) $${HOURS}h $${MINUTES}m ($$COUNT sessions)"; \
	fi; \
	echo ""

work-week: ## 📅 Affiche les statistiques de la semaine
	@if [ ! -f var/time-tracking/history.csv ]; then \
		echo "$(YELLOW)⚠️  Aucune donnée disponible$(NC)"; \
		exit 0; \
	fi
	@echo ""; \
	echo "$(BOLD)$(CYAN)📅 Statistiques de la semaine$(NC)"; \
	echo ""; \
	WEEK_START=$$(date -v-mon '+%Y-%m-%d' 2>/dev/null || date -d 'last monday' '+%Y-%m-%d' 2>/dev/null || date '+%Y-%m-%d'); \
	TOTAL_WEEK=0; \
	COUNT=0; \
	while IFS=',' read -r start_date end_date duration formatted; do \
		SESSION_DATE=$$(echo "$$start_date" | cut -d' ' -f1); \
		if [ "$$SESSION_DATE" \>= "$$WEEK_START" ]; then \
			echo "  $(GREEN)•$(NC) $$start_date → $$formatted"; \
			TOTAL_WEEK=$$((TOTAL_WEEK + duration)); \
			COUNT=$$((COUNT + 1)); \
		fi; \
	done < var/time-tracking/history.csv; \
	if [ $$COUNT -eq 0 ]; then \
		echo "$(YELLOW)  Aucune session cette semaine$(NC)"; \
	else \
		HOURS=$$((TOTAL_WEEK / 3600)); \
		MINUTES=$$(((TOTAL_WEEK % 3600) / 60)); \
		echo ""; \
		echo "$(YELLOW)📊 Total cette semaine:$(NC) $${HOURS}h $${MINUTES}m ($$COUNT sessions)"; \
	fi; \
	echo ""

work-reset: ## 🗑️  Réinitialise les statistiques de temps
	@echo "$(YELLOW)⚠️  Êtes-vous sûr de vouloir supprimer TOUTES les données de tracking ? [y/N]$(NC)"
	@read -r confirm; \
	if [ "$$confirm" = "y" ] || [ "$$confirm" = "Y" ]; then \
		rm -rf var/time-tracking; \
		echo "$(GREEN)✅ Toutes les données ont été supprimées$(NC)"; \
	else \
		echo "$(CYAN)❌ Opération annulée$(NC)"; \
	fi

work-export: ## 💾 Exporte les données en CSV
	@if [ ! -f var/time-tracking/history.csv ]; then \
		echo "$(RED)❌ Aucune donnée à exporter$(NC)"; \
		exit 1; \
	fi
	@EXPORT_FILE="work-tracking-export-$$(date +%Y%m%d-%H%M%S).csv"; \
	echo "Date Début,Date Fin,Durée (secondes),Durée formatée" > $$EXPORT_FILE; \
	cat var/time-tracking/history.csv >> $$EXPORT_FILE; \
	echo "$(GREEN)✅ Données exportées dans: $$EXPORT_FILE$(NC)"; \
	echo "$(CYAN)💡 Ouvrez ce fichier avec Excel, Google Sheets, etc.$(NC)"

git-stats: ## 📊 Affiche les statistiques Git du projet
	@echo ""; \
	echo "$(BOLD)$(CYAN)╔═══════════════════════════════════════════════════════════╗$(NC)"; \
	echo "$(BOLD)$(CYAN)║              📊 STATISTIQUES GIT DU PROJET               ║$(NC)"; \
	echo "$(BOLD)$(CYAN)╚═══════════════════════════════════════════════════════════╝$(NC)"; \
	echo ""; \
	FIRST_COMMIT=$$(git log --reverse --format='%ai' 2>/dev/null | head -n1 | cut -d' ' -f1); \
	LAST_COMMIT=$$(git log -1 --format='%ai' 2>/dev/null | cut -d' ' -f1); \
	COMMIT_COUNT=$$(git rev-list --count HEAD 2>/dev/null); \
	CONTRIBUTORS=$$(git shortlog -sn --all 2>/dev/null | wc -l | tr -d ' '); \
	BRANCHES=$$(git branch -a 2>/dev/null | wc -l | tr -d ' '); \
	FILES=$$(git ls-files 2>/dev/null | wc -l | tr -d ' '); \
	echo "$(YELLOW)📅 Premier commit:$(NC)      $$FIRST_COMMIT"; \
	echo "$(YELLOW)📅 Dernier commit:$(NC)      $$LAST_COMMIT"; \
	echo "$(YELLOW)📝 Nombre de commits:$(NC)   $$COMMIT_COUNT"; \
	echo "$(YELLOW)👥 Contributeurs:$(NC)       $$CONTRIBUTORS"; \
	echo "$(YELLOW)🌿 Branches:$(NC)            $$BRANCHES"; \
	echo "$(YELLOW)📄 Fichiers suivis:$(NC)     $$FILES"; \
	echo ""; \
	echo "$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(NC)"; \
	echo "$(BOLD)$(CYAN)👤 Top 5 des contributeurs:$(NC)"; \
	echo "$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(NC)"; \
	echo ""; \
	git shortlog -sn --all 2>/dev/null | head -n 5 | while read count author; do \
		echo "  $(GREEN)$$count commits$(NC) - $$author"; \
	done; \
	echo ""; \
	echo "$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(NC)"; \
	echo "$(BOLD)$(CYAN)📈 Activité récente (7 derniers jours):$(NC)"; \
	echo "$(CYAN)━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━$(NC)"; \
	echo ""; \
	git log --since="7 days ago" --format="%ad - %s" --date=short 2>/dev/null | head -n 10 || echo "  $(YELLOW)Aucun commit récent$(NC)"; \
	echo ""

wakatime-today: ## ⏱️  Affiche les stats WakaTime du jour
	@if ! command -v wakatime-cli > /dev/null 2>&1; then \
		echo "$(RED)❌ WakaTime CLI n'est pas installé$(NC)"; \
		echo ""; \
		echo "$(CYAN)📦 Installation:$(NC)"; \
		echo "  • macOS:    brew install wakatime-cli"; \
		echo "  • Linux:    pip install wakatime"; \
		echo "  • Windows:  scoop install wakatime-cli"; \
		echo ""; \
		echo "$(CYAN)💡 Puis configurez votre API key:$(NC)"; \
		echo "  wakatime-cli --config"; \
		echo ""; \
		echo "$(CYAN)🔗 Plus d'infos: https://wakatime.com/$(NC)"; \
		exit 1; \
	fi
	@echo ""; \
	echo "$(BOLD)$(CYAN)⏱️  WakaTime - Statistiques du jour$(NC)"; \
	echo ""; \
	wakatime-cli --today

wakatime-week: ## 📅 Affiche les stats WakaTime de la semaine
	@if ! command -v wakatime-cli > /dev/null 2>&1; then \
		echo "$(RED)❌ WakaTime CLI n'est pas installé$(NC)"; \
		echo "$(CYAN)💡 Voir: make wakatime-today pour les instructions$(NC)"; \
		exit 1; \
	fi
	@echo ""; \
	echo "$(BOLD)$(CYAN)📅 WakaTime - Statistiques de la semaine$(NC)"; \
	echo ""; \
	wakatime-cli --today --print

wakatime-status: ## 📊 Affiche le statut WakaTime
	@if ! command -v wakatime-cli > /dev/null 2>&1; then \
		echo "$(RED)❌ WakaTime CLI n'est pas installé$(NC)"; \
		echo "$(CYAN)💡 Voir: make wakatime-today pour les instructions$(NC)"; \
		exit 1; \
	fi
	@echo ""; \
	echo "$(BOLD)$(CYAN)📊 WakaTime - Statut$(NC)"; \
	echo ""; \
	wakatime-cli --today --print; \
	echo ""; \
	echo "$(CYAN)🔗 Dashboard complet: https://wakatime.com/dashboard$(NC)"; \
	echo ""

# =============================================================================
# GESTION COMPOSER
# =============================================================================

## —— 📦 Composer ——————————————————————————————————————————————————————————————

composer-install: ## 📥 Installation des dépendances PHP
	$(COMPOSER) install --no-interaction --prefer-dist --optimize-autoloader
	@echo "$(GREEN)✅ Dépendances installées$(NC)"

composer-update: ## 🔄 Mise à jour des dépendances PHP
	$(COMPOSER) update --no-interaction
	@echo "$(GREEN)✅ Dépendances mises à jour$(NC)"

composer-validate: ## ✅ Valide le fichier composer.json
	$(COMPOSER) validate --strict

composer-audit: ## 🔒 Vérifie les vulnérabilités de sécurité
	$(COMPOSER) audit

composer-require: ## ➕ Ajoute une dépendance (ex: make composer-require package=vendor/package)
	$(COMPOSER) require $(package)

composer-remove: ## ➖ Supprime une dépendance (ex: make composer-remove package=vendor/package)
	$(COMPOSER) remove $(package)

# =============================================================================
# GESTION DOCKER
# =============================================================================

## —— 🐳 Docker ————————————————————————————————————————————————————————————————

docker-start: ## 🚀 Démarre DB + Adminer (configuration par défaut)
	@if [ -f docker-compose.yml ]; then \
		$(DOCKER_COMPOSE) --profile tools up -d; \
		echo "$(GREEN)✅ Services démarrés$(NC)"; \
		echo "$(CYAN)📊 Database: localhost:5432$(NC)"; \
		echo "$(CYAN)📊 Adminer: http://localhost:8080$(NC)"; \
		echo "$(CYAN)📊 pgAdmin: http://localhost:5050$(NC)"; \
	else \
		echo "$(YELLOW)⚠ Aucun docker-compose.yml trouvé : mode sans Docker activé.$(NC)"; \
		echo "$(YELLOW)⚠ SQLite sera utilisé automatiquement.$(NC)"; \
	fi

docker-up: ## ⬆️  Démarre uniquement la base de données
	$(DOCKER_COMPOSE) up -d
	@echo "$(GREEN)✅ Base de données démarrée$(NC)"

docker-down: ## ⬇️  Arrête TOUS les conteneurs Docker (tous profils)
	$(DOCKER_COMPOSE) $(DOCKER_PROFILES) down --remove-orphans
	@echo "$(RED)🛑 Tous les conteneurs Docker arrêtés$(NC)"

docker-restart: docker-down docker-start ## 🔄 Redémarre les conteneurs Docker (DB + Adminer)

docker-logs: ## 📋 Affiche les logs Docker
	$(DOCKER_COMPOSE) logs -f

docker-ps: ## 📊 Liste les conteneurs actifs
	$(DOCKER_COMPOSE) ps

docker-env: ## 🔍 Affiche les variables d'environnement d'un service (ex: make docker-env service=adminer)
	@if [ -z "$(service)" ]; then \
		echo "$(RED)❌ Spécifiez un service avec service=nom_du_service$(NC)"; \
		echo "$(YELLOW)Exemple: make docker-env service=adminer$(NC)"; \
		exit 1; \
	fi
	@echo "$(CYAN)Variables d'environnement du service $(BOLD)$(service)$(NC)$(CYAN):$(NC)"
	@echo ""
	@$(DOCKER_COMPOSE) exec $(service) env | sort || \
	echo "$(RED)❌ Le service $(service) n'est pas en cours d'exécution$(NC)"

docker-config: ## 📋 Affiche la configuration Docker Compose finale avec les valeurs substituées
	@echo "$(CYAN)Configuration finale de Docker Compose :$(NC)"
	@echo ""
	@$(DOCKER_COMPOSE) config

docker-build: ## 🔨 Reconstruit les images Docker
	$(DOCKER_COMPOSE) build --no-cache
	@echo "$(GREEN)✅ Images Docker reconstruites$(NC)"

docker-clean: ## 🧹 Supprime tous les conteneurs, volumes et images
	$(DOCKER_COMPOSE) $(DOCKER_PROFILES) down -v --remove-orphans
	@echo "$(GREEN)✅ Nettoyage Docker complet terminé$(NC)"

docker-shell: ## 🐚 Ouvre un shell dans le conteneur de base de données
	$(DOCKER_EXEC) database /bin/sh

docker-all: ## 🚀 Démarre tous les services (tous les profils)
	$(DOCKER_COMPOSE) $(DOCKER_PROFILES) up -d
	@echo "$(GREEN)✅ Tous les services démarrés$(NC)"
	@echo "$(CYAN)📊 Services disponibles:$(NC)"
	@echo "  • Database (PostgreSQL): localhost:5432"
	@echo "  • pgAdmin: http://localhost:5050"
	@echo "  • Adminer: http://localhost:8080"
	@echo "  • Mailpit: http://localhost:8025"
	@echo "  • Redis: localhost:6379"
	@echo "  • Redis Commander: http://localhost:8081"
	@echo "  • RabbitMQ: http://localhost:15672"
	@echo "  • Elasticsearch: http://localhost:9200"

docker-tools: ## 🔧 Démarre avec les outils de gestion DB (pgAdmin + Adminer)
	$(DOCKER_COMPOSE) --profile tools up -d
	@echo "$(GREEN)✅ Services de gestion DB démarrés$(NC)"
	@echo "$(CYAN)🔧 Accès:$(NC)"
	@echo "  • pgAdmin: http://localhost:5050 (admin@starter.local / admin)"
	@echo "  • Adminer: http://localhost:8080"

docker-mail: ## 📧 Démarre avec Mailpit (gestion emails)
	$(DOCKER_COMPOSE) --profile mail up -d
	@echo "$(GREEN)✅ Service email démarré$(NC)"
	@echo "$(CYAN)📧 Accès:$(NC)"
	@echo "  • Mailpit: http://localhost:8025"
	@echo "  • SMTP: localhost:1025"
	@echo "$(YELLOW)💡 Configuration Symfony:$(NC) MAILER_DSN=smtp://mailpit:1025"

docker-cache: ## ⚡ Démarre avec Redis (cache)
	$(DOCKER_COMPOSE) --profile cache up -d
	@echo "$(GREEN)✅ Service cache démarré$(NC)"
	@echo "$(CYAN)⚡ Accès:$(NC)"
	@echo "  • Redis: localhost:6379"
	@echo "  • Redis Commander: http://localhost:8081 (admin / admin)"
	@echo "$(YELLOW)💡 Configuration Symfony:$(NC) REDIS_URL=redis://redis:6379"

docker-queue: ## 📬 Démarre avec RabbitMQ (files d'attente)
	$(DOCKER_COMPOSE) --profile queue up -d
	@echo "$(GREEN)✅ Service de files d'attente démarré$(NC)"
	@echo "$(CYAN)📬 Accès:$(NC)"
	@echo "  • RabbitMQ Management: http://localhost:15672 (starter / starter)"
	@echo "  • AMQP: localhost:5672"
	@echo "$(YELLOW)💡 Configuration Symfony:$(NC) MESSENGER_TRANSPORT_DSN=amqp://starter:starter@rabbitmq:5672/%2f/messages"

docker-search: ## 🔍 Démarre avec Elasticsearch (recherche)
	$(DOCKER_COMPOSE) --profile search up -d
	@echo "$(GREEN)✅ Service de recherche démarré$(NC)"
	@echo "$(CYAN)🔍 Accès:$(NC)"
	@echo "  • Elasticsearch: http://localhost:9200"

docker-dev: docker-mail docker-cache ## 🛠️  Configuration recommandée pour le développement
	@echo "$(GREEN)✅ Configuration de développement prête$(NC)"

# =============================================================================
# GESTION BASE DE DONNÉES
# =============================================================================

## —— 💾 Base de données ——————————————————————————————————————————————————————

db-create: ## ➕ Crée la base de données
	$(CONSOLE) doctrine:database:create --if-not-exists
	@echo "$(GREEN)✅ Base de données créée$(NC)"

db-drop: ## ➖ Supprime la base de données
	$(CONSOLE) doctrine:database:drop --force --if-exists
	@echo "$(RED)🗑️  Base de données supprimée$(NC)"

db-migrate: ## 🔄 Exécute les migrations
	$(CONSOLE) doctrine:migrations:migrate --no-interaction
	@echo "$(GREEN)✅ Migrations exécutées$(NC)"

db-diff: ## 📝 Génère une nouvelle migration
	$(CONSOLE) doctrine:migrations:diff
	@echo "$(GREEN)✅ Migration générée$(NC)"

db-rollback: ## ⏪ Annule la dernière migration
	$(CONSOLE) doctrine:migrations:migrate prev --no-interaction
	@echo "$(YELLOW)⏪ Migration annulée$(NC)"

db-validate: ## ✅ Valide le mapping Doctrine
	$(CONSOLE) doctrine:schema:validate

db-fixtures: ## 🌱 Charge les fixtures
	$(CONSOLE) doctrine:fixtures:load --no-interaction
	@echo "$(GREEN)✅ Fixtures chargées$(NC)"

db-reset: db-drop db-create db-migrate ## 🔄 Reset complet de la base de données
	@echo "$(GREEN)💾 Base de données réinitialisée$(NC)"

db-backup: ## 💾 Sauvegarde la base de données
	@mkdir -p var/backups
	@echo "$(CYAN)💾 Sauvegarde de la base de données...$(NC)"
	docker compose exec -T database pg_dump -U $(DB_USER) $(DB_NAME) > var/backups/db-backup-$$(date +%Y%m%d-%H%M%S).sql
	@echo "$(GREEN)✅ Sauvegarde terminée$(NC)"

db-restore: ## 🔄 Restaure la base de données (ex: make db-restore file=var/backups/db-backup.sql)
	@if [ -z "$(file)" ]; then \
		echo "$(RED)❌ Veuillez spécifier un fichier : make db-restore file=var/backups/db-backup.sql$(NC)"; \
		exit 1; \
	fi
	@echo "$(CYAN)🔄 Restauration de la base de données...$(NC)"
	docker compose exec -T database psql -U $(DB_USER) $(DB_NAME) < $(file)
	@echo "$(GREEN)✅ Restauration terminée$(NC)"

# =============================================================================
# GESTION DU CACHE
# =============================================================================

## —— 🗄️  Cache ————————————————————————————————————————————————————————————————

cache-clear: ## 🧹 Vide le cache
	$(CONSOLE) cache:clear
	@echo "$(GREEN)✅ Cache vidé$(NC)"

cache-warmup: ## 🔥 Réchauffe le cache
	$(CONSOLE) cache:warmup
	@echo "$(GREEN)✅ Cache réchauffé$(NC)"

cache-clear-prod: ## 🧹 Vide le cache de production
	$(CONSOLE) cache:clear --env=prod
	$(CONSOLE) cache:warmup --env=prod
	@echo "$(GREEN)✅ Cache de production vidé$(NC)"

cc: cache-clear cache-warmup ## 🧹 Alias pour vider et réchauffer le cache
	@echo "$(GREEN)✅ Cache vidé et réchauffé$(NC)"

clean-cache-all: ## 🧹 Supprime tous les fichiers de cache
	rm -rf var/cache/*
	@echo "$(GREEN)✅ Tous les fichiers de cache supprimés$(NC)"

# =============================================================================
# GESTION DES ASSETS
# =============================================================================

## —— 🎨 Assets ————————————————————————————————————————————————————————————————

assets-install: ## 📥 Installe les assets
	$(CONSOLE) importmap:install
	$(CONSOLE) asset:install
	@echo "$(GREEN)✅ Assets installés$(NC)"

assets-compile: ## 🔨 Compile les assets
	$(CONSOLE) asset:install
	@echo "$(GREEN)✅ Assets compilés$(NC)"

assets-watch: ## 👀 Surveille les changements d'assets
	$(CONSOLE) asset:install --watch

# =============================================================================
# TESTS
# =============================================================================

## —— 🧪 Tests ——————————————————————————————————————————————————————————————————

test: ## 🧪 Lance tous les tests
	$(PHPUNIT)

test-unit: ## 🧪 Lance les tests unitaires
	$(PHPUNIT) --testsuite Unit

test-functional: ## 🧪 Lance les tests fonctionnels
	$(PHPUNIT) --testsuite Functional

test-coverage: ## 📊 Lance les tests avec couverture de code
	XDEBUG_MODE=coverage $(PHPUNIT) --coverage-html var/coverage
	@echo "$(GREEN)✅ Couverture générée dans var/coverage$(NC)"

test-watch: ## 👀 Lance les tests en mode watch
	$(PHPUNIT) --testdox --watch

# =============================================================================
# QUALITÉ DE CODE
# =============================================================================

## —— 🔍 Qualité de code ——————————————————————————————————————————————————————

lint: lint-yaml lint-twig lint-container ## ✅ Vérifie la syntaxe de tous les fichiers
	@echo "$(GREEN)✅ Vérification de syntaxe terminée$(NC)"

lint-yaml: ## ✅ Vérifie la syntaxe YAML
	$(CONSOLE) lint:yaml config

lint-twig: ## ✅ Vérifie la syntaxe Twig
	$(CONSOLE) lint:twig templates

lint-php: ## ✅ Vérifie la syntaxe PHP
	@if [ -f vendor/bin/php-cs-fixer ]; then \
		vendor/bin/php-cs-fixer fix --dry-run --diff --verbose; \
	else \
		echo "$(YELLOW)⚠️  php-cs-fixer n'est pas installé$(NC)"; \
	fi

lint-container: ## ✅ Vérifie le container
	$(CONSOLE) lint:container

fix-php: ## 🔧 Corrige automatiquement le code PHP
	@if [ -f vendor/bin/php-cs-fixer ]; then \
		vendor/bin/php-cs-fixer fix; \
		echo "$(GREEN)✅ Code PHP corrigé$(NC)"; \
	else \
		echo "$(YELLOW)⚠️  php-cs-fixer n'est pas installé$(NC)"; \
		echo "$(CYAN)💡 Installation : composer require --dev friendsofphp/php-cs-fixer$(NC)"; \
	fi

# =============================================================================
# ANALYSE DE QUALITÉ (PHPQA)
# =============================================================================

## —— 🔬 Analyse de qualité (jakzal/phpqa) ————————————————————————————————————

phpstan: ## 🔍 Analyse statique du code avec PHPStan (niveau max)
	@echo "$(CYAN)🔍 Analyse statique du code avec PHPStan (niveau max)...$(NC)"
	@$(PHPQA_RUN) phpstan analyse -l max src
	@echo "$(GREEN)✅ Analyse PHPStan terminée$(NC)"

phpstan-baseline: ## 📊 Génère la baseline PHPStan
	@echo "$(CYAN)📊 Génération de la baseline PHPStan...$(NC)"
	@$(PHPQA_RUN) phpstan analyse -l max src --generate-baseline
	@echo "$(GREEN)✅ Baseline générée dans phpstan-baseline.neon$(NC)"

phpmd: ## 🔎 Détection de code smell avec PHP Mess Detector
	@echo "$(CYAN)🔎 Détection de code smell avec PHPMD...$(NC)"
	@$(PHPQA_RUN) phpmd src text cleancode,codesize,controversial,design,naming,unusedcode
	@echo "$(GREEN)✅ Analyse PHPMD terminée$(NC)"

phpcpd: ## 📋 Détection de code dupliqué avec PHP Copy/Paste Detector
	@echo "$(CYAN)📋 Détection de code dupliqué...$(NC)"
	@$(PHPQA_RUN) phpcpd src
	@echo "$(GREEN)✅ Analyse PHPCPD terminée$(NC)"

phpcs: ## 📐 Vérification des standards de code avec PHP CodeSniffer
	@echo "$(CYAN)📐 Vérification des standards de code...$(NC)"
	@$(PHPQA_RUN) phpcs --standard=PSR12 src
	@echo "$(GREEN)✅ Analyse PHPCS terminée$(NC)"

phpcbf: ## 🔧 Correction automatique des standards de code avec PHP Code Beautifier
	@echo "$(CYAN)🔧 Correction automatique des standards de code...$(NC)"
	@$(PHPQA_RUN) phpcbf --standard=PSR12 src
	@echo "$(GREEN)✅ Corrections PHPCBF appliquées$(NC)"

phpmetrics: ## 📊 Génère un rapport de métriques avec PhpMetrics
	@echo "$(CYAN)📊 Génération du rapport PhpMetrics...$(NC)"
	@mkdir -p var/phpmetrics
	@$(PHPQA_RUN) phpmetrics --report-html=var/phpmetrics src
	@echo "$(GREEN)✅ Rapport généré dans var/phpmetrics/index.html$(NC)"

deptrac: ## 🏗️  Analyse les dépendances architecturales avec Deptrac
	@echo "$(CYAN)🏗️  Analyse des dépendances architecturales...$(NC)"
	@if [ -f deptrac.yaml ]; then \
		$(PHPQA_RUN) deptrac analyse --config-file=deptrac.yaml; \
		echo "$(GREEN)✅ Analyse Deptrac terminée$(NC)"; \
	else \
		echo "$(YELLOW)⚠️  Fichier deptrac.yaml non trouvé$(NC)"; \
		echo "$(CYAN)💡 Créez un fichier deptrac.yaml pour utiliser cette commande$(NC)"; \
	fi

deptrac-graph: ## 🎨 Génère un graphique des dépendances
	@echo "$(CYAN)🎨 Génération du graphique des dépendances...$(NC)"
	@mkdir -p var/deptrac
	@$(PHPQA_RUN) deptrac analyse --config-file=deptrac.yaml --formatter=graphviz-dot --output=var/deptrac/graph.dot 2>&1 | grep -v "Deprecated:"
	@echo "$(GREEN)✅ Graphique généré : var/deptrac/graph.dot$(NC)"
	@if command -v dot > /dev/null 2>&1; then \
		dot -Tpng var/deptrac/graph.dot -o var/deptrac/graph.png; \
		echo "$(GREEN)✅ Image PNG générée : var/deptrac/graph.png$(NC)"; \
		dot -Tsvg var/deptrac/graph.dot -o var/deptrac/graph.svg; \
		echo "$(GREEN)✅ Image SVG générée : var/deptrac/graph.svg$(NC)"; \
	else \
		echo "$(YELLOW)💡 Pour générer une image, installez Graphviz :$(NC)"; \
		echo "   • macOS: brew install graphviz"; \
		echo "   • Ubuntu/Debian: sudo apt-get install graphviz"; \
	fi

deptrac-image: ## 🖼️  Génère directement une image PNG des dépendances
	@echo "$(CYAN)🖼️  Génération de l'image des dépendances...$(NC)"
	@mkdir -p var/deptrac
	@$(PHPQA_RUN) deptrac analyse --config-file=deptrac.yaml --formatter=graphviz-image --output=var/deptrac/graph.png 2>&1 | grep -v "Deprecated:"
	@echo "$(GREEN)✅ Image générée : var/deptrac/graph.png$(NC)"

deptrac-html: ## 🌐 Génère un rapport HTML interactif des dépendances
	@echo "$(CYAN)🌐 Génération du rapport HTML...$(NC)"
	@mkdir -p var/deptrac
	@$(PHPQA_RUN) deptrac analyse --config-file=deptrac.yaml --formatter=graphviz-html --output=var/deptrac/graph.html 2>&1 | grep -v "Deprecated:"
	@echo "$(GREEN)✅ Rapport HTML généré : var/deptrac/graph.html$(NC)"
	@echo "$(CYAN)💡 Ouvrez le fichier dans votre navigateur$(NC)"

deptrac-mermaid: ## 🧜 Génère un diagramme Mermaid des dépendances
	@echo "$(CYAN)🧜 Génération du diagramme Mermaid...$(NC)"
	@mkdir -p var/deptrac
	@$(PHPQA_RUN) deptrac analyse --config-file=deptrac.yaml --formatter=mermaidjs --output=var/deptrac/graph.mmd 2>&1 | grep -v "Deprecated:"
	@echo "$(GREEN)✅ Diagramme Mermaid généré : var/deptrac/graph.mmd$(NC)"
	@echo "$(CYAN)💡 Visualisez sur https://mermaid.live/$(NC)"

phpinsights: ## 💡 Analyse complète avec PHP Insights
	@echo "$(CYAN)💡 Analyse complète avec PHP Insights...$(NC)"
	@$(PHPQA_RUN) phpinsights analyse src --no-interaction --min-quality=80 --min-complexity=80 --min-architecture=80 --min-style=80
	@echo "$(GREEN)✅ Analyse PHP Insights terminée$(NC)"

qa: phpstan phpmd phpcpd ## 🎯 Analyse de qualité rapide (PHPStan + PHPMD + PHPCPD)
	@echo "$(GREEN)✅ Analyse de qualité rapide terminée$(NC)"

qa-full: phpstan phpmd phpcpd phpcs phpmetrics ## 🚀 Analyse de qualité complète
	@echo ""
	@echo "$(BOLD)$(CYAN)╔═══════════════════════════════════════════════════════════╗$(NC)"
	@echo "$(BOLD)$(CYAN)║                                                           ║$(NC)"
	@echo "$(BOLD)$(CYAN)║          ✅ ANALYSE DE QUALITÉ COMPLÈTE TERMINÉE          ║$(NC)"
	@echo "$(BOLD)$(CYAN)║                                                           ║$(NC)"
	@echo "$(BOLD)$(CYAN)╚═══════════════════════════════════════════════════════════╝$(NC)"
	@echo ""
	@echo "$(GREEN)📊 Rapport PhpMetrics disponible :$(NC) var/phpmetrics/index.html"
	@echo ""

# =============================================================================
# DÉVELOPPEMENT
# =============================================================================

## —— 💻 Développement ————————————————————————————————————————————————————————

serve: ## 🌐 Lance le serveur de développement Symfony
	$(SYMFONY_BIN) server:start -d
	@echo "$(CYAN)🌐 Serveur démarré sur http://$(SERVER_HOST):$(SERVER_PORT)$(NC)"

console: ## 🖥️  Ouvre la console Symfony
	$(CONSOLE)

routes: ## 🗺️  Affiche toutes les routes
	$(CONSOLE) debug:router

router-match: ## 🎯 Teste une route spécifique (ex: make router-match path=/api/users)
	@if [ -z "$(path)" ]; then \
		echo "$(RED)❌ Veuillez spécifier un path : make router-match path=/votre/route$(NC)"; \
		echo "$(YELLOW)Exemples :$(NC)"; \
		echo "  make router-match path=/"; \
		echo "  make router-match path=/api/users"; \
		echo "  make router-match path=/login"; \
		exit 1; \
	fi
	@echo "$(CYAN)🎯 Test de la route: $(path)$(NC)"
	@$(CONSOLE) router:match $(path)

debug-container: ## 🔍 Liste tous les services du container
	$(CONSOLE) debug:container

debug-events: ## 🔍 Liste tous les events disponibles
	$(CONSOLE) debug

# =============================================================================
# FRONTEND
# =============================================================================

## —— 🚀 Frontend ( Angular ) ——————————————————————————————————————————————————

start-frontend: ## 🚀 Démarre le frontend Angular
	$(FRONTEND_ANGULAR) serve