<?php

namespace App\EventListener;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

class ControllerListener extends AbstractController
{
    private Security $security;
    private UserRepository $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private RouterInterface $router;

    public function __construct(Security $security, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, RouterInterface $router){
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->passwordHasher = $passwordHasher;
        $this->router = $router;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $authorized = [ 'app_disconnect', 'app_login', 'user_reset_passwordLogin'];
        if(!in_array($event->getRequest()->attributes->get('_route'), $authorized)){
            if($this->security->getUser()){
                $user = $this->userRepository->findUserByLogin($this->security->getUser()->getUserIdentifier());
                if ($this->passwordHasher->isPasswordValid($user, 'password'))
                {
                    $response = new RedirectResponse($this->generateUrl('user_reset_passwordLogin', ['id' => $user->getId()]));

                    $event->setController(function () use ($response) {
                        return $response;
                    });
                }
            }
        }
    }
}