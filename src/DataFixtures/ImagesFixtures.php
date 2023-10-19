<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Images;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ImagesFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        for($img = 1; $img <= 100; $img++){
            $image = new Images();
            $image->setName($faker->image(null, 640, 480));
            $product = $this->getReference('prod-'.rand(1, 10));
            $image->setProducts($product);
            $manager->persist($image);
        }

        $manager->flush();   
    }

    //la fonction load charge les fixtures dans l'ordre Alphabetique
    //ImageFixture est donc charger avant ProductsFixture
    //Or Image a besoin de la ref du produits
    //
    //On implemente DependentFixtureInterface => qui impose getDependencies()

    //ProductsFixtures est executée avant image
    public function getDependencies():array
    {
        return [
            ProductsFixtures::class
        ];
    }
}
