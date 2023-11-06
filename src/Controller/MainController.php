<?php

namespace App\Controller;

use App\Entity\EtatEnum;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Clock\now;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $Siterepository = $entityManager->getRepository(Site::class);
        $sites = $Siterepository->findAll();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findAll();


        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'sites' => $sites,
            'DateDuJour' => now(),
            'sorties' => $sorties,
            'EtatEnum' => EtatEnum::class,
        ]);
    }

    #[Route('/{id}', name: 'app_main_filter')]
    public function IndexWithFilterForTable(EntityManagerInterface $entityManager) : Response
    {
        $Siterepository = $entityManager->getRepository(Site::class);
        $sites = $Siterepository->findAll();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findAll(); // TODO : filter

        return $this->render('main/index.html.twig', [
            'sites' => $sites,
            'DateDuJour' => now(),
            'sorties' => $sorties,
            'EtatEnum' => EtatEnum::class,
        ]);
    }
}
