<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\CategoriesType;
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController{

    #[Route('/admin', name:'app_admin')]
    public function administration(CategoriesRepository $categoriesRepository, ProductsRepository $productsRepository):Response{
        

        return $this->render('administration/admin/nav_administration.html.twig',[
            'categories' => $categoriesRepository->findAll(),
            'products' => $productsRepository->findAll()
        ]);
    }

    #[Route('/admin/categories', name:'app_admin_categorie')]
    public function listeCatégories(CategoriesRepository $categoriesRepository) : Response {
        
        $categories = $categoriesRepository->findBy([], ['categorieOrder' => 'ASC']);
        return $this->render('administration/admin/categories_admin.html.twig',[
            'categories' => $categories
        ]);
    }

    #[Route('/admin/ajouter-categorie', name:'app_admin_ajouter_categorie')]
    public function ajouterCategorie(Request $request, EntityManagerInterface $em) : Response {
        
        $categorie = new Categories();
        $form = $this->createForm(CategoriesType::class, $categorie);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            //dd($categorie);
            if($categorie){
                $em->persist($categorie);
                $em->flush();

                $this->addFlash('success', 'La catégorie a bien été ajoutée !');
                return $this->redirectToRoute('app_admin_categorie');
            }else{
                $this->addFlash('danger', 'Erreur lors de l\'ajout de la catégorie !');
                return $this->redirectToRoute('app_admin_ajouter_categorie');
            }
            
        }

        return $this->render('administration/admin/ajouter_categorie.html.twig',[
            'form' => $form
        ]);
    }

    #[Route('/admin/produits', name:'app_administration_produits')]
    public function listeProduits(ProductsRepository $productsRepository) : Response {
        
       
        return $this->render('administration/admin/produits_admin.html.twig',[
            'products' => $productsRepository->findAll()
        ]);
    }
}