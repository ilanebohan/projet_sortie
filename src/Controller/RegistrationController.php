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
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_ADMIN')]
class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // If the login is already taken, return an error
            $userRepo = $entityManager->getRepository(User::class);
            $userLogin = $userRepo->findOneBy(['login' => $user->getLogin()]);
            if ($userLogin)
            {
                $this->addFlash('error', 'Ce login est déjà utilisé');
                //return $this->redirectToRoute('app_register');
            }

            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setActif(true);


            // check that the phone number is in this format : "0X XX XX XX XX" otherwise, put space between numbers
            $phone = $user->getTelephone();
            // put spaces every 2 number if there is none
            if (strlen($phone) == 10)
            {
                $phone = substr($phone, 0, 2) . ' ' . substr($phone, 2, 2) . ' ' . substr($phone, 4, 2) . ' ' . substr($phone, 6, 2) . ' ' . substr($phone, 8, 2);
            }

            // make telephone nullable in database
            if ($phone == null)
            {
                $user->setTelephone(" ");
            }
            else {
                $user->setTelephone($phone);
            }


            $image = $form->get('image')->getData();

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $user->setImageFilename($newFilename);
            }

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
