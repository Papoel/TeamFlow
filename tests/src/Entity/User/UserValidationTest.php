<?php

namespace App\Tests\Entity\User;

use App\Entity\User\User;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    // Test 1 : Vérifie que l'objet est bien une instance de User
    #[Test]
    public function instanceOfUser(): void
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user);
    }

    // Test 2 : Vérifie que l'utilisateur avec le rôle Manager est valide
    #[Test]
    public function validUserPassesValidation(): void
    {
        $user = new User();
        $user->setNni('A12345');
        $user->setPassword('SecurePassword123');
        $user->setRoles(['ROLE_MANAGER']);

        $errors = $this->validator->validate($user);

        $this->assertCount(0, $errors);
    }

    // Test 3 : Vérifie que l'utilisateur avec le rôle Intervenant est valide
    #[Test]
    public function validUserWithIntervenantRolePassesValidation(): void
    {
        $nni = sprintf('B%05d', random_int(0, 99999));
        $user = new User();
        $user->setNni($nni);
        $user->setPassword('SecurePassword456');
        $user->setRoles(['ROLE_INTERVENANT']);

        $errors = $this->validator->validate($user);

        $this->assertCount(0, $errors);
    }

    // Test 4 : Vérifie que l'utilisateur avec plusieurs rôles est valide
    #[Test]
    public function userWithMultipleRolesPassesValidation(): void
    {
        $user = new User();
        $user->setNni('C11111');
        $user->setPassword('SecurePassword789');
        $user->setRoles(['ROLE_MANAGER', 'ROLE_INTERVENANT']);

        $errors = $this->validator->validate($user);

        $this->assertCount(0, $errors);
    }

    // Test 5 : Vérifie que l'utilisateur avec un tableau vide de rôles est valide
    #[Test]
    public function userWithEmptyRolesPassesValidation(): void
    {
        // getRoles() ajoutera automatiquement ROLE_USER
        $user = new User();
        $user->setNni('D22222');
        $user->setPassword('SecurePassword000');
        $user->setRoles([]); // ✅ Tableau vide = ROLE_USER sera ajouté automatiquement

        $errors = $this->validator->validate($user);

        $this->assertCount(0, $errors);
    }

    // Test 6 : Vérifie qu'une erreur est générée si le NNI est vide
    #[Test]
    public function nniCannotBeBlank(): void
    {
        $user = new User();
        $user->setNni('');
        $user->setPassword('password123456');
        $user->setRoles(['ROLE_MANAGER']);

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, $errors->count());
        $this->assertEquals('Le NNI est obligatoire', $errors[0]->getMessage());
    }

    // Test 7 : Vérifie qu'une erreur est générée si le NNI est trop court
    #[Test]
    public function nniMustBeExactly6Characters(): void
    {
        $user = new User();
        $user->setNni('A123'); // ❌ Trop court
        $user->setPassword('password123456');
        $user->setRoles(['ROLE_MANAGER']);

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, $errors->count());
        $this->assertEquals('Le NNI doit contenir exactement 6 caractères', $errors[0]->getMessage());
    }

    // Test 8 : Vérifie qu'une erreur est générée si le NNI ne correspond pas au pattern
    #[Test]
    public function nniMustMatchPattern(): void
    {
        $user = new User();
        $user->setNni('123456'); // ❌ Pas de lettre au début
        $user->setPassword('password123456');
        $user->setRoles(['ROLE_MANAGER']);

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, $errors->count());
        $this->assertEquals(
            'Le NNI doit commencer par une lettre majuscule suivie de 5 chiffres (ex: A12345)',
            $errors[0]->getMessage()
        );
    }

    // Test 9 : Vérifie que le NNI est normalisé
    #[Test]
    public function setNniNormalizesFirstLetterToUppercase(): void
    {
        $user = new User();
        $user->setNni('a12345'); // ❌ minuscule

        $this->assertEquals('A12345', $user->getNni()); // ✅ Majuscule
    }

    // Test 10 : Vérifie que le NNI est normalisé si la première lettre est déjà majuscule
    #[Test]
    public function setNniPreservesUppercaseFirstLetter(): void
    {
        $user = new User();
        $user->setNni('B67890'); // ✅ Déjà majuscule

        $this->assertEquals('B67890', $user->getNni()); // ✅ Reste majuscule
    }

    // Test 11 : Vérifie que le NNI est normalisé correctement
    #[Test]
    public function setNniPreservesDigits(): void
    {
        $user = new User();
        $user->setNni('c99999');

        $this->assertEquals('C99999', $user->getNni()); // ✅ Seule la 1ère lettre change
    }

    // Test 12 : Vérifie que le NNI est normalisé correctement
    #[Test]
    public function setNniHandlesEmptyString(): void
    {
        $user = new User();
        $user->setNni('');

        $this->assertEquals('', $user->getNni()); // ✅ Ne plante pas
    }

    // Test 13 : Vérifie qu'une erreur est générée si le mot de passe est vide
    #[Test]
    public function passwordCannotBeBlank(): void
    {
        $user = new User();
        $user->setNni('A12345');
        $user->setPassword('');
        $user->setRoles(['ROLE_MANAGER']);

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, $errors->count());
        $this->assertEquals('Veuillez saisir un mot de passe.', $errors[0]->getMessage());
    }

    // Test 14 : Vérifie qu'une erreur est générée si le mot de passe est trop court
    #[Test]
    public function passwordMustBeAtLeast8Characters(): void
    {
        $user = new User();
        $user->setNni('A12345');
        $user->setPassword('short'); // ❌ Trop court
        $user->setRoles(['ROLE_MANAGER']);

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, $errors->count());
        $this->assertStringContainsString(
            'Le mot de passe doit contenir au moins une lettre minuscule, une lettre majuscule et un chiffre.',
            $errors[0]->getMessage()
        );
    }

    // Test 15 : Vérifie qu'une erreur est générée si le rôle est invalide
    #[Test]
    public function invalidRoleIsRejected(): void
    {
        $user = new User();
        $user->setNni('A12345');
        $user->setPassword('password123456');
        $user->setRoles(['ROLE_INVALID']); // ❌ Rôle inexistant

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, $errors->count());
        $this->assertStringContainsString('n\'est pas valide', $errors[0]->getMessage());
    }

    // Test 16 : Vérifie qu'une erreur est générée si le rôle est invalide
    #[Test]
    public function roleUserIsNotAllowedInDatabase(): void
    {
        // ROLE_USER est ajouté automatiquement par getRoles()
        // mais ne devrait pas être stocké en base
        $user = new User();
        $user->setNni('E33333');
        $user->setPassword('password123456');
        $user->setRoles(['ROLE_USER']); // ❌ Ne devrait pas être stocké

        $errors = $this->validator->validate($user);

        $this->assertGreaterThan(0, $errors->count());
        $this->assertStringContainsString('n\'est pas valide', $errors[0]->getMessage());
    }
}
