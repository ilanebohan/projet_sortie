<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class PasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une adresse e-mail.',
                    ]),
                    new Email([
                        'message' => 'Veuillez saisir une adresse e-mail valide.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Mapping du formulaire à une classe d'entité
            'data_class' => null,
        ]);
    }
}
