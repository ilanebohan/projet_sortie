<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class SecurityController extends AbstractController
{

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        if (isset($_COOKIE['remember_me']))
        {
            $lastUsername = openssl_decrypt($_COOKIE['remember_me'], "AES-128-ECB", $this->getParameter('secret_key'));
        }
        else
        {
            $lastUsername = $authenticationUtils->getLastUsername();
        }
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error,'mailError' => '',]);
    }

    #[Route(path: '/disconnect', name: 'app_disconnect')]
    public function disconnect()
    {
        if (isset($_COOKIE['REMEMBERME']))
        {
            $hash = openssl_encrypt($this->getUser()->getUserIdentifier(), "AES-128-ECB", $this->getParameter('secret_key'));
            setcookie('remember_me',$hash, time() + 3600, '/');
        }
        if (!isset($_COOKIE['REMEMBERME']))
        {
            if (isset($_COOKIE['remember_me']))
            {
                unset($_COOKIE['remember_me']);
                setcookie('remember_me', null, -1, '/');
            }
        }
            return $this->redirectToRoute('app_logout');
    }


    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): never
    {
        // controller can be blank: it will never be called!
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
