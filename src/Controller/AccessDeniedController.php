<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccessDeniedController extends AbstractController
{
    #[Route('/accessDenied/{statusCode}', name: 'app_access_denied')]
    public function index($statusCode): Response
    {
        return $this->render('access_denied/accessDenied.html.twig', [
            'controller_name' => 'AccessDeniedController',
            'statusCode' => $statusCode
        ]);
    }
}
