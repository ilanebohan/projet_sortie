<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route('/accessDenied/{statusCode}', name: 'app_access_denied')]
    public function index($statusCode): Response
    {
        return $this->render('error/error.html.twig', [
            'controller_name' => 'ErrorController',
            'statusCode' => $statusCode
        ]);
    }
}
