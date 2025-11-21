<?php

namespace App\Tests\Entity\User;

use App\Entity\User\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    // Test 1 : Création basique
    #[Test]
    public function newUserHasNoId(): void
    {
        $user = new User();

        $this->assertNull($user->getId());
    }

    // Test 2 : Getter/Setter NNI
    #[Test]
    public function getSetNni(): void
    {
        $user = new User();
        $nni = 'A475196';

        $result = $user->setNni($nni);

        $this->assertSame($user, $result); // Teste le fluent interface
        $this->assertEquals($nni, $user->getNni());
    }

    // Test 3 : UserIdentifier utilise le NNI
    #[Test]
    public function getUserIdentifierReturnsNni(): void
    {
        $user = new User();
        $nni = 'A475196';
        $user->setNni($nni);

        $this->assertEquals($nni, $user->getUserIdentifier());
    }

    // Test 4 : UserIdentifier retourne une chaîne même si NNI est null
    #[Test]
    public function getUserIdentifierReturnsEmptyStringWhenNniIsNull(): void
    {
        $user = new User();

        $this->assertEquals('', $user->getUserIdentifier());
    }

    // Test 5 : Getter/Setter Password
    public function testGetSetPassword(): void
    {
        $user = new User();
        $password = 'hashed_password';

        $result = $user->setPassword($password);

        $this->assertSame($user, $result);
        $this->assertEquals($password, $user->getPassword());
    }

    // Test 6 : Rôles par défaut
    #[Test]
    public function getRolesReturnsDefaultRole(): void
    {
        $user = new User();

        $roles = $user->getRoles();

        $this->assertContains('ROLE_INTERVENANT', $roles);
        $this->assertCount(1, $roles);
    }

    // Test 7 : Rôles personnalisés + ROLE_INTERVENANT automatique
    #[Test]
    public function getRolesAlwaysIncludesRoleIntervenant(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();

        //$this->assertContains('ROLE_INTERVENANT', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
    }

    // Test 8 : Pas de rôles dupliqués
    #[Test]
    public function getRolesRemovesDuplicates(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_ADMIN', 'ROLE_USER', 'ROLE_ADMIN']);

        $roles = $user->getRoles();

        $this->assertCount(2, $roles); // Seulement ROLE_USER et ROLE_ADMIN
    }

    // Test 9 : Setter de rôles
    #[Test]
    public function setRoles(): void
    {
        $user = new User();
        $customRoles = ['ROLE_ADMIN', 'ROLE_MODERATOR'];

        $result = $user->setRoles($customRoles);

        $this->assertSame($user, $result);
        // getRoles() ajoute ROLE_INTERVENANT, donc on vérifie les 3
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_MODERATOR', $user->getRoles());
        //$this->assertContains('ROLE_INTERVENANT', $user->getRoles());
    }

    // Test 10 : serialize hash le mot de passe avec CRC32C
    #[Test]
    public function serializeHashesPasswordWithCrc32c(): void
    {
        // Arrange
        $user = new User();
        $user->setNni('A123456');
        $user->setPassword('my_secret_password');
        $user->setRoles(['ROLE_ADMIN']);

        // Act
        $serialized = $user->__serialize();

        // Assert
        // Vérifie que le mot de passe a été remplacé par son hash CRC32C
        $expectedHash = hash('crc32c', 'my_secret_password');
        $passwordKey = "\0" . User::class . "\0password";

        $this->assertArrayHasKey($passwordKey, $serialized);
        $this->assertEquals($expectedHash, $serialized[$passwordKey]);

        // Vérifie que le mot de passe original n'est PAS dans les données sérialisées
        $this->assertNotContains('my_secret_password', $serialized);
    }

    // Test 11 : serialize conserve les autres propriétés
    #[Test]
    public function serializePreservesOtherProperties(): void
    {
        // Arrange
        $user = new User();
        $user->setNni('B987654');
        $user->setPassword('password123');
        $user->setRoles(['ROLE_USER', 'ROLE_MODERATOR']);

        // Act
        $serialized = $user->__serialize();

        // Assert
        // Vérifie que les autres propriétés sont préservées
        $nniKey = "\0" . User::class . "\0nni";
        $rolesKey = "\0" . User::class . "\0roles";

        $this->assertArrayHasKey($nniKey, $serialized);
        $this->assertEquals('B987654', $serialized[$nniKey]);

        $this->assertArrayHasKey($rolesKey, $serialized);
        $this->assertEquals(['ROLE_USER', 'ROLE_MODERATOR'], $serialized[$rolesKey]);
    }

    // Test 12 : serialize produit un hash cohérent
    #[Test]
    public function serializeProducesConsistentHash(): void
    {
        // Arrange
        $user1 = new User();
        $user1->setPassword('same_password');

        $user2 = new User();
        $user2->setPassword('same_password');

        // Act
        $serialized1 = $user1->__serialize();
        $serialized2 = $user2->__serialize();

        // Assert
        // Même mot de passe = même hash
        $passwordKey = "\0" . User::class . "\0password";
        $this->assertEquals(
            $serialized1[$passwordKey],
            $serialized2[$passwordKey],
            'Same passwords should produce same CRC32C hash'
        );
    }
}
