<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED', null, 'User tried to access a page without having ROLE_ADMIN');
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }
}
