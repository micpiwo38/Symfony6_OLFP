<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Repository\ProductsRepository;
use App\Service\ImagesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductsAdminController extends AbstractController{

    #[Route('/admin/produits', name: 'app_admin_produits')]
    public function gestionProduits(ProductsRepository $productsRepository):Response{
        return $this->render('administration/produits/gestion_produits.html.twig',[
            'products' => $productsRepository->findAll()
        ]);
    }

    #[Route('/admin/ajouter-produit', name: 'app_admin_ajouter_produits')]
    public function ajouterProduits(
        Request $request, 
        EntityManagerInterface $em, 
        SluggerInterface $slugger,
        ImagesService $imagesService
        ):Response{
        //Instance de entité produit
        $products = new Products();
        //Creer le formulaire
        $form = $this->createForm(ProductsFormType::class, $products);
        //Analyse des attributs name via la super globale $_POST['attribut name']
        $form->handleRequest($request);

        //Si form soumis + valide
        if($form->isSubmitted() && $form->isValid()){
            //Recuperer le tableau d'image via ImagesService
            $images = $form->get('images')->getData();
            //Test
            //dd($images);
            //Boucle sur le tableau d'image
            foreach($images as $image){
                //Le dossier de destination
                $folder = 'products';
                //Appel du service d'ajout + la methode addPhoto(le fichier, le dossier + largeur + hauteur)
                $fichier = $imagesService->addPhotos($image, $folder, 300,300);
                //Instance de entité image
                $img = new Images();
                //Setter du nom
                $img->setName($fichier);
                //Entité products possède une methode addImages()
                $products->addImage($img);

            }

            //Récuperer le slug = au champ name + lowercase
            $slug = $slugger->slug($products->getName());
            //dd($slug);
            //Ajout du slug au produit
            $products->setSlug($slug);

            $em->persist($products);
            $em->flush();

            $this->addFlash('success', 'Le produits a bien été ajouté !');
            return $this->redirectToRoute('app_admin_produits');
        }

        return $this->render('administration/produits/ajouter_produit.html.twig',[
            'product_form' => $form->createView()
        ]);
    }

    #[Route('/admin/editer-produit/{slug}', name: 'app_admin_editer_produits')]
   
    public function editerProduits(
        Products $products, 
        Request $request, 
        EntityManagerInterface $em, 
        SluggerInterface $slugger,
        ImagesService $imagesService
        
        ):Response{

        //Interdire ne fonction des roles
        //Methode avec les voters
        //Le voter demande a minima le ROLE_ADMIN
        //$this->denyAccessUnlessGranted('PRODUCT_EDIT', $products);

         //Creer le formulaire
         $form = $this->createForm(ProductsFormType::class, $products);
         //Analyse des attributs name via la super globale $_POST['attribut name']
         $form->handleRequest($request);
 
         //Si form soumis + valide
         if($form->isSubmitted() && $form->isValid()){

               //Recuperer le tableau d'image via ImagesService
               $images = $form->get('images')->getData();
               //Test
               //dd($images);
               //Boucle sur le tableau d'image
               foreach($images as $image){
                   //Le dossier de destination
                   $folder = 'products';
                   //Appel du service d'ajout + la methode addPhoto(le fichier, le dossier + largeur + hauteur)
                   $fichier = $imagesService->addPhotos($image, $folder, 300,300);
                   //Instance de entité image
                   $img = new Images();
                   //Setter du nom
                   $img->setName($fichier);
                   //Entité products possède une methode addImages()
                   $products->addImage($img);
   
               }

             //Récuperer le slug = au champ name + lowercase
             $slug = $slugger->slug($products->getName());
             //dd($slug);
             //Ajout du slug au produit
             $products->setSlug($slug);
 
             $em->persist($products);
             $em->flush();
 
             $this->addFlash('success', 'Le produits a bien été mis à jour !');
             return $this->redirectToRoute('app_admin_produits');
         }

        return $this->render('administration/produits/editer_produits.html.twig',[
            'product_form' => $form->createView(),
            'product' => $products 
        ]);
    }

    #[Route('/admin/supprimer-produit/{id}', name: 'app_admin_supprimer_produits')]

    public function supprimerProduits(ProductsRepository $productsRepository, $id, EntityManagerInterface $em):Response{

        //Interdire ne fonction des roles
        //Methode avec les voters
         //Le voter demande a minima le ROLE_PRODUCTS_ADMIN
         //Soit avec la directive = #[IsGranted('PRODUCT_DELETE', 'products')] ou
        //$this->denyAccessUnlessGranted('PRODUCT_DELETE', $products);

        $produit = $productsRepository->find($id);
        if($produit){
            $em->remove($produit);
            $em->flush();

            $this->addFlash('success', 'Le produit a bien été supprimer');
            return $this->redirectToRoute('app_administration_produits');
        }


        return $this->render('administration/produits/gestion_produits.html.twig',[

        ]);
    }


    //Supprimer une image
    #[Route('/admin/supprimer-image/{id}', name: 'app_admin_supprimer_image', methods: ['DELETE'])]
    public function supprimerImage(Images $images, Request $request, EntityManagerInterface $em, ImagesService $imagesService) : JsonResponse {
        //En AJAX
        //Recup du contenu de la requète = true = tableau associatif
        $data = json_decode($request->getContent(), true);
        //Le jeton est il valid
        if($this->isCsrfTokenValid('delete' . $images->getId(), $data['_token'])){
            //recup du nom de l'image
            $nom = $images->getName();
            //Appel de la methode deletePhoto de ImageService
            if($imagesService->deletePhoto($nom, 'products', 300, 300)){
                $em->remove($images);
                $em->flush();
                $this->addFlash('success', "La photo a bien été supprimée !");
                return new JsonResponse(['success' => "La photo a bien été supprimée !"],200);
            }
            $this->addFlash('danger', "La suppression de la photo a échoué !");
            return new JsonResponse(['success' => "Erreur de supression de la photo !"],200);
        }

        return new JsonResponse(['error' => 'Le jeton est invalide'], 400);
    }
}