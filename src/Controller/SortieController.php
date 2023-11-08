<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Form\CreateSortieType;
use App\Form\CreateSortieWithLieuType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

    #[Route('/annuler/{id}', name: 'app_sortie_annuler')]
    public function annuler(int $id, SortieRepository $sortieRepository, EntityManagerInterface $em, EtatRepository $etatRepository): Response
    {
        $user = $this->getUser();
        $sortie = $sortieRepository->find($id);
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            $sortie->setEtat($etatRepository->find(6));
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
        if ($user !== $sortie->getOrganisateur()) {
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
        $sortie->removeParticipant($user);
        $em->persist($sortie);
        $em->flush();

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
