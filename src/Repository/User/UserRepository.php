<?php

namespace App\Repository\User;

use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Trouve un utilisateur par son NNI
     */
    public function findByNni(string $nni): ?User
    {
        return $this->findOneBy(['nni' => $nni]);
    }



    /**
     * Sauvegarde un utilisateur
     * 
     * @throws UnsupportedUserException Si l'objet n'est pas une instance de User
     */
    public function save(PasswordAuthenticatedUserInterface $user): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances de "%s" ne sont pas supportées.', $user::class));
        }

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Supprime un utilisateur
     * 
     * @throws UnsupportedUserException Si l'objet n'est pas une instance de User
     */
    public function remove(PasswordAuthenticatedUserInterface $user): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances de "%s" ne sont pas supportées.', $user::class));
        }

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }
}
