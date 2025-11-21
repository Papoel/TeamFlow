<?php

namespace App\Tests\Traits;

use App\Entity\User\User;
use App\Tests\Helpers\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Trait pour faciliter la création d'utilisateurs dans les tests
 * 
 * Usage:
 * - Ajouter `use InteractsWithUsers;` dans votre classe de test
 * - Utilisez les méthodes helper comme $this->createUser(), $this->createAdmin()
 */
trait InteractsWithUsers
{
    /**
     * Crée un utilisateur simple
     */
    protected function createUser(
        string $nni = 'A12345',
        string $password = 'password',
        bool $persist = true
    ): User {
        return UserFactory::createOne(
            $this->getEntityManager(),
            $this->getPasswordHasher(),
            $nni,
            $password,
            $persist
        );
    }

    /**
     * Crée un utilisateur avec des rôles spécifiques
     */
    protected function createUserWithRoles(
        array $roles,
        string $nni = 'A12345',
        string $password = 'password',
        bool $persist = true
    ): User {
        return UserFactory::createWithRoles(
            $this->getEntityManager(),
            $this->getPasswordHasher(),
            $roles,
            $nni,
            $password,
            $persist
        );
    }

    /**
     * Crée un administrateur
     */
    protected function createAdmin(
        string $nni = 'ADMIN001',
        string $password = 'admin_password',
        bool $persist = true
    ): User {
        return UserFactory::createAdmin(
            $this->getEntityManager(),
            $this->getPasswordHasher(),
            $nni,
            $password,
            $persist
        );
    }

    /**
     * Crée un manager
     */
    protected function createManager(
        string $nni = 'MGR001',
        string $password = 'manager_password',
        bool $persist = true
    ): User {
        return UserFactory::createManager(
            $this->getEntityManager(),
            $this->getPasswordHasher(),
            $nni,
            $password,
            $persist
        );
    }

    /**
     * Crée un intervenant
     */
    protected function createIntervenant(
        string $nni = 'INT001',
        string $password = 'password',
        bool $persist = true
    ): User {
        return UserFactory::createIntervenant(
            $this->getEntityManager(),
            $this->getPasswordHasher(),
            $nni,
            $password,
            $persist
        );
    }

    /**
     * Crée plusieurs utilisateurs
     * 
     * @return User[]
     */
    protected function createManyUsers(
        int $count,
        string $baseNni = 'USER',
        string $password = 'password'
    ): array {
        return UserFactory::createMany(
            $this->getEntityManager(),
            $this->getPasswordHasher(),
            $count,
            $baseNni,
            $password
        );
    }

    /**
     * Récupère l'EntityManager depuis le container
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }

    /**
     * Récupère le PasswordHasher depuis le container
     */
    protected function getPasswordHasher(): UserPasswordHasherInterface
    {
        return static::getContainer()->get('security.user_password_hasher');
    }
}
