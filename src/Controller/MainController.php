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
        $Siterepository = $entityManager->getRepository(Site::class);
        $sites = $Siterepository->findAll();

        #region Filtre de base
        $array = Array();
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

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED', null, 'User tried to access a page without being authenticated');

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
        $Filter = array();

#region Get Filter

        $idSite = $request->get('site');
        $StringSearch = $request->get('searchBar');
        $DateDebut = $request->get('dateDebut');
        $DateFin = $request->get('dateFin');
        $organisateur = $request->get('organisateur');
        $inscrit = $request->get('inscrit');
        $nonInscrit = $request->get('nonInscrit');
        $SortiePassee = $request->get('passee');
        $statutId = $request->get('statut');
        $userid = $this->getUser()->getId();

#endregion

#region Setup ArrayFilter

        $Filter['idSite'] = $idSite;
        $Filter['StringSearch'] = $StringSearch;
        $Filter['DateDebut'] = $DateDebut;
        $Filter['DateFin'] = $DateFin;
        $Filter['organisateur'] = $organisateur;
        $Filter['inscrit'] = $inscrit;
        $Filter['nonInscrit'] = $nonInscrit;
        $Filter['SortiePassee'] = $SortiePassee;
        $Filter['userid'] = $userid;
        $Filter['statutId'] = $statutId;

#endregion

        $Siterepository = $entityManager->getRepository(Site::class);
        $sites = $Siterepository->findAll();

        $sortieRepository = $entityManager->getRepository(Sortie::class);
        $sorties = $sortieRepository->findByFilter($Filter);

        return $this->render('main/index.html.twig', [
            'sites' => $sites,
            'DateDuJour' => now(),
            'sorties' => $sorties,
            'idSiteUtilisateur' => $this->getUser()->getSite()->getId(),
            'FirstConnexionAfterLogIn' => false,
        ]);
    }
}
