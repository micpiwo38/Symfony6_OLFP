<?php

namespace App\DataFixtures;

use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

;

class UsersFixtures extends Fixture
{
    private $passwordEncoder;
    private $slugger;

    public function __construct(UserPasswordHasherInterface $passwordEncoder, SluggerInterface $slugger)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = $slugger;
    }


    public function load(ObjectManager $manager): void
    {

            //Un admin
        $admin = new Users();
        $admin->setEmail('admin@admin.com');
        $admin->setLastname('ADMIN');
        $admin->setFirstname('Admin');
        $admin->setAddress('15 rue des admins');
        $admin->setZipcode("38000");
        $admin->setCity("Grenoble");
        $admin->setPassword($this->passwordEncoder->hashPassword($admin, 'azerty'));
 
        $manager->persist($admin);

        //Les utilisateurs
        $faker = Faker\Factory::create('fr_FR');
        for($i = 0; $i < 10; $i++){
            $user = new Users();
            $user->setEmail($faker->email);
            $user->setLastname($faker->lastName);
            $user->setFirstname($faker->firstName);
            $user->setAddress($faker->streetAddress);
            //Supprimer les espaces
            $user->setZipcode(str_replace(' ','', $faker->postcode));
            $user->setCity($faker->city);
            $user->setPassword($this->passwordEncoder->hashPassword($admin, 'secret'));
       
        $manager->persist($user);

        }

        $manager->flush();
    }
}
