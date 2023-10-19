<?php


namespace App\Controller\Admin;

use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/utilisateurs', name: 'app_admin_users_')]
class UsersController extends AbstractController{

    #[Route('/', name: 'index')]
    public function afficherUtilisateur(UsersRepository $usersRepository): Response{

        return $this->render('administration/utilisateurs/afficher_utilisateur.html.twig',[
            'users' => $usersRepository->findAll()
        ]);
    }
}