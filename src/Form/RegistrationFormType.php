<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,
                [
                    'label' => 'Adresse email *',
                    'attr' => ['autocomplete' => 'email'],
                    'constraints' => [
                        new Email([
                            'message' => 'Veuillez entrer une adresse email valide',
                        ]),
                    ],
                ])
            ->add('nom', null, ['label' => 'Nom *'])
            ->add('prenom', null, ['label' => 'Prénom *'])
            ->add('telephone', null, ['label' => 'Téléphone',
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^0[1-9]([-. ]?[0-9]{2}){4}$/',
                        'message' => 'Le numéro de téléphone doit être au format 0X XX XX XX XX'
                    ]),
                ],
            ])
            ->add('administrateur', CheckboxType::class, array('required' => false))
            //->add('actif', CheckboxType::class)
            ->add('site')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}