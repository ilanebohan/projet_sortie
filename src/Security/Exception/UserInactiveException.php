<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class UserInactiveException extends AuthenticationException
{

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Désolé, votre compte est inactif, veuillez contacter un administrateur.';
    }

}