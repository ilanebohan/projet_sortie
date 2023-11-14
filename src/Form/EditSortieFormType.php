<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\Positive;

class EditSortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateDebut', DateTimeType ::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker'],
                'model_timezone' => 'Europe/Paris',
                'constraints' => [
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'Vous devez mettre une date de debut supérieure à la date et l\'heure d\'aujourd\'hui'
                    ])
                ]
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée (en minutes)',
                'constraints' => [
                    new Positive([
                        'message' => 'La durée doit être positive'
                    ])
                ]
            ])
            ->add('dateCloture', DateTimeType ::class, [
                'label' => 'Date de cloture des inscriptions',
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker'],
                'model_timezone' => 'Europe/Paris',
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
            ->add('nbInscriptionsMax')
            ->add('descriptionInfos')
            ->add('siteOrganisateur')
            ->add('lieu')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
