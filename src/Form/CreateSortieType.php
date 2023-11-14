<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class CreateSortieType extends AbstractType
{
    private VilleRepository $villeRepository;
    private lieuRepository $lieuRepository;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        $this->lieuRepository = new LieuRepository($registry);
        $this->villeRepository = new VilleRepository($registry);
        $this->logger = $logger;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom de la sortie *',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez mettre un nom'
                    ])
                ]
            ])
            ->add('dateDebut', DateTimeType ::class, [
                'label' => 'Date et heure de la sortie *',
                'widget' => 'single_text',
                'model_timezone' => 'Europe/Paris',
                'constraints' => [
                    new GreaterThan([
                        'value' => 'now',
                        'message' => 'Vous devez mettre une date de Debut supérieure à la date et l\'heure d\'aujourd\'hui'
                    ])
                ]
            ])
            ->add('dateCloture', DateTimeType ::class, [
                'label' => 'Date limite d\'inscription *',
                'widget' => 'single_text',
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
            ->add('nbInscriptionsMax', NumberType::class, [
                'label' => 'Nombres de places *',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez mettre un nom'
                    ])
                ]
            ])
            ->add('duree', NumberType::class, [
                'label' => 'Durée (en minutes) *',
                'constraints' => [
                    new Positive([
                        'message' => 'La durée doit être positive'
                    ]),
                    new NotBlank([
                        'message' => 'Vous devez mettre une durée'
                    ])
                ]
            ])
            ->add('descriptionInfos', TextareaType::class, [
                'label' => 'Description et infos *'
            ])
            ->add('ville', EntityType::class, [
                'mapped' => false,
                'label' => 'Ville *',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'choice_value' => 'id',
                'query_builder' => function (VilleRepository $villeRepository) {
                    return $villeRepository->createQueryBuilder("v")->addOrderBy('v.nom');
                },
                'data' => $options['villeId']
            ])
            ->add('addLieu', SubmitType::class, [
                'label' => '+',
                'validate' => false,
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
            ->add('creer', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
            ->add('publier', SubmitType::class, [
                'label' => 'Publier la sortie',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
        ->add('estPrivee', CheckboxType::class, [
            'label' => 'Sortie privée ',
            'required' => false,
        ]);
        // When the user selects a ville, we'll reload the lieu field.
        $formModifier = function (FormInterface $form, Ville $ville = null) {
            $lieux = null === $ville ? [] : $this->getLieuxOfVille($ville->getId());

            $form->add('lieu', EntityType::class, [
                'class' => 'App\Entity\Lieu',
                'label' => 'Lieu *',
                'placeholder' => '-- Choisir un lieu --',
                'choices' => $lieux,
            ]);
        };
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getVille());
            }
        );
        $builder->get('ville')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $ville = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $ville);  // Here line 58 and error show this line
            }
        );



        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }


    public function getLieuxOfVille(int $idVille)
    {
        $ville = $this->villeRepository->findOneBy(['id' => $idVille]);
        $lieu = $this->lieuRepository->findBy(['ville' => $ville]);

        return $lieu;
    }


    protected
    function addElements(FormInterface $form, $ville = null)
    {

        if ($ville) {
            $form->add('lieu', EntityType::class, array(
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'label' => 'Lieu *',
                'query_builder' => function (LieuRepository $lr) use ($ville) {
                    return $lr->createQueryBuilder('l')
                        ->join('l.ville', 'v')
                        ->where('v.id = :villeID')
                        ->setParameter('villeID', $ville->getID())
                        ->orderBy('l.nom', 'ASC');
                }));
        } else {
            $form->add('lieu', ChoiceType::class, array(
                    'choice_label' => 'nom',
                    'label' => 'Lieu *',
                    'choices' => array()),
            );
        }
    }
    public
    function onPreSubmit(FormEvent $event): void
    {
        $form = $event->getForm();
        $ville = $form->get("ville")->getData();
        $this->addElements($form, $ville);
    }

    public
    function onPreSetData(FormEvent $event): void
    {
        $form = $event->getForm();
        $ville = $form->get("ville")->getData();

        $this->addElements($form,$ville);
    }

    public
    function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'villeId' => Ville::class,
            'data_class' => Sortie::class,
        ]);
    }
}
