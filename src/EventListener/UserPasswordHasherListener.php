<?php

namespace App\EventListener;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: User::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
class UserPasswordHasherListener
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function prePersist(User $user): void
    {
        $this->hashPassword($user);
    }

    public function preUpdate(User $user): void
    {
        $this->hashPassword($user);
    }

    private function hashPassword(User $user): void
    {
        $plainPassword = $user->getPassword();

        // Si le mot de passe n'est pas déjà hashé (détection basique)
        if ($plainPassword && !$this->isPasswordHashed($plainPassword)) {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $plainPassword
            );
            $user->setPassword($hashedPassword);
        }
    }

    private function isPasswordHashed(string $password): bool
    {
        // Un mot de passe hashé commence généralement par $2y$ (bcrypt) ou $argon2
        return str_starts_with($password, '$2y$') || str_starts_with($password, '$argon2');
    }
}
