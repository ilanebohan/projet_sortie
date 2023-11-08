<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Exception\UserInactiveException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    private UserRepository $userRepository;

    public function __construct(private UrlGeneratorInterface $urlGenerator, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(Request $request): Passport
    {
        $emailOrUsername  = $request->request->get('email_or_username', '');

        // if emailOrUsername is email then get user by email
        if (filter_var($emailOrUsername, FILTER_VALIDATE_EMAIL)) {
            $user = $this->userRepository->findOneBy(['email' => $emailOrUsername]);
        }
        // else if emailOrUsername is username then get user by username
        else {
            $user = $this->userRepository->findOneBy(['login' => $emailOrUsername]);
        }

        // $user to UserInterface type
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $emailOrUsername);

        if ($user->isActif())
        {
            // if checkbox remember me is checked then add RememberMeBadge()
            if ($request->request->get('_remember_me') == 'on') {
                return new Passport(
                    new UserBadge($user->getEmail()),
                    new PasswordCredentials($request->request->get('password', '')),
                    [
                        new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                        new RememberMeBadge(),
                    ]
                );
            }
            // else if checkbox remember me is not checked then do not add RememberMeBadge()
            else
            {
                return new Passport(
                    new UserBadge($user->getEmail()),
                    new PasswordCredentials($request->request->get('password', '')),
                    [
                        new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                    ]
                );
            }
        }
        else
        {
            throw new UserInactiveException();
        }


    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // For example:
        // return new RedirectResponse($this->urlGenerator->generate('some_route'));
        return new RedirectResponse($this->urlGenerator->generate('app_main'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
