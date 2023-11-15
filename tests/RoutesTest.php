<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class RoutesTest extends WebTestCase
{

    public function testLogin()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();

    }

    public function testAccueil()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects();
    }

    public function testAllRoutesRedirect()
    {
        $client = static::createClient();
        $routes = ['/','/register','/user','/site','/lieu','/ville'];
        // foreach routes, make a call and assert
        foreach ($routes as $route) {
            $crawler = $client->request('GET', $route);
            $this->assertResponseStatusCodeSame(302);
            $this->assertResponseRedirects();
        }
    }

    public function testVisitingWhileAdminLoggedIn(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByEmail('ilane.bohan@gmail.com');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $routes = ['/','/register','/cgu','/user/list','/site/','/lieu/','/ville/','/user/details/'.$testUser->getId()];
        // foreach routes, make a call and assert
        foreach ($routes as $route) {
            $crawler = $client->request('GET', $route);
            $this->assertResponseIsSuccessful();
        }

    }
}
