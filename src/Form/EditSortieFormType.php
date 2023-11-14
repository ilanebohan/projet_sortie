<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditSortieFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateDebut', DateTimeType ::class, [
                'label' => 'Date et heure de la sortie',
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker']
            ])
            ->add('duree')
            ->add('dateCloture', DateTimeType ::class, [
                'label' => 'Date et heure de fin d\'inscription',
                'widget' => 'single_text',
                'attr' => ['class' => 'js-datepicker']
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
