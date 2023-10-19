<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

;

class CategoriesFixtures extends Fixture
{
    private $slugger;
    private $counter = 1;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }


    public function load(ObjectManager $manager): void
    {
        //Catéfories parentes

        $parent = $this->createCategories('Informatique', null, $manager);
         //Les enfants
        $this->createCategories('Ordinateurs portables', $parent, $manager);
        $this->createCategories('Ecrans', $parent, $manager);

        //Re parent
        $parent = $this->createCategories('Livres', null, $manager);
        //Enfants
        $this->createCategories('Bande déssinées', $parent, $manager);
        $this->createCategories('Romans', $parent, $manager);

        $manager->flush();
    }

    public function createCategories(string $name, Categories $parent = null, ObjectManager $manager){
            //Ordinateur est enfant de Informatique
            $categorie = new Categories();
            $categorie->setName($name);
            $categorie->setSlug($this->slugger->slug($categorie->getName())->lower());
            $categorie->setParent($parent);
            $manager->persist($categorie);
            //Reference = mise en memoire 1 elements a passer a ProductsFixtures
            $this->addReference('cat-'.$this->counter, $categorie);
            $this->counter++;

            return $categorie;
    }
}
