#!/bin/bash

# Script de validation de l'architecture des tests
# Usage: ./tests/validate-architecture.sh

echo "🔍 Validation de l'architecture des tests TeamFlow"
echo "=================================================="
echo ""

# Couleurs
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Compteurs
PASSED=0
FAILED=0

# Fonction pour vérifier qu'un fichier existe
check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $2"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} $2 - Fichier manquant: $1"
        ((FAILED++))
    fi
}

# Fonction pour vérifier qu'une classe/trait existe dans un fichier
check_class_in_file() {
    if grep -q "$2" "$1"; then
        echo -e "${GREEN}✓${NC} $3"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} $3 - '$2' non trouvé dans $1"
        ((FAILED++))
    fi
}

echo "📁 Vérification des fichiers créés..."
echo ""

# Traits
check_file "tests/Traits/RefreshDatabase.php" "Trait RefreshDatabase"
check_file "tests/Traits/InteractsWithUsers.php" "Trait InteractsWithUsers"
check_file "tests/Traits/DatabaseDriverDetector.php" "Trait DatabaseDriverDetector"

# Helpers
check_file "tests/Helpers/UserFactory.php" "Helper UserFactory"

# Documentation
check_file "tests/README.md" "Documentation README"

echo ""
echo "🔧 Vérification des tests refactorisés..."
echo ""

# AuthenticationTest
if [ -f "tests/Api/Feature/AuthenticationTest.php" ]; then
    check_class_in_file "tests/Api/Feature/AuthenticationTest.php" "use RefreshDatabase" "AuthenticationTest utilise RefreshDatabase"
    check_class_in_file "tests/Api/Feature/AuthenticationTest.php" "use InteractsWithUsers" "AuthenticationTest utilise InteractsWithUsers"
    check_class_in_file "tests/Api/Feature/AuthenticationTest.php" "createUser" "AuthenticationTest utilise createUser()"
else
    echo -e "${YELLOW}⚠${NC} AuthenticationTest.php non trouvé (à migrer)"
fi

# LoginControllerTest
if [ -f "tests/src/Controller/Security/LoginControllerTest.php" ]; then
    check_class_in_file "tests/src/Controller/Security/LoginControllerTest.php" "use RefreshDatabase" "LoginControllerTest utilise RefreshDatabase"
    check_class_in_file "tests/src/Controller/Security/LoginControllerTest.php" "use InteractsWithUsers" "LoginControllerTest utilise InteractsWithUsers"
    check_class_in_file "tests/src/Controller/Security/LoginControllerTest.php" "createUser" "LoginControllerTest utilise createUser()"
else
    echo -e "${YELLOW}⚠${NC} LoginControllerTest.php non trouvé (à migrer)"
fi

echo ""
echo "📊 Résumé"
echo "========="
echo -e "${GREEN}Réussis:${NC} $PASSED"
echo -e "${RED}Échoués:${NC} $FAILED"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✅ Architecture validée avec succès !${NC}"
    echo ""
    echo "Prochaines étapes :"
    echo "1. Lancer les tests : php bin/phpunit"
    echo "2. Lire la documentation : cat tests/README.md"
    echo "3. Migrer les tests restants : cat tests/MIGRATION_GUIDE.md"
    exit 0
else
    echo -e "${RED}❌ Validation échouée${NC}"
    echo ""
    echo "Veuillez vérifier les fichiers manquants ci-dessus."
    exit 1
fi
