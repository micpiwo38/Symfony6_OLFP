<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\NewPasswordType;
use App\Form\ResetPasswordType;
use App\Repository\UsersRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/mot-passe-oublie', name: 'app_forgoten-password')]
    public function motPasseOublie(
        Request $request, 
        UsersRepository $usersRepository,
        TokenGeneratorInterface $tokenGeneratorInterface,
        EntityManagerInterface $em,
        SendMailService $sendMailService
        ) : Response {

        $form = $this->createForm(ResetPasswordType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //Recherche email existant
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());
            
            if($user){
                //1 - On genere un nouveau token
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
                //dd($user);
                //Persist en bdd
                $em->persist($user);
                $em->flush();

                //2 - un lien de reinitialsation du mot de passe
                //Abstract controller genere une nouvelle url
                $url = $this->generateUrl('app_reset-password',[
                    'token' => $token
                ], UrlGeneratorInterface::ABSOLUTE_URL);

                //3 - les données du mail depuis notre services
                $context = [
                    'url' => $url,
                    'user' => $user
                ];

                //from + to + message + template + contexte
                $sendMailService->send(
                    'admin@emcommerce.com',
                    $user->getEmail(),
                    'Réinitialisation de votre mot de passe',
                    'password_reset',
                    $context
                );

                $this->addFlash('success', 'Email de reinitialisation de mot de passe a bien été envoyé, merci de verifié votre boite email !');
                //la redirection
                return $this->redirectToRoute('app_login');
            }else{
                $this->addFlash('danger', 'Une erreur est survenue, merci de recommencer !');
                return $this->redirectToRoute('app_login');
            }
           
        }

        return $this->render('security/reset_password_request.html.twig',[
            'reset_form' => $form->createView()
        ]);
    }

    //Apres clic liens de reinit
    #[Route(path: '/mot-passe-oublie/{token}', name: 'app_reset-password')]
    public function resetPassword(
        string $token,
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManagerInterface,
        UserPasswordHasherInterface $userPasswordHasherInterface
        ) : Response {

            //1 - verifié si le token est en BDD
            $user = $usersRepository->findOneByResetToken($token);
            if($user){
                //Si le jeton est valide
                //Un formulaire pour reset le mot de passe
                $form = $this->createForm(NewPasswordType::class);
                $form->handleRequest($request);

                if($form->isSubmitted() && $form->isValid()){
                    //On supprime le token
                    $user->setResetToken('');
                    
                    //Hasher le nouveau mot de passe
                    $user->setPassword(
                        $userPasswordHasherInterface->hashPassword(
                            $user,
                            $form->get('password')->getData()
                            )
                        );
                        //Persistance et execution
                        $entityManagerInterface->persist($user);
                        $entityManagerInterface->flush();

                        $this->addFlash('success', 'Le mot de passe a été modifié avec succès !');
                        return $this->redirectToRoute('app_login');
                }

                return $this->render('security/new_password.html.twig',[
                    'password_form' => $form->createView()
                ]);
            }else{  
                $this->addFlash('danger', 'Le jeton est invalide !');
                return $this->redirectToRoute('app_login');
            }
        
    }
}
