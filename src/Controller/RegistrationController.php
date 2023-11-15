<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[IsGranted('ROLE_ADMIN')]
class RegistrationController extends AbstractController
{

    #[Route('/register', name: 'app_register')]
    public function register(Request $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             SluggerInterface $slugger,
                             EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seed = str_split('abcdefghijklmnopqrstuvwxyz'
                . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                . '0123456789!@#$%^&*()');
            shuffle($seed);
            $login = "";
            foreach (array_rand($seed, 10) as $k)
            {
                $login .= $seed[$k];
            }
            $user->setLogin($login);

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword(
                $user, 'password'
            ));
            $user->setActif(true);


            // check that the phone number is in this format : "0X XX XX XX XX" otherwise, put space between numbers
            $phone = $user->getTelephone();
            // put spaces every 2 number if there is none
            if ($phone != null && strlen($phone) == 10) {
                $phone = substr($phone, 0, 2)
                    . ' ' .
                    substr($phone, 2, 2)
                    . ' ' .
                    substr($phone, 4, 2)
                    . ' ' .
                    substr($phone, 6, 2)
                    . ' ' .
                    substr($phone, 8, 2);
            }

            // make telephone nullable in database
            if ($phone == null) {
                $user->setTelephone(" ");
            } else {
                $user->setTelephone($phone);
            }

            $user->setAllowImageDiffusion(false);

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('user_list');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

}