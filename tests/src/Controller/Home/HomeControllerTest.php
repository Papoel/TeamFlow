<?php

namespace App\Tests\Controller\Home;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    #[Test]
    public function indexShouldReturnRouteAndStatus(): void
    {
        $client = static::createClient();
        $client->request(Request::METHOD_GET, '/');
        // Check the name of the route
        self::assertRouteSame('app_home');
        self::assertResponseIsSuccessful();
    }
}
