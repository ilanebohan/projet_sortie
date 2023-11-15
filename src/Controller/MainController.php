<?php

namespace App\Controller;

use App\Entity\EtatEnum;
use App\Entity\Site;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Array_;
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED',
            null,
            'User tried to access a page without being authenticated'
        );


        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findAll();

        #region Filtre de base
        $array = array();
        $user = $this->getUser();
        $array['idSite'] = $user->getSite()->getId();
        $array['organisateur'] = true;
        $array['inscrit'] = true;
        $array['nonInscrit'] = true;
        $array['userid'] = $user->getId();

        #endregion

        #region filtre obligatoire mais inutiles
        $array['StringSearch'] = null;
        $array['DateDebut'] = null;
        $array['DateFin'] = null;
        $array['SortiePassee'] = null;
        $array['statutId'] = null;
        #endregion


        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findByFilter($array);


        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'sites' => $sites,
            'DateDuJour' => now(),
            'sorties' => $sorties,
            'idSiteUtilisateur' => $user->getSite()->getId(),
            'FirstConnexionAfterLogIn' => true,
        ]);
    }

    #[Route('/home', name: 'app_main_filter')]
    public function IndexWithFilterForTable(Request $request, EntityManagerInterface $entityManager): Response
    {
        $filter = array();

#region Get Filter

        $idSite = $request->get('site');
        $stringSearch = $request->get('searchBar');
        $dateDebut = $request->get('dateDebut');
        $dateFin = $request->get('dateFin');
        $organisateur = $request->get('organisateur');
        $inscrit = $request->get('inscrit');
        $nonInscrit = $request->get('nonInscrit');
        $sortiePassee = $request->get('passee');
        $statutId = $request->get('statut');
        $userid = $this->getUser()->getId();

#endregion

#region Setup ArrayFilter

        $filter['idSite'] = $idSite;
        $filter['StringSearch'] = $stringSearch;
        $filter['DateDebut'] = $dateDebut;
        $filter['DateFin'] = $dateFin;
        $filter['organisateur'] = $organisateur;
        $filter['inscrit'] = $inscrit;
        $filter['nonInscrit'] = $nonInscrit;
        $filter['SortiePassee'] = $sortiePassee;
        $filter['userid'] = $userid;
        $filter['statutId'] = $statutId;

#endregion

        $siteRepository = $entityManager->getRepository(Site::class);
        $sites = $siteRepository->findAll();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findByFilter($filter);

        return $this->render('main/index.html.twig', [
            'sites' => $sites,
            'DateDuJour' => now(),
            'sorties' => $sorties,
            'idSiteUtilisateur' => $this->getUser()->getSite()->getId(),
            'FirstConnexionAfterLogIn' => false,
        ]);
    }
}
