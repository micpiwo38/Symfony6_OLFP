<?php

namespace App\Form;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class CategoriesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                'label' => 'Nom de la catégorie'
            ])
            ->add('categorieOrder', NumberType::class,[
                'label' => 'Ordre de la catégorie'
            ])
            ->add('slug', TextType::class,[
                'label' => 'Slug de la catégorie'
            ])
            ->add('parent', EntityType::class,[
                'label' => 'Catégorie du produit',
                'class' => Categories::class,
                'choice_label' => 'name',
                //Separer les parents des enfants
                'group_by' => 'parent.name',
                //Afficher que les enfants
                'query_builder' => function(CategoriesRepository $categoriesRepository){
                    return $categoriesRepository->createQueryBuilder('c')
                    //Que les catégories dont les parents ne sont pas null
                    ->where('c.parent IS NULL')
                    //Trier par ordre croissant
                    ->orderBy('c.name', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categories::class,
        ]);
    }
}
