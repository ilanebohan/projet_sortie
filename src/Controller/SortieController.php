<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\CreateSortieType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/create', name: 'app_sortie')]
    public function create(Request $request): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(CreateSortieType::class, $sortie);
        $form->handleRequest($request);
        $addLieu = false;
        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            if($data->getAddLieu()){
                $addLieu = true;
            }
        }
        return $this->render('sortie/create.html.twig', [
            'sortieForm' => $form->createView(),
            'addLieu' => $addLieu,
        ]);
    }
}
