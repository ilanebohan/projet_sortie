<?php

namespace App\Tests;

use App\Entity\Ville;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VilleTest extends WebTestCase
{

    public function testSomething(): void
    {/*
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello World');*/
        $this->createVille();
    }

    private function createVille()
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $ville = new Ville();
        $ville->setNom("maVille");
        $ville->setCodePostal("44000");

        $entityManager->persist($ville);
        $entityManager->flush();

        //$villeBDD = $villeRepository->findOneBy(array('id' => $id));
        $villeBDD = $entityManager->getRepository(Ville::class)->find($ville->getId());

        $this->assertEquals($ville->getId(), $villeBDD->getId());
        $this->assertEquals($ville->getNom(), $villeBDD->getNom());
        $this->assertEquals($ville->getCodePostal(), $villeBDD->getCodePostal());
    }
}
