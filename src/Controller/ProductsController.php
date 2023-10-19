<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\Products;
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductsController extends AbstractController{

    #[Route('/produits', name:'app_products')]
    public function afficherProduits(ProductsRepository $productsRepository) : Response {
        
        return $this->render('produits/afficher_produits.html.twig',[
            'products' => $productsRepository->findAll(),
        ]);

    }

    //On interpole la variable slug dans URL
    #[Route('/details-produit/{slug}', name:'app_product_details')]
    //Injection de l'entity Products
    public function detailsProduit(Products $products): Response{

        //dd($products);
        return $this->render('produits/detail_produit.html.twig',[
            'details_produit' => $products
        ]);
    }

}