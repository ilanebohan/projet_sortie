<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccessDeniedListener implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;


    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // the priority must be greater than the Security HTTP
            // ExceptionListener, to make sure it's called before
            // the default exception listener
            KernelEvents::EXCEPTION => ['onKernelException', 2],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof AccessDeniedException) {
            return;
        }

        // ... perform some action (e.g. logging)

        // optionally set the custom response

        $request = $event->getRequest();
        if ($request->getRequestUri() == "/") {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_login')));

        }
        else
        {
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_access_denied', ['statusCode' => Response::HTTP_FORBIDDEN])));
        }

        // or stop propagation (prevents the next exception listeners from being called)
        //$event->stopPropagation();
    }
}