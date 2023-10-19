<?php

namespace App\Form;

use App\Entity\Products;
use Faker\Provider\Image;
use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Image as ConstraintsImage;

class ProductsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'label' => 'Nom du produit'
            ])
            ->add('description', TextareaType::class,[
                'label' => 'Description du produit'
            ])
            ->add('price', MoneyType::class,[
                'label' => 'Prix du produit',
                'constraints' =>[
                    new Positive(message: "Le prix doit etre positif !")
                ]
            ])
            ->add('stock', NumberType::class,[
                'label' => 'Quantitée(s) en stock',
                'constraints' =>[
                    new Positive(message: "Le quantitée doit etre positive !")
                ]
            ])
            ->add('categories', EntityType::class,[
                'label' => 'Catégorie du produit',
                'class' => Categories::class,
                'choice_label' => 'name',
                //Separer les parents des enfants
                'group_by' => 'parent.name',
                //Afficher que les enfants
                'query_builder' => function(CategoriesRepository $categoriesRepository){
                    return $categoriesRepository->createQueryBuilder('c')
                    //Que les catégories dont les parents ne sont pas null
                    ->where('c.parent IS NOT NULL')
                    //Trier par ordre croissant
                    ->orderBy('c.name', 'ASC');
                }
            ])

            ->add('images', FileType::class,[
                'label' => false,
                'multiple' => true,
                //Pas d'equvalent dans entité Products
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new All(
                        new ConstraintsImage([
                            'maxWidth' => 1280,
                            'maxWidthMessage' => 'L\'image doit faire {{ max_width }} pixels de large au maximum'
                        ])
                    )
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Products::class,
        ]);
    }
}
