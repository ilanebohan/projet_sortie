<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Form\EditPasswordType;
use App\Form\PasswordResetType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Ulid;

class MailController extends AbstractController
{

    #[Route('/mail', name: 'app_send_mail')]
    public function index(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager) : Response
    {
        $form = $this->createForm(PasswordResetType::class, [
            'action' => $this->generateUrl('app_send_mail'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $entityManager->getRepository(User::class)->findUserByMail($email);

            if ($user) {

                $TokenRepository = $entityManager->getRepository(PasswordResetToken::class);

                $tokenExistant = $TokenRepository->findOneBy(['User' => $user]);

                if($tokenExistant) {
                    $entityManager->remove($tokenExistant);
                    $entityManager->flush();
                }

                $newToken = new PasswordResetToken($user);
                $entityManager->persist($newToken);
                $entityManager->flush();

                $email = (new Email())
                    ->from('theo.blanchard2022@campus-eni.fr')
                    ->to($user->getEmail())
                    ->subject('Réinitialisation du mot de passe')
                    ->html(
                        $this->renderView('mail/password_reset.html.twig', [
                            'token' => $newToken,
                        ])
                    );

                $mailer->send($email);

                $this->addFlash('success', 'Un e-mail de réinitialisation a été envoyé.');

                return $this->render('security/login.html.twig', [
                    'last_username' => '',
                ]);

            } else {
                $this->addFlash('danger', 'Aucun utilisateur trouvé avec cet e-mail.');
            }
        }

        return $this->render('mail/index.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/mail/Resetpassword', name: 'app_reset_password')]
    public function reset(Request $request, $token, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher) : Response
    {
        $tokenEntity = $entityManager->getRepository(PasswordResetToken::class)->findOneBy(['token' => $token]);

        // Vérifier si le jeton a été trouvé et s'il est encore valide
        if (!$tokenEntity || $tokenEntity->isExpired()) {
            throw $this->createNotFoundException('Jeton de réinitialisation invalide.');
        }

        // Créer le formulaire pour la réinitialisation du mot de passe
        $form = $this->createForm(EditPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $tokenEntity->getUser();

            $newPassword = $form->get('newPassword')->getData();

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $newPassword
                )
            );

            $entityManager->remove($tokenEntity);
            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('password_reset/reset.html.twig', [
            'token' => $token,
            'form' => $form->createView(),
        ]);
    }
}
