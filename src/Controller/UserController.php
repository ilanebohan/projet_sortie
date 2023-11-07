<?php

namespace App\Controller;

use App\Form\EditUserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

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
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditUserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($form->get('plainPassword')->getData() == $form->get('confirmation')->getData()){
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }
            else{
                return $this->redirectToRoute('user_edit', array('pwdDifferent' => true));
            }

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_main');
        }

        return $this->render('user/edit.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
