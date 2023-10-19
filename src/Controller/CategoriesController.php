<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class CategoriesController extends AbstractController{


    #[Route('/categories/{slug}', name:'app_categories')]
    public function listeCategorie(Request $request, Categories $categories, ProductsRepository $productsRepository) : Response {

        //La page courante dans url = si iltouve pas => par default la page est 1
        $page_courante = $request->query->getInt('page', 1);
        //Produits par categorie = page courante + slug + limite
        $products = $productsRepository->findProductsPaginated($page_courante, $categories->getSlug(), 2);

        return $this->render('categories/liste_categories.html.twig',[
            'categorie' => $categories,
            'products' => $products
        ]);
    }
}