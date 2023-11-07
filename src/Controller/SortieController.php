<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CreateSortieType;
use App\Form\CreateSortieWithLieuType;
use App\Repository\EtatRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/create', name: 'app_sortie')]
    public function create(Request $request, EntityManagerInterface $em, EtatRepository $etatRepository, UserRepository $userRepository): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(CreateSortieType::class, $sortie);
        $form->handleRequest($request);
        $formWithLieu = $this->createForm(CreateSortieWithLieuType::class, $sortie);
        $formWithLieu->handleRequest($request);
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
