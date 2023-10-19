<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Service\JwtService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager,
        SendMailService $sendMailService,
        JwtService $jwtService
        ): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            //Generer un jwt depuis le service
            //1 - creer le header = type + algo
            $header = [
                'type' => 'JWT',
                'alg' => 'HS256'
            ];

            //Creer le payload
            $payload = [
                'user_id' => $user->getId()
            ];

            //generer le token = Appel de la methode du service JwtService->generate
            //function generate(array $header, array $payload, string $secret, int $validity = 10800): string
            //Le secret = la cle de services.yml -> parameters = app.jwtsecret: '%env(JWT_SECRET)%'
            $token = $jwtService->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // Appel du Service email
            $sendMailService->send(
                'administartion@ecommerce.com',
                $user->getEmail(),
                'Activation de votre compte sur le site E-commerce',
                'register',
                [
                    'user' => $user,
                    'token' => $token
                ]
                );
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route('/check/{token}', name:'app_verify_user')]
    public function verifyUser($token, JwtService $jwt, UsersRepository $usersRepository, EntityManagerInterface $em): Response{
        //Appel du service JWT + Methode isValid
        //dd($jwtService->isValid($token));
        //dd($jwtService->getPayload($token));
        //dd($jwtService->isExpired($token));
        //dd($jwtService->checkSignature($token, $this->getParameter('app.jwtsecret')));

        //check si le token est valide, n'a pas expiré et que la signature n'est pas corompue
        //On vérifie si le token est valide, n'a pas expiré et n'a pas été modifié
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->checkSignature($token, $this->getParameter('app.jwtsecret'))){
            // On récupère le payload
            $payload = $jwt->getPayload($token);

            // On récupère le user du token
            $user = $usersRepository->find($payload['user_id']);

            //On vérifie que l'utilisateur existe et n'a pas encore activé son compte
            if($user && !$user->getIsVerified()){
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'Votre compte a bien été activé');
                return $this->redirectToRoute('app_profile_index');
            }
        }
        // Ici un problème se pose dans le token
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }

    #[Route("/renoyer-validation", name:'app_resend_verif')]
    public function resendEmailValidation(JwtService $jwt, SendMailService $sendMailService, UsersRepository $usersRepository) : Response {
        //Recup de utilisateur courant
        $user = $this->getUser();
        //Si on est pas connecter
        if(!$user){
            $this->addFlash('danger', 'Merci de vous connectez pour acceder à cette page !');
            return $this->redirectToRoute('app_login');
        }

        //Si utilisateur  deja verifié son compte
        if($user->getIsVerified()){
            $this->addFlash('warning', 'Votre compte à deja été activé !');
            return $this->redirectToRoute('app_profile_index');
        }

        $header = [
            'type' => 'JWT',
            'alg' => 'HS256'
        ];

        //Creer le payload
        $payload = [
            'user_id' => $user->getId()
        ];

        //generer le token = Appel de la methode du service JwtService->generate
        //function generate(array $header, array $payload, string $secret, int $validity = 10800): string
        //Le secret = la cle de services.yml -> parameters = app.jwtsecret: '%env(JWT_SECRET)%'
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        // Appel du Service email
        $sendMailService->send(
            'administartion@ecommerce.com',
            $user->getEmail(),
            'Activation de votre compte sur le site E-commerce',
            'register',
            [
                'user' => $user,
                'token' => $token
            ]
            );

            $this->addFlash('success', 'Un email d\'activation a bien été envoyé  !');
            return $this->redirectToRoute('app_profile_index');
    }
    
}
