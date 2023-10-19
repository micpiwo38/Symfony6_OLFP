<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * Page d'accueil
     *
     * @return Response
     */
    //Ceci est une insatnce de Route->UrlMatcher
    //Injection de dependance = ControllerResolver (instance de la classe) + ArgumentResolver(appel de la bonne methode)
    //Ex: MainController = ControllerResolver = $controller
    //Et methode Index() = ArgumentResolver = $argument
    //Pour la reponse
    ////On appel donc dans un tableau le controllerResover + argumentResolver
    //$response = call_user_func_array($controller, $arguments);
    #[Route('/', name: 'app_main')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {

        return $this->render('main/index.html.twig', [
            'categories' => $categoriesRepository->findBy([], ['categorieOrder' => 'ASC']),
        ]);
    }
}
