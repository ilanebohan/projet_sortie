<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\User;
use App\Form\AddUserCsvType;
use App\Form\ChangePasswordAndLoginType;
use App\Form\ChangePasswordType;
use App\Form\EditUserFormType;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
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
            'user' => $user
        ]);
    }

    #[Route('/user/delete/{id}', name: 'user_delete', requirements: ['id' => '\d+'])]
    public function deleteUser(int $id = null,
                               UserRepository $userRepository,
                               EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findUserById($id);
        try {
            $entityManager->remove($user);
            $entityManager->flush();
            $messageRetour = 'Utilisateur ' . $user->getLogin() . ' supprimé';
        } catch (ForeignKeyConstraintViolationException $e) {
            $messageRetour = 'L\'utilisateur ' . $user->getLogin() . ' ne peut pas être supprimé';
        }

        return $this->redirectToRoute('user_list', ['messageRetour' => $messageRetour], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user/deleteMessage/{id}', name: 'user_delete_message', requirements: ['id' => '\d+'])]
    public function deleteUserMessage(int $id = null,
                                      UserRepository $userRepository,
                                      EntityManagerInterface $entityManager): string
    {
        $user = $userRepository->findUserById($id);
        try {
            $entityManager->remove($user);
            $entityManager->flush();
            $messageRetour = 'Utilisateur ' . $user->getLogin() . ' supprimé';
        } catch (ForeignKeyConstraintViolationException $e) {
            $messageRetour = 'L\'utilisateur ' . $user->getLogin() . ' ne peut pas être supprimé';
        }

        return $messageRetour;
    }

    #[Route('/user/desactivate/{id}', name: 'user_desactivate', requirements: ['id' => '\d+'])]
    public function desactivateUser(int $id = null,
                                    UserRepository $userRepository,
                                    EntityManagerInterface $entityManager): Response
    {
        $user = $userRepository->findUserById($id);
        $user->setActif(false);
        $entityManager->persist($user);
        $entityManager->flush();
        $user = $userRepository->findUserById($id);
        $messageRetour = '';
        if (!$user->isActif()) {
            $messageRetour = 'Utilisateur ' . $user->getLogin() . ' désactivé';
        } else {
            $messageRetour = 'Erreur de lors de la désactivation de ' . $user->getLogin();
        }


        return $this->redirectToRoute('user_list', ['messageRetour' => $messageRetour], Response::HTTP_SEE_OTHER);
    }

    #[Route('/user/list', name: 'user_list')]
    public function listUser(Request $request,
                             UserRepository $userRepository,
                             SluggerInterface $slugger,
                             EntityManagerInterface $entityManager,
                             SiteRepository $siteRepository,
                             UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(AddUserCsvType::class, $user);
        $form->handleRequest($request);
        $messageRetour = $request->get('messageRetour');
        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('file')->getData();

            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            $projectDir = $this->getParameter('public_directory') . '/uploads/csv/' . $newFilename;

            try {
                $file->move(
                    $this->getParameter('csv_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $reader = Reader::createFromPath($projectDir, 'r');

            for ($i = 0; $i <= $reader->count() - 1; $i++) {
                if ($i == 0) {
                    $i++;
                }
                $row = $reader->fetchOne($i);
                $userReader = new User();
                $util = explode(";", $row[0]);

                $userReader->setNom($util[0]);
                $userReader->setPrenom($util[1]);
                $userReader->setTelephone($util[2]);
                $userReader->setEmail($util[3]);
                if ($util[4] == 'oui') {
                    $userReader->setAdministrateur(true);
                } else {
                    $userReader->setAdministrateur(false);
                }
                $seed = str_split('abcdefghijklmnopqrstuvwxyz'
                    . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                    . '0123456789!@#$%^&*()');
                shuffle($seed);
                $login = "";
                foreach (array_rand($seed, 10) as $k) {
                    $login .= $seed[$k];
                }
                $userReader->setLogin($login);
                $userReader->setPassword($userPasswordHasher->hashPassword(
                    $userReader, 'password'
                ));
                $userReader->setAllowImageDiffusion(false);

                $site = $siteRepository->findSiteByNom($util[5]);
                if ($site) {
                    $userReader->setSite($site[0]);
                } else {
                    $entitySite = new Site();
                    $entitySite->setNom($util[5]);
                    $entityManager->persist($entitySite);
                    $entityManager->flush();
                    $userReader->setSite($entitySite);
                }
                $userReader->setActif(true);
                $entityManager->persist($userReader);
                $entityManager->flush();

                $fileSystem = new Filesystem();
                if ($fileSystem->exists($projectDir)) {
                    $fileSystem->remove($projectDir);
                }
            }

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/list.html.twig', [
            'controller_name' => 'UserController',
            'users' => $userRepository->findAll(),
            'messageRetour' => $messageRetour,
            'registrationForm' => $form->createView()
        ]);
    }

    #[Route('/user/edit/resetPassword/{id}', name: 'user_reset_password')]
    public function resetPassword(int $id,
                                  EntityManagerInterface $entityManager,
                                  UserPasswordHasherInterface $userPasswordHasher,
                                  Request $request,
                                  UserRepository $userRepository,
                                  UserPasswordHasherInterface $passwordHasher): Response
    {
        $idUserConnected = $userRepository->findUserByLogin($this->getUser()->getUserIdentifier())->getId();

        if($id != $idUserConnected){
            return $this->redirectToRoute('app_access_denied', ['statusCode' => 403]);
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        $user = $userRepository->findUserById($id);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($passwordHasher->isPasswordValid($user, $form->get("previousPassword")->getData())) {
                $user->setPassword($userPasswordHasher->hashPassword(
                    $user, $form->get("plainPassword")->getData()
                ));
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('app_disconnect');
            } else {
                $form->addError(new FormError("Mot de passe incorrect"));
            }
        }
        return $this->render('user/resetPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/edit/resetPasswordLogin/{id}', name: 'user_reset_passwordLogin')]
    public function resetPasswordLogin(int $id,
                                       EntityManagerInterface $entityManager,
                                       UserPasswordHasherInterface $userPasswordHasher,
                                       Request $request,
                                       UserRepository $userRepository,
                                       UserPasswordHasherInterface $passwordHasher): Response
    {
        $idUserConnected = $userRepository->findUserByLogin($this->getUser()->getUserIdentifier())->getId();

        if($id != $idUserConnected){
            return $this->redirectToRoute('app_access_denied', ['statusCode' => 403]);
        }

        $form = $this->createForm(ChangePasswordAndLoginType::class);
        $form->handleRequest($request);
        $user = $userRepository->findUserById($id);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($passwordHasher->isPasswordValid($user, $form->get("previousPassword")->getData())) {
                $user->setPassword($userPasswordHasher->hashPassword(
                    $user, $form->get("plainPassword")->getData()
                ));
                $user->setLogin($form->get("login")->getData());
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('app_disconnect');
            } else {
                $form->addError(new FormError("Mot de passe incorrect"));
            }
        }
        return $this->render('user/resetPasswordLogin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/edit/{id}', name: 'user_edit')]
    public function editUser(int $id,
                             Request $request,
                             SluggerInterface $slugger,
                             EntityManagerInterface $entityManager,
                             UserRepository $userRepository): Response
    {
        $user = $userRepository->findUserById($id);
        $userLogged = $this->getUser();
        $form = $this->createForm(EditUserFormType::class, $user, ['admin' => $userLogged->isAdministrateur()]);
        $form->handleRequest($request);

        if ($form->get("modifierMdp")->isClicked()) {
            if ($userLogged->isAdministrateur()) {
                return $this->redirectToRoute('app_forgot_password_request');
            }
            return $this->redirectToRoute('user_reset_password', ['id' => $id]);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                $projectDir = $this->getParameter('image_directory') . '/' . $user->getImageFilename();
                $fileSystem = new Filesystem();
                if ($fileSystem->exists($projectDir) && $user->getImageFilename()) {
                    $fileSystem->remove($projectDir);
                } else {
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
            'user' => $user
        ]);
    }
}
