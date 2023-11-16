<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AddUserCsvType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Ajouter des utilisateurs depuis un CSV ou un XML:',
                'mapped' => false,
                'required' => false,

                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'text/csv',
                            'application/xml',
                            'text/plain'
                        ],
                        'mimeTypesMessage' => 'Merci de donner un fichier Csv ou XML correct',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
