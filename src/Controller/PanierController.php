<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PanierController extends AbstractController{

    #[Route('/ajouter-panier/{id}', name:'app_panier')]
    public function ajouterAuPanier(Products $products, SessionInterface $sessionInterface):Response{
        
        //Id du produit
        $id = $products->getId();
        //Recup du panier existant
        //Si pas de panier => on recup un tableau vide
        $panier = $sessionInterface->get('panier', []);

        //On ajoute le produit au panier si il n'y est pas => Sinon on increment
        if(empty($panier[$id])){
            $panier[$id] = 1;
        }else{
            $panier[$id]++;
        }
        
        //Ajout du panier a la session
        $sessionInterface->set('panier', $panier);
        //On redirige vers la page du panier
        return $this->redirectToRoute('app_afficher_panier');
    }

    #[Route('/afficher-panier/', name:'app_afficher_panier')]
    public function afficherPanier(SessionInterface $sessionInterface, ProductsRepository $productsRepository):Response{

        $panier = $sessionInterface->get('panier', []);

        //initialisé des varaiables
        $data = [];
        $total = 0;

        foreach($panier as $id => $quantite){
            $product = $productsRepository->find($id);
            $data[] = [
                'product' => $product,
                'quantity' => $quantite
            ];
            $total += $product->getPrice() * $quantite;  
        }

        return $this->render('panier/afficher_panier.html.twig',[
            'panier' => $data,
            'total' => $total
        ]);
    }

    //Ajouter supprimer des quantités
    #[Route('/ajouter-quantite-panier/{id}', name:'app_add_quantity_panier')]
    public function ajouterQuantitePanier(Products $products, SessionInterface $sessionInterface):Response{
        
        //Id du produit
        $id = $products->getId();
        //Recup du panier existant
        //Si pas de panier => on recup un tableau vide
        $panier = $sessionInterface->get('panier', []);

        //On ajoute le produit au panier si il n'y est pas => Sinon on increment
        if(empty($panier[$id])){
            $panier[$id] = 1;
        }else{
            $panier[$id]++;
        }
        
        //Ajout du panier a la session
        $sessionInterface->set('panier', $panier);
        //On redirige vers la page du panier
        return $this->redirectToRoute('app_afficher_panier');
    }


      //Ajouter supprimer des quantités
      #[Route('/supprimer-quantite-panier/{id}', name:'app_remove_quantity_panier')]
      public function supprimerQuantitePanier(Products $products, SessionInterface $sessionInterface):Response{
          
          //Id du produit
          $id = $products->getId();
          //Recup du panier existant
          //Si pas de panier => on recup un tableau vide
          $panier = $sessionInterface->get('panier', []);
  
          //On retire le produit du panier si il y a 1 seul exemplaire => Sinon on decremente
          if(!empty($panier[$id])){
            //Si la quantité > 1
                if($panier[$id] > 1){
                    $panier[$id]--;
                }else{
                    //defaire une variable
                    unset($panier[$id]);
                }
          }
          
          //Ajout du panier a la session
          $sessionInterface->set('panier', $panier);
          //On redirige vers la page du panier
          return $this->redirectToRoute('app_afficher_panier');
      }


      //Supprimer un produit du panier
      #[Route('/supprimer-produit-panier/{id}', name:'app_remove_product_panier')]
      public function supprimerProduitPanier(Products $products, SessionInterface $sessionInterface):Response{
          
          //Id du produit
          $id = $products->getId();
          //Recup du panier existant
          //Si pas de panier => on recup un tableau vide
          $panier = $sessionInterface->get('panier', []);
  
          //Si le tableau du panier n'est pas vide => on defait la variable panier
          if(!empty($panier[$id])){
                unset($panier[$id]);
          }
          
          //Ajout du panier a la session
          $sessionInterface->set('panier', $panier);
          //On redirige vers la page du panier
          return $this->redirectToRoute('app_afficher_panier');
      }

      //Vider le panier
      #[Route('/vider-panier/', name:'app_vider_panier')]
      public function viderPanier(SessionInterface $sessionInterface):Response{
            $sessionInterface->remove('panier');
            return $this->redirectToRoute('app_afficher_panier');
      }

}