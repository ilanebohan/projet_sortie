<?php

namespace App\Form;

use App\Entity\User;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;

class EditUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('allowImageDiffusion', CheckboxType::class, [
                'label'    => 'Autoriser la diffusion de l\'image',
                'required' => false,
            ])
            ->add('image', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/pjpeg',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid Image',
                    ])
                ],
            ]);
        if ($options['admin']) {
            $builder
                ->add('nom')
                ->add('prenom')
                ->add('site');
        } else {
            $builder
                ->add('nom', null, [
                    'disabled' => true,
                ])
                ->add('prenom', null, [
                    'disabled' => true,
                ])
                ->add('site', null, [
                    'disabled' => true,
                ]);
        }
        $builder
            ->add('telephone', null, ['label' => 'Téléphone',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^0[1-9]([-. ]?[0-9]{2}){4}$/',
                        'message' => 'Le numéro de téléphone doit être au format 0X XX XX XX XX'
                    ]),
                ],
            ])
            ->add('email')
            ->add('login')
            ->add('modifierMdp', SubmitType::class, [
                'label' => 'Modifier mon mot de passe',
                'validate' => false,
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'admin' => Boolean::class,
        ]);
    }
}
