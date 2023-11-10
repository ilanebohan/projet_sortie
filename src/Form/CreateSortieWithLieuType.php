<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Sortie;
use App\Repository\SiteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;

class CreateSortieWithLieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom de la sortie',
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('dateDebut', DateTimeType ::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                'constraints' => [
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'Vous devez mettre une date de Debut supérieure à la date et l\'heure d\'aujourd\'hui'
                    ])
                ]
            ])
            ->add('dateCloture', DateTimeType ::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
                'constraints' => [
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'Vous devez mettre une date de cloture supérieure à la date et l\'heure d\'aujourd\'hui'
                    ]),
                    new LessThan([
                        'propertyPath' => 'parent.all[dateDebut].data',
                        'message' => 'Vous devez mettre une date de cloture inférieure à la date de début'
                    ])
                ]
            ])
            ->add('nbInscriptionsMax', NumberType::class, [
                'label' => 'Nombres de places',
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée',
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('descriptionInfos', TextareaType::class, [
                'label' => 'Description et infos',
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('siteOrganisateur', EntityType::class, [
                'label' => 'Ville organisatrice',
                'class' => Site::class,
                'choice_label' => 'nom',
                'query_builder' => function (SiteRepository $siteRepository) {
                    return $siteRepository->createQueryBuilder("s")->addOrderBy('s.nom');
                },
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('lieu', LieuType::class)
            ->add('creer', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
            ->add('estPrivee', CheckboxType::class, [
                'label' => 'Sortie privée ',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
