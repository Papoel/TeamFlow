<?php

namespace App\Tests\Helpers;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Factory pour créer des utilisateurs de test
 * 
 * Usage:
 * $user = UserFactory::createOne($entityManager, $passwordHasher, 'A12345', 'password');
 * $admin = UserFactory::createAdmin($entityManager, $passwordHasher);
 */
class UserFactory
{
    /**
     * Crée un utilisateur basique avec ROLE_INTERVENANT
     */
    public static function createOne(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        string $nni = 'A12345',
        string $plainPassword = 'password',
        bool $persist = true
    ): User {
        $user = new User();
        $user->setNni($nni);
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

        if ($persist) {
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $user;
    }

    /**
     * Crée un utilisateur avec des rôles personnalisés
     */
    public static function createWithRoles(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        array $roles,
        string $nni = 'A12345',
        string $plainPassword = 'password',
        bool $persist = true
    ): User {
        $user = new User();
        $user->setNni($nni);
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        $user->setRoles($roles);

        if ($persist) {
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $user;
    }

    /**
     * Crée un administrateur
     */
    public static function createAdmin(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        string $nni = 'ADMIN001',
        string $plainPassword = 'admin_password',
        bool $persist = true
    ): User {
        return self::createWithRoles(
            $entityManager,
            $passwordHasher,
            ['ROLE_ADMIN'],
            $nni,
            $plainPassword,
            $persist
        );
    }

    /**
     * Crée un manager
     */
    public static function createManager(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        string $nni = 'MGR001',
        string $plainPassword = 'manager_password',
        bool $persist = true
    ): User {
        return self::createWithRoles(
            $entityManager,
            $passwordHasher,
            ['ROLE_MANAGER'],
            $nni,
            $plainPassword,
            $persist
        );
    }

    /**
     * Crée un intervenant (utilisateur par défaut)
     */
    public static function createIntervenant(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        string $nni = 'INT001',
        string $plainPassword = 'password',
        bool $persist = true
    ): User {
        return self::createOne(
            $entityManager,
            $passwordHasher,
            $nni,
            $plainPassword,
            $persist
        );
    }

    /**
     * Crée plusieurs utilisateurs à la fois
     * 
     * @return User[]
     */
    public static function createMany(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        int $count,
        string $baseNni = 'USER',
        string $plainPassword = 'password'
    ): array {
        $users = [];

        for ($i = 1; $i <= $count; $i++) {
            $nni = sprintf('%s%03d', $baseNni, $i);
            $users[] = self::createOne(
                $entityManager,
                $passwordHasher,
                $nni,
                $plainPassword,
                false // Ne pas persister immédiatement
            );
        }

        // Persiste tous les utilisateurs d'un coup
        foreach ($users as $user) {
            $entityManager->persist($user);
        }
        $entityManager->flush();

        return $users;
    }
}
