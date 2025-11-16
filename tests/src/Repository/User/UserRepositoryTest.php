<?php

namespace App\Tests\Repository\User;

use App\Entity\User\User;
use PHPUnit\Framework\Attributes\Test;
use App\Repository\User\UserRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(User::class);
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        parent::tearDown();
    }

    // Test 1 : Le repository peut être instancié
    #[Test]
    public function canBeInstantiated(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);

        $repository = new UserRepository($registry);

        $this->assertInstanceOf(UserRepository::class, $repository);
    }

    // Test 2 : upgradePassword met à jour le mot de passe
    #[Test]
    public function upgradePasswordUpdatesUserPassword(): void
    {
        // Arrange
        $newHashedPassword = 'new_hashed_password';
        $user = new User();
        $user->setPassword('old_password');

        // Crée un EntityManager avec des expectations spécifiques
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($user);
        $entityManager->expects($this->once())
            ->method('flush');

        // Utilise le helper avec l'EntityManager personnalisé
        $registry = $this->createConfiguredRegistry($entityManager);
        $repository = new UserRepository($registry);

        // Act
        $repository->upgradePassword($user, $newHashedPassword);

        // Assert
        $this->assertEquals($newHashedPassword, $user->getPassword());
    }

    // Test 3 : upgradePassword lance une exception pour un mauvais type
    #[Test]
    public function upgradePasswordThrowsExceptionForWrongUserType(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $wrongUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        $registry = $this->createConfiguredRegistry();
        $repository = new UserRepository($registry);

        $repository->upgradePassword($wrongUser, 'new_password');
    }

    // Test 4 : findByNni trouve un utilisateur par NNI
    #[Test]
    public function findByNniFindsUserByNni(): void
    {
        // Arrange
        $nni = sprintf('B%05d', random_int(0, 99999));

        $user = new User();
        $user->setNni($nni);
        $user->setPassword('password123456');
        $user->setRoles(['ROLE_MANAGER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->clear();

        // Act
        $result = $this->repository->findByNni($nni);

        // Assert
        $this->assertNotNull($result, 'Un utilisateur devrait être trouvé par son NNI');
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($nni, $result->getNni());
    }

    // Test 5 : findByNni retourne null si l'utilisateur n'est pas trouvé
    #[Test]
    public function findByNniReturnsNullIfUserNotFound(): void
    {
        // Arrange : s'assurer qu'aucun User n’a ce NNI
        $nni = 'Z99999';

        // Act
        $result = $this->repository->findByNni($nni);

        // Assert
        $this->assertNull($result);
    }

    // Test 6 : save enregistre un utilisateur
    #[Test]
    public function savePersistsUser(): void
    {
        // Arrange
        $user = new User();

        // Crée un EntityManager avec des expectations spécifiques
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($user);
        $entityManager->expects($this->once())
            ->method('flush');

        // Utilise le helper avec l'EntityManager personnalisé
        $registry = $this->createConfiguredRegistry($entityManager);
        $repository = new UserRepository($registry);

        // Act
        $repository->save($user);

        // Assert
        $this->assertTrue(true); // Si le code arrive ici, le test est réussi
    }

    // Test 7 : save lance une exception pour un mauvais type
    #[Test]
    public function saveThrowsExceptionForWrongUserType(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $wrongUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        $registry = $this->createConfiguredRegistry();
        $repository = new UserRepository($registry);

        $repository->save($wrongUser);
    }

    // Test 8 : remove supprime un utilisateur
    #[Test]
    public function removeRemovesUser(): void
    {
        // Arrange
        $user = new User();

        // Crée un EntityManager avec des expectations spécifiques
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('remove')
            ->with($user);
        $entityManager->expects($this->once())
            ->method('flush');

        // Utilise le helper avec l'EntityManager personnalisé
        $registry = $this->createConfiguredRegistry($entityManager);
        $repository = new UserRepository($registry);

        // Act
        $repository->remove($user);

        // Assert
        $this->assertTrue(true); // Si le code arrive ici, le test est réussi
    }

    // Test 9 : remove lance une exception pour un mauvais type
    #[Test]
    public function removeThrowsExceptionForWrongUserType(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $wrongUser = $this->createMock(PasswordAuthenticatedUserInterface::class);

        $registry = $this->createConfiguredRegistry();
        $repository = new UserRepository($registry);

        $repository->remove($wrongUser);
    }

    /**
     * Helper pour créer un ManagerRegistry configuré minimalement
     * 
     * @param EntityManagerInterface|null $entityManager EntityManager personnalisé (optionnel)
     * @return ManagerRegistry
     */
    private function createConfiguredRegistry(?EntityManagerInterface $entityManager = null): ManagerRegistry
    {
        // Mock ClassMetadata
        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = User::class;

        // Utilise l'EntityManager fourni ou en crée un par défaut
        if ($entityManager === null) {
            $entityManager = $this->createMock(EntityManagerInterface::class);
        }

        $entityManager->method('getClassMetadata')
            ->with(User::class)
            ->willReturn($classMetadata);

        // Mock ManagerRegistry
        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')
            ->with(User::class)
            ->willReturn($entityManager);

        return $registry;
    }
}
