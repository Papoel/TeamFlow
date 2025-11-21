<?php

namespace App\Tests;

use App\Tests\Traits\RefreshDatabase;
use App\Tests\Traits\InteractsWithUsers;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    use RefreshDatabase;
    use InteractsWithUsers;
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        parent::setUp(); // Appelle RefreshDatabase::setUp() qui nettoie la BDD

        // Créer un utilisateur de test en 1 ligne
        $this->createUser('Z54321', 'password');
    }

    #[Test]
    public function login(): void
    {
        $route_name = 'app_login';
        $route_path = '/login';

        // Denied - Can't login with invalid NNI.
        $this->client->request(Request::METHOD_GET, $route_path);
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'A12345',
            '_password' => 'password',
        ]);

        self::assertRouteSame($route_name);
        self::assertResponseRedirects($route_path);
        $this->client->followRedirect();

        // Ensure we do not reveal if the user exists or not.
        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');

        // Denied - Can't login with invalid password.
        $this->client->request(Request::METHOD_GET, $route_path);
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'Z54321',
            '_password' => 'bad-password',
        ]);

        self::assertRouteSame($route_name);
        self::assertResponseRedirects($route_path);
        $this->client->followRedirect();

        // Ensure we do not reveal the user exists but the password is wrong.
        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');

        // Success - Login with valid credentials is allowed.
        $this->client->submitForm('Sign in', [
            '_username' => 'Z54321',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertRouteSame('app_home');

        self::assertSelectorNotExists('.alert-danger');
        self::assertResponseIsSuccessful();
    }
}
