<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExceptionListener
{

    private UrlGeneratorInterface $urlGenerator;


    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();


        if ($exception instanceof AccessDeniedException) {
            switch ($request->getRequestUri()){
                case "/":
                    $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_login')));
                    break;
                default:
                    $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_access_denied', ['statusCode' => Response::HTTP_FORBIDDEN])));
                    break;
            };
        }
        else if ($exception instanceof NotFoundHttpException)
        {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_access_denied', ['statusCode' => Response::HTTP_NOT_FOUND])));
        }
        else
        {
            // @TODO : DECOMMENTER AVANT LA PRESENTATION
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_access_denied', ['statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR])));
        }

        // inspect the exception
        // do whatever else you want, logging, modify the response, etc, etc
        //$event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_access_denied', ['statusCode' => "404"])));
    }

}