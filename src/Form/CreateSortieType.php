<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\VilleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateSortieType extends AbstractType
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
            ->add('dateDebut', null, [
                'label' => 'Date et heure de la sortie',
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('dateCloture', null, [
                'label' => 'Date limite d\'inscription',
                'row_attr' => [
                    'class' => 'input-group mb-3'
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
            ->add('ville', EntityType::class, [
                'mapped' => false,
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
            ->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'])
            ->add('addLieu', SubmitType::class, [
                'validate' => false,
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
            ->add('lieu', EntityType::class, [
                'label' => 'Lieu',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'query_builder' => function (LieuRepository $lieuRepository) {
                    return $lieuRepository->createQueryBuilder("l")->addOrderBy('l.nom');
                },
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ]
            ])
            ->add('creer', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ]);

    }

    public function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $sortie = $event->getData();
        $ville = $form->get('ville')->getData();
        if ($ville != null) {
            $form->add('codePostal', null, [
                'mapped' => false,
                'data' => $ville->getCodePostal()
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
