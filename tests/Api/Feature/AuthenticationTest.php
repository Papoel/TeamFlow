<?php

namespace App\Tests\Api\Feature;

use App\Entity\User\User;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Nettoyer la base de données avant chaque test
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->createQuery('DELETE FROM App\Entity\User\User')->execute();
        $entityManager->clear();
    }

    #[Test]
    public function apiLoginSuccessfullyAuthenticatesUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        // Créer un utilisateur de test
        $user = new User();
        $user->setNni('A34123');
        $hashedPassword = $container->get('security.user_password_hasher')
            ->hashPassword($user, '$3CR3T');
        $user->setPassword($hashedPassword);

        $manager = $container->get('doctrine')->getManager();
        $manager->persist($user);
        $manager->flush();

        // Étape 1 : Récupérer un token JWT
        $response = $client->request(Request::METHOD_POST, '/api/auth/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'nni' => 'A34123',
                'password' => '$3CR3T',
            ],
        ]);

        self::assertResponseIsSuccessful();
        $json = $response->toArray();
        self::assertArrayHasKey('token', $json);
        self::assertNotEmpty($json['token']);

        // Étape 2 : Tester l'accès non autorisé (sans token)
        $client->request(Request::METHOD_GET, '/api/users');
        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);

        // Étape 3 : Tester l'accès autorisé (avec token)
        $client->request(Request::METHOD_GET, '/api/users', [
            'auth_bearer' => $json['token'],
        ]);
        self::assertResponseIsSuccessful();
    }

    #[Test]
    public function apiLoginFailsWithInvalidCredentials(): void
    {
        $client = static::createClient();

        // Tenter de se connecter avec des identifiants invalides
        $client->request(Request::METHOD_POST, '/api/auth/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'nni' => 'A99999',
                'password' => 'wrongpassword',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    #[Test]
    public function apiLoginFailsWithMissingCredentials(): void
    {
        $client = static::createClient();

        // Tenter de se connecter sans mot de passe
        $client->request(Request::METHOD_POST, '/api/auth/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'nni' => 'A34123',
            ],
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
