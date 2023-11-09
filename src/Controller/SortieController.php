<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Form\AnnulerSortieType;
use App\Form\CreateSortieType;
use App\Form\CreateSortieWithLieuType;
use App\Form\EditSortieFormType;
use App\Form\EditSortieWithoutAnnulationFormType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/sortie')]
class SortieController extends AbstractController
{

    #[Route('/publish/{id}', name: 'app_sortie_publish')]
    public function publish(int $id, SortieRepository $sortieRepository, EntityManagerInterface $em, EtatRepository $etatRepository): Response
    {
        $user = $this->getUser();
        $sortie = $sortieRepository->find($id);
        if ($user === $sortie->getOrganisateur()) {
            $sortie->setEtat($etatRepository->find(2));
            $em->persist($sortie);
            $em->flush();
        }
        return $this->redirectToRoute('app_main');
    }

    #[Route('/inscrire/{id}', name: 'app_sortie_inscrire')]
    public function inscrire(int $id, SortieRepository $sortieRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $sortie = $sortieRepository->find($id);
        if ($user !== $sortie->getOrganisateur()
            && $sortie->getEtat()->getLibelle() == "Ouverte"
            && $sortie->getDateCloture() > new DateTime('now')) {
            $sortie->addParticipant($user);
            $em->persist($sortie);
            $em->flush();
        }
        return $this->redirectToRoute('app_main');
    }

    #[Route('/desister/{id}', name: 'app_sortie_desister')]
    public function desister(int $id, SortieRepository $sortieRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $sortie = $sortieRepository->find($id);
        if ($sortie->getEtat()->getLibelle() == "Ouverte"
            || $sortie->getEtat()->getLibelle() == "Clôturée") {
            $sortie->removeParticipant($user);
            $em->persist($sortie);
            $em->flush();
        }

        return $this->redirectToRoute('app_main');
    }

    #[Route('/afficher/{id}', name: 'app_sortie_afficher')]
    public function afficher(int $id, Request $request, SortieRepository $sortieRepository, EntityManagerInterface $em) : Response
    {
        $sortie = $sortieRepository->find($id);

        if($sortie)
        {
            return $this->render('sortie/afficher.html.twig', [
                'sortie' => $sortie
            ]);
        }
        return $this->redirectToRoute('app_main');
    }



    #[Route('/annuler/{id}', name: 'app_sortie_annuler')]
    public function annuler(int $id, Request $request, SortieRepository $sortieRepository, EntityManagerInterface $em, EtatRepository $etatRepository): Response
    {
        $user = $this->getUser();
        $sortie = $sortieRepository->find($id);
        if (in_array("ROLE_ADMIN", $user->getRoles()) or $user === $sortie->getOrganisateur()
            && $sortie->getEtat()->getLibelle() == "Ouverte"
            && $sortie->getDateDebut() > new DateTime('now')) {
            $form = $this->createForm(AnnulerSortieType::class,$sortie);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $sortie->setEtat($etatRepository->find(6));
                $em->persist($sortie);
                $em->flush();
                return $this->redirectToRoute('app_main');
            }
            return $this->render('sortie/annuler.html.twig', [
                'sortieForm' => $form->createView(),
                'sortie' => $sortie,
            ]);


        }
        return $this->redirectToRoute('app_main');
    }

    #[Route('/create', name: 'app_sortie_create')]
    public function create(Request $request, EntityManagerInterface $em, EtatRepository $etatRepository, LieuRepository $lieuRepository, VilleRepository $villeRepository): Response
    {
        $sortie = new Sortie();
        $ville = $villeRepository->find(1);
        $form = $this->createForm(CreateSortieType::class, $sortie, ['villeId' => $ville]);
        $form->handleRequest($request);
        $formWithLieu = $this->createForm(CreateSortieWithLieuType::class, $sortie);
        $formWithLieu->handleRequest($request);
        $lieux = $lieuRepository->findAll();
        if ($form->get('addLieu')->isClicked() || $formWithLieu->isSubmitted()) {
            if ($formWithLieu->isSubmitted() && $formWithLieu->isValid()) {
                $this->save($sortie, $etatRepository, $em);

                return $this->redirectToRoute('app_main');
            }
            return $this->render('sortie/createWithLieu.html.twig', [
                'sortieForm' => $formWithLieu->createView(),
            ]);
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $this->save($sortie, $etatRepository, $em);

            return $this->redirectToRoute('app_main');
        }
        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $form->createView(),
            'lieux' => $lieux
        ]);
    }


    #[Route('/sortie/edit/{id}', name: 'app_sortie_edit')]
    public function edit(int $id, Request $request, SortieRepository $sortieRepository , EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->find($id);
        $etat = $sortie->getEtat()->getLibelle();
        $form = $this->createForm(EditSortieFormType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('app_main');
        }

        return $this->render('sortie/edit.html.twig', [
            'SortieForm' => $form->createView(),
            'EtatSortie' => $etat,
        ]);
    }


    /**
     * @param Sortie $sortie
     * @param EtatRepository $etatRepository
     * @param EntityManagerInterface $em
     * @return void
     */
    public function save(Sortie $sortie, EtatRepository $etatRepository, EntityManagerInterface $em): void
    {
        $sortie->setEtat($etatRepository->find(1));
        $user = $this->getUser();
        $sortie->setOrganisateur($user);
        $em->persist($sortie);
        $em->flush();

        $this->addFlash("success", "La Sortie a bien été crée");
    }
}
