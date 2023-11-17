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
use Monolog\Handler\Curl\Util;
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
    #[Route('/user/detailsOrganisateur/{id}', name: 'user_details_organisateur', requirements: ['id' => '\d+'])]
    public function detailsOrganisateur(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->findUserById($id);
        return $this->render('user/detailsOrganisateur.html.twig', [
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
                                      EntityManagerInterface $entityManager): Response
    {
        $reponse = new Response();
        $user = $userRepository->findUserById($id);
        try {
            $entityManager->remove($user);
            $entityManager->flush();
            $messageRetour = 'Utilisateur ' . $user->getLogin() . ' supprimé';
            $reponse->setStatusCode(200);
        } catch (ForeignKeyConstraintViolationException $e) {
            $messageRetour = 'L\'utilisateur ' . $user->getLogin() . ' ne peut pas être supprimé';
            $reponse->setStatusCode(500);
        }
        $reponse->setContent($messageRetour);
        return $reponse;
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
            $error = false;

            $extension = $file->getClientOriginalExtension();

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

            if($extension == "xml"){
                $xml = simplexml_load_file($projectDir, 'SimpleXMLElement');

                foreach ($xml->utilisateur as $utilisateurXml) {

                    $utilReader = new User();

                    $utilReader->setNom($utilisateurXml->nom);
                    $utilReader->setPrenom($utilisateurXml->prenom);

                    if(preg_match("/^0[1-9]\d{8}$/", $utilisateurXml->telephone)){
                        $utilReader->setTelephone($utilisateurXml->telephone);
                    }
                    else{
                        $form->addError(new FormError("Telephone incorrect pour l'utilisateur ".$utilisateurXml->nom." ".$utilisateurXml->prenom));
                        $error = true;
                        continue;
                    }

                    if($userRepository->findUserByMail($utilisateurXml->email)){
                        $form->addError(new FormError("Email déjà utilisé pour l'utilisateur ".$utilisateurXml->nom." ".$utilisateurXml->prenom));
                        $error = true;
                        continue;
                    }

                    if(preg_match("/^[\w.-]+@([\w-]+\.)+[\w-]{2,4}$/", $utilisateurXml->email) && !$userRepository->findUserByMail($utilisateurXml->email)){
                        $utilReader->setEmail($utilisateurXml->email);
                    }
                    else{
                        $form->addError(new FormError("Email incorrect pour l'utilisateur ".$utilisateurXml->nom." ".$utilisateurXml->prenom));
                        $error = true;
                        continue;
                    }

                    $utilReader->setAdministrateur($utilisateurXml->administrateur == 1);
                    $utilReader->setActif($utilisateurXml->actif == 1);

                    $site = $siteRepository->findSiteByNom($utilisateurXml->site);

                    if ($site) {
                        $utilReader->setSite($site[0]);
                    } else {
                        $form->addError(new FormError("Site inexistant pour l'utilisateur ".$utilisateurXml->nom." ".$utilisateurXml->prenom));
                        $error = true;
                        continue;
                    }

                    $seed = str_split('abcdefghijklmnopqrstuvwxyz'
                        . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                        . '0123456789!@#$%^&*()');
                    shuffle($seed);
                    $login = "";
                    foreach (array_rand($seed, 10) as $k) {
                        $login .= $seed[$k];
                    }
                    $utilReader->setLogin($login);
                    $utilReader->setPassword($userPasswordHasher->hashPassword(
                        $utilReader, 'password'
                    ));
                    $utilReader->setAllowImageDiffusion(false);

                    $entityManager->persist($utilReader);
                    $entityManager->flush();
                }
            }

            if($extension == "csv"){
                $reader = Reader::createFromPath($projectDir, 'r');

                for ($i = 0; $i <= $reader->count() - 1; $i++) {
                    $row = $reader->fetchOne($i);
                    $util = explode(",", str_replace("\"", "", $row[0]));

                    $utilReader = new User();
                    $utilReader->setNom($util[0]);
                    $utilReader->setPrenom($util[1]);
                    if(preg_match("/^0[1-9]\d{8}$/", $util[2])){
                        $utilReader->setTelephone($util[2]);
                    }
                    else{
                        $form->addError(new FormError("Telephone incorrect pour l'utilisateur ".$util[0]." ".$util[1]));
                        $error = true;
                        continue;
                    }

                    if($userRepository->findUserByMail($util[3])){
                        $form->addError(new FormError("Email déjà utilisé pour l'utilisateur ".$util[0]." ".$util[1]));
                        $error = true;
                        continue;
                    }

                    if(preg_match("/^[\w.-]+@([\w-]+\.)+[\w-]{2,4}$/", $util[3]) && !$userRepository->findUserByMail($util[3])){
                        $utilReader->setEmail($util[3]);
                    }
                    else{
                        $form->addError(new FormError("Email incorrect pour l'utilisateur ".$util[0]." ".$util[1]));
                        $error = true;
                        continue;
                    }

                    if ($util[4] == 1) {
                        $utilReader->setAdministrateur(true);
                    } else {
                        $utilReader->setAdministrateur(false);
                    }

                    if ($util[5] == 1) {
                        $utilReader->setActif(true);
                    } else {
                        $utilReader->setActif(false);
                    }

                    $site = $siteRepository->findSiteByNom($util[6]);
                    if ($site) {
                        $utilReader->setSite($site[0]);
                    } else {
                        $form->addError(new FormError("Site inexistant pour l'utilisateur ".$util[0]." ".$util[1]));
                        $error = true;
                        continue;
                    }

                    $seed = str_split('abcdefghijklmnopqrstuvwxyz'
                        . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                        . '0123456789!@#$%^&*()');
                    shuffle($seed);
                    $login = "";
                    foreach (array_rand($seed, 10) as $k) {
                        $login .= $seed[$k];
                    }
                    $utilReader->setLogin($login);
                    $utilReader->setPassword($userPasswordHasher->hashPassword(
                        $utilReader, 'password'
                    ));
                    $utilReader->setAllowImageDiffusion(false);

                    $entityManager->persist($utilReader);
                    $entityManager->flush();
                }

            }

            $fileSystem = new Filesystem();
            if ($fileSystem->exists($projectDir)) {
                $fileSystem->remove($projectDir);
            }

            if(!$error){
                return $this->redirectToRoute('user_list');
            }

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
                $form->addError(new FormError("Données incorrectes"));
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
            if ($passwordHasher->isPasswordValid($user, $form->get("previousPassword")->getData()) && !$userRepository->findUserByLogin($form->get("login")->getData())  ) {
                $user->setPassword($userPasswordHasher->hashPassword(
                    $user, $form->get("plainPassword")->getData()
                ));
                $user->setLogin($form->get("login")->getData());
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('app_disconnect');
            } else {
                if($userRepository->findUserByLogin($form->get("login")->getData()))
                    $form->addError(new FormError("Login déjà utilisé"));
                else
                    $form->addError(new FormError("Données incorrectes"));
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
