# Guide des Tests - TeamFlow

## 🎯 Architecture des Tests

Ce projet utilise une architecture de tests moderne et maintenable avec des **traits** et des **helpers** pour éliminer la duplication de code.

## 📁 Structure

```
tests/
├── Traits/
│   ├── RefreshDatabase.php      # Nettoyage automatique de la BDD
│   └── InteractsWithUsers.php   # Helpers pour créer des utilisateurs
├── Helpers/
│   └── UserFactory.php          # Factory pour créer des utilisateurs
```

---

## 🚀 Quick Start

### Exemple basique

```php
<?php

namespace App\Tests\Api;

use App\Tests\Traits\RefreshDatabase;
use App\Tests\Traits\InteractsWithUsers;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class MyTest extends ApiTestCase
{
    use RefreshDatabase;      // Nettoie la BDD avant chaque test
    use InteractsWithUsers;   // Ajoute des méthodes helper
    
    public function testSomething(): void
    {
        // Créer un utilisateur en 1 ligne
        $user = $this->createUser('A12345', 'password');
        
        // Tester votre logique métier...
    }
}
```

---

## 🛠️ Traits Disponibles

### 1️⃣ `RefreshDatabase`

Nettoie automatiquement la base de données avant chaque test.

**Avantages:**

- ✅ Tests isolés
- ✅ Pas de pollution entre tests
- ✅ Pas de code de nettoyage manuel

**Usage:**

```php
use App\Tests\Traits\RefreshDatabase;

class MyTest extends WebTestCase
{
    use RefreshDatabase;
    
    // La BDD est automatiquement nettoyée avant chaque test
}
```

**Ce qui se passe:**

- Toutes les tables sont vidées avec `TRUNCATE`
- Les contraintes de clés étrangères sont temporairement désactivées
- Le cache de Doctrine est nettoyé

---

### 2️⃣ `InteractsWithUsers`

Fournit des méthodes helper pour créer des utilisateurs rapidement.

**Méthodes disponibles:**

```php
// Utilisateur basique (ROLE_INTERVENANT)
$user = $this->createUser('A12345', 'password');

// Utilisateur avec rôles personnalisés
$user = $this->createUserWithRoles(['ROLE_ADMIN', 'ROLE_MANAGER'], 'A12345');

// Administrateur
$admin = $this->createAdmin('ADM001', 'admin_pass');

// Manager
$manager = $this->createManager('MGR001', 'manager_pass');

// Intervenant
$intervenant = $this->createIntervenant('INT001' 'password');

// Créer plusieurs utilisateurs
$users = $this->createManyUsers(10, 'USER'); // USER001, USER002, ...
```

**Paramètres optionnels:**

```php
createUser(
    string $nni = 'A12345',
    string $password = 'password',
    bool $persist = true  // Si false, l'utilisateur n'est pas persisté
)
```

---

## 🏭 UserFactory

Factory statique pour créer des utilisateurs. Utilisée en interne par `InteractsWithUsers`.

**Usage direct (si vous n'utilisez pas le trait):**

```php
use App\Tests\Helpers\UserFactory;

$em = static::getContainer()->get('doctrine')->getManager();
$hasher = static::getContainer()->get('security.user_password_hasher');

// Créer un utilisateur
$user = UserFactory::createOne($em, $hasher, 'A12345', 'password');

// Créer un admin
$admin = UserFactory::createAdmin($em, $hasher);

// Créer plusieurs utilisateurs
$users = UserFactory::createMany($em, $hasher, 5, 'TEST');
```

---

## 📊 Comparaison Avant/Après

### ❌ Avant (code dupliqué)

```php
class AuthenticationTest extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Code de nettoyage manuel
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->createQuery('DELETE FROM App\Entity\User\User')->execute();
        $entityManager->clear();
    }

    public function testLogin(): void
    {
        $container = static::getContainer();
        
        // 10 lignes pour créer un utilisateur !
        $user = new User();
        $user->setNni('A34123');
        $hashedPassword = $container->get('security.user_password_hasher')
            ->hashPassword($user, '$3CR3T');
        $user->setPassword($hashedPassword);
        
        $manager = $container->get('doctrine')->getManager();
        $manager->persist($user);
        $manager->flush();
        
        // ... tests ...
    }
}
```

### ✅ Après (code propre et maintenable)

```php
class AuthenticationTest extends ApiTestCase
{
    use RefreshDatabase;      // Nettoyage automatique
    use InteractsWithUsers;   // Helpers disponibles

    public function testLogin(): void
    {
        // 1 seule ligne pour créer un utilisateur !
        $user = $this->createUser('A34123', '$3CR3T');
        
        // ... tests (focus sur la logique métier) ...
    }
}
```

**Gains:**

- ⚡ **90% moins de code** pour créer des utilisateurs
- 📖 **Plus lisible** : intention claire
- 🔧 **Plus maintenable** : changement centralisé
- 🧪 **Tests isolés** : pas de pollution entre tests

---

## 🎨 Patterns & Bonnes Pratiques

### 1. Tester uniquement la logique métier

Les tests doivent se concentrer sur **ce qu'ils testent**, pas sur la mise en place.

```php
// ❌ Mauvais : Le test mélange setup et logique
public function testUserCanLogin(): void
{
    $user = new User();
    $user->setNni('A12345');
    // ... 8 lignes de setup ...
    
    // Finalement le vrai test
    $this->assertTrue($user->canLogin());
}

// ✅ Bon : Le test est focalisé
public function testUserCanLogin(): void
{
    $user = $this->createUser('A12345', 'password');
    
    $this->assertTrue($user->canLogin());
}
```

### 2. Nommer les utilisateurs de manière explicite

```php
// ❌ Mauvais
$user1 = $this->createUser('A11111');
$user2 = $this->createUser('A22222');

// ✅ Bon : Les noms ont un sens
$regularUser = $this->createUser('REGULAR01');
$adminUser = $this->createAdmin('ADMIN01');
```

### 3. Utiliser les méthodes appropriées

```php
// ❌ Mauvais : Créer un admin manuellement
$admin = $this->createUserWithRoles(['ROLE_ADMIN'], 'A12345');

// ✅ Bon : Utiliser la méthode dédiée
$admin = $this->createAdmin('A12345');
```

---

## 🔍 Tests Existants à Refactoriser

Voici les tests actuels qui bénéficieraient de cette refactorisation :

1. ✅ **AuthenticationTest.php** - Partiellement refactorisé
2. ✅ **LoginControllerTest.php** - Partiellement refactorisé
3. ⚠️ **UserRepositoryTest.php** - À refactoriser
4. ✅ **UserTest.php** - Pas besoin (tests unitaires purs)

---

## 🧪 Exemples de Tests

### Test d'authentification API

```php
#[Test]
public function userCanAuthenticateViaApi(): void
{
    $user = $this->createUser('A12345', 'secret');
    
    $client = static::createClient();
    $response = $client->request('POST', '/api/auth/login_check', [
        'json' => [
            'nni' => 'A12345',
            'password' => 'secret',
        ],
    ]);
    
    $this->assertResponseIsSuccessful();
    $this->assertArrayHasKey('token', $response->toArray());
}
```

### Test avec différents rôles

```php
#[Test]
public function onlyAdminsCanAccessAdminPanel(): void
{
    $admin = $this->createAdmin();
    $user = $this->createUser();
    
    // L'admin peut accéder
    $this->loginAs($admin);
    $this->client->request('GET', '/admin');
    $this->assertResponseIsSuccessful();
    
    // L'utilisateur normal ne peut pas
    $this->loginAs($user);
    $this->client->request('GET', '/admin');
    $this->assertResponseStatusCodeSame(403);
}
```

### Test de scénario complexe

```php
#[Test]
public function managerCanAssignTasksToMultipleIntervenants(): void
{
    $manager = $this->createManager('MGR001');
    $intervenants = $this->createManyUsers(5, 'INT');
    
    $this->loginAs($manager);
    
    foreach ($intervenants as $intervenant) {
        $this->assignTask($intervenant, 'Task for ' . $intervenant->getNni());
    }
    
    $this->assertCount(5, $this->taskRepository->findAll());
}
```

---

## 💡 Conseils Pro

### 1. Ne pas persister si pas nécessaire

```php
// Si vous n'avez pas besoin de l'utilisateur en base
$user = $this->createUser('A12345', 'password', persist: false);
```

### 2. Créer des helpers spécifiques au domaine

```php
// Dans votre test
protected function createUserWithActivePremium(): User
{
    $user = $this->createUser();
    $user->setPremiumUntil(new \DateTime('+1 year'));
    $this->getEntityManager()->flush();
    return $user;
}
```

### 3. Combiner les traits selon vos besoins

```php
class MyTest extends WebTestCase
{
    use RefreshDatabase;       // Nettoyage BDD
    use InteractsWithUsers;    // Création d'utilisateurs
    use InteractsWithTasks;    // Votre propre trait (à créer)
}
```

---

## 🐛 Dépannage

### "Class not found" pour les traits

Assurez-vous que l'autoloader de Composer est à jour :

```bash
composer dump-autoload
```

### Les tests sont lents

Si le nettoyage de la BDD est trop lent, envisagez d'utiliser une base SQLite en mémoire pour les tests :

```yaml
# config/packages/test/doctrine.yaml
doctrine:
    dbal:
        url: 'sqlite:///:memory:'
```

### Erreur "SET FOREIGN_KEY_CHECKS"

Si vous n'utilisez pas MySQL, modifiez le trait `RefreshDatabase` pour votre SGBD (PostgreSQL, SQLite, etc.).

---

## 📚 Ressources

- [Documentation PHPUnit](https://phpunit.de/)
- [Symfony Testing Best Practices](https://symfony.com/doc/current/testing.html)
- [API Platform Testing](https://api-platform.com/docs/core/testing/)

---

## ✨ Prochaines Étapes

1. ✅ Refactoriser tous les tests existants
2. 📝 Créer d'autres helpers selon les besoins (tasks, projects, etc.)
3. 🧪 Ajouter des tests de régression
4. 📊 Mettre en place la couverture de code
5. 🚀 Intégrer dans la CI/CD

---

**Auteur:** Refactorisation proposée le {{ date }}  
**Version:** 1.0
