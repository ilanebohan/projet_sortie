<?php

namespace App\Controller;

use App\Form\EditUserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/details/{id}', name: 'user_details', requirements: ['id' => '\d+'])]
    public function details(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->findUserById($id);
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'user'=> $user
        ]);
    }
    #[Route('/user/delete/{id}', name: 'user_delete', requirements: ['id' => '\d+'])]
    public function deleteUser(int $id, UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findUserById($id);
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $messageRetour = 'Utilisateur ' . $user->getLogin()  . ' supprimé';
        }

        return $this->redirectToRoute('user_list', ['messageRetour'=>$messageRetour], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user/desactivate/{id}', name: 'user_desactivate', requirements: ['id' => '\d+'])]
    public function desactivateUser(int $id, UserRepository $userRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findUserById($id);
        $user->setActif(false);
        $entityManager->persist($user);
        $entityManager->flush();

        $user = $userRepository->findUserById($id);
        $messageRetour = '';
        if (!$user->isActif())
        {
            $messageRetour = 'Utilisateur ' . $user->getLogin()  . ' désactivé';
        }
        else
        {
            $messageRetour = 'Erreur de lors de la désactivation de ' . $user->getLogin();
        }

        return $this->redirectToRoute('user_list', ['messageRetour'=>$messageRetour], Response::HTTP_SEE_OTHER);
    }


    #[Route('/user/list', name: 'user_list')]
    public function listUser(String $messageRetour = null, UserRepository $userRepository): Response
    {
        return $this->render('user/list.html.twig', [
            'controller_name' => 'UserController',
            'users'=> $userRepository->findAll(),
            'messageRetour' => $messageRetour
        ]);
    }

    #[Route('/user/edit', name: 'user_edit')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditUserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $image = $form->get('image')->getData();

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                $projectDir = $this->getParameter('public_directory').'/uploads/images/'.$user->getImageFilename();
                $fileSystem = new Filesystem();
                if($fileSystem->exists($projectDir)){
                    $fileSystem->remove($projectDir);
                }
                else{
                    $user->setImageFilename($newFilename);
                }



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

        return $this->render('user/edit.html.twig', [
            'registrationForm' => $form->createView(),
            'fileName' => $user->getImageFilename()
        ]);
    }
}
