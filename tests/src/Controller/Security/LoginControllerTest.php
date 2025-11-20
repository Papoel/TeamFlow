<?php

namespace App\Tests;

use App\Entity\User\User;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(User::class);

        // Remove any existing users from the test database
        foreach ($userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();

        // Create a User fixture
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');

        $user = (new User())->setNni('Z54321');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));

        $em->persist($user);
        $em->flush();
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
