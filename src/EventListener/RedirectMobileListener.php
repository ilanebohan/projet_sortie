<?php

namespace App\EventListener;


use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectMobileListener
{
    public function redirectMobile(RequestEvent $request)
    {
        $isRouteAuthorized = false;
        $authorizedRoutes = ["",'/','/login','/disconnect',"/sortie/afficher/"];
        $appareils = ['iPod','iPhone','BlackBerry','Windows Phone','Mobile', 'Pixel', 'moto g'];

        // get UserAgent from request
        $userAgent = $request->getRequest()->headers->get('User-Agent');
        // Si l'utilisateur est sur un appareil mobile de la liste $appareils
        foreach ($appareils as $appareil)
        {
            if (str_contains($userAgent, $appareil)) {
                // Si la route de la requête contient une route de la liste $authorizedRoutes
                foreach ($authorizedRoutes as $route)
                {
                    if (str_contains($request->getRequest()->getPathInfo(), $route))
                    {
                        $isRouteAuthorized = true;
                    }
                }
                if (!$isRouteAuthorized)
                {
                    // Redirection vers la page d'erreur
                    $request->setResponse(new RedirectResponse('/accessDenied/403'));
                }

            }
        }
    }
}
