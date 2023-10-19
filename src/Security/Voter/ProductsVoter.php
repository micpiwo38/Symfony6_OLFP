<?php

namespace App\Security\Voter;

use App\Entity\Products;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ProductsVoter extends Voter{

    //Constantes
    const EDIT = 'PRODUCT_EDIT';
    const DELETE = 'PRODUCT_DELETE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    //$attribute = valeur de la constante + le produit
    protected function supports(string $attribute, $products): bool{
        //Si dans le tableau on a pas la contante + EDIT ou DELETE
        if(!in_array($attribute, [self::EDIT, self::DELETE])){
            return false;
        }
        //Si le produits n'est pas une instance de entité Products
        if(!$products instanceof Products){
            return false;
        }

        //En une seule ligne
        //return in_array($attribute, [self::EDIT, self::DELETE]) && $products instanceof Products;

        return true;
    }

    //$attribute = valeur de la constante +  le produit + Token interface = utilisateur + ses droits
    protected function voteOnAttribute(string $attribute, $products, TokenInterface $token): bool{
        //Recup de utilisateur dans un jeton
        $user = $token->getUser();
        //Si pas connecter
        if(!$user instanceof UserInterface){
            return false;
        }

        //Utilisateur est ROLE_PRODUCTS_ADMIN ou ADMIN
        if($this->security->isGranted("ROLE_ADMIN")){
            return true;
        }

        //Si connecter mais pas admin
        switch($attribute){
            case self::EDIT:
                //permission d'editer
                return $this->canEdit();
                break;
            case self::DELETE:
                //permission de supprimer
                return $this->canDelete();
                break;
        }
    }

    //Pour editer le voter demande le ROLE_ADMIN
     //Dans ProductsAdminController => $this->denyAccessUnlessGranted('PRODUCT_EDIT', $products);
    private function canEdit(){
        return $this->security->isGranted("ROLE_ADMIN");
    }

     //Pour editer le voter demande le ROLE_PRODUCT_ADMIN
     //Dans ProductsAdminController => $this->denyAccessUnlessGranted('PRODUCT_DELETE', $products);
    private function canDelete(){
        return $this->security->isGranted("ROLE_PRODUCTS_ADMIN");
    }
}