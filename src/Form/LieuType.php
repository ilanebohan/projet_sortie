<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ville', EntityType::class, [
                'label' => 'Ville',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'query_builder' => function (VilleRepository $villeRepository) {
                    return $villeRepository->createQueryBuilder("v")->addOrderBy('v.nom');
                },
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('nom')
            ->add('rue')
            ->add('latitude')
            ->add('longitude');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
