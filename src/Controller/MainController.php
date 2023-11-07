<?php

namespace App\Controller;

use App\Entity\EtatEnum;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/home', name: 'app_main_filter')]
    public function IndexWithFilterForTable(Request $request, EntityManagerInterface $entityManager) : Response
    {
        $Filter = array();

        #region Get Filter

        $idSite = $request->get('site');
        $StringSearch = $request->get('searchBar');
        $DateDebut = $request->get('DateDebut');
        $DateFin = $request->get('DateFin');
        $organisateur = $request->get('orga');
        $inscrit = $request->get('inscrit');
        $nonInscrit = $request->get('nonInscrit');
        $SortiePassee = $request->get('passee');
        $userid = $this->getUser()->getId();

        #endregion

        #region Setup ArrayFilter

        $Filter->set('idSite', $idSite);
        $Filter->set('StringSearch', $StringSearch);
        $Filter->set('DateDebut', $DateDebut);
        $Filter->set('DateFin', $DateFin);
        $Filter->set('organisateur', $organisateur);
        $Filter->set('inscrit', $inscrit);
        $Filter->set('nonInscrit', $nonInscrit);
        $Filter->set('SortiePassee', $SortiePassee);
        $Filter->set('userid', $userid);

        #endregion


        $Siterepository = $entityManager->getRepository(Site::class);
        $sites = $Siterepository->findAll();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findByFilter($Filter); // TODO : filter

        return $this->render('main/index.html.twig', [
            'sites' => $sites,
            'DateDuJour' => now(),
            'sorties' => $sorties,
            'EtatEnum' => EtatEnum::class,
        ]);
    }
}
