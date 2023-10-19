<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Repository\OrdersDetailsRepository;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrdersController extends AbstractController{

    #[Route('/ajouter-commandes', name:'app_add_order')]
    
    public function ajouterUneCommande(
        SessionInterface $sessionIdInterface, 
        ProductsRepository $productsRepository,
        EntityManagerInterface $em
        ):Response{
        //impose une connexion USER
        $this->denyAccessUnlessGranted("ROLE_USER");
        //Recup du panier de la page précedente grace a la session
        $panier = $sessionIdInterface->get('panier', []);
        //dd($panier);

        //Si le tableau panier est vide
        if($panier === []){
            $this->addFlash('warning', 'Votre panier est vide');
            return $this->redirectToRoute('app_main');
        }

        //Le panier n'est pas vide => on creer la commande
        //Instance de entité orders
        $order = new Orders();


  
             //On rempli la commande
        //User concerner
        $order->setUsers($this->getUser());
        //Référence random
        $order->setReference(uniqid());

        //parcours du panier pour les details de la commande
        foreach($panier as $item => $quantite){
            //Instance de la classe orderDetails quantité + prix + la commande + le produit
            $orderDetails = new OrdersDetails();
            //Le produits
            $product = $productsRepository->find($item);
            //dd($product);
            //le prix
            $prix = $product->getPrice();
            //Remplir la table order details
            $orderDetails->setProducts($product);
            $orderDetails->setPrice($prix);
            $orderDetails->setQuantity($quantite);

            //ajout des details a la commande principale
            $order->addOrdersDetail($orderDetails);
        }

        //HORS DE LA BOUCLE
        $em->persist($order);
        $em->flush();

        //Vider le panier
        $sessionIdInterface->remove('panier');

        $this->addFlash('success', 'Votre commande a bien été validée !');
        return $this->redirectToRoute('app_main');
        
    }

    #[Route('/resume-commandes', name:'app_resume_order')]
    public function resumeCommande(OrdersRepository $order, OrdersDetailsRepository $ordersDetailsRepository) : Response {
        
        return $this->render('commandes/resume_commande.html.twig',[
            'order' => $order->findAll(),
            'order_details' => $ordersDetailsRepository->findAll()
        ]);
    }


}