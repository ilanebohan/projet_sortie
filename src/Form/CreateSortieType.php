<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateSortieType extends AbstractType
{
    private VilleRepository $villeRepository;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        $this->villeRepository = new VilleRepository($registry);
        $this->logger = $logger;
    }

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
                'choice_value' => 'id',
                'query_builder' => function (VilleRepository $villeRepository) {
                    return $villeRepository->createQueryBuilder("v")->addOrderBy('v.nom');
                },
                'data' => $options['villeId'],
                'row_attr' => [
                    'class' => 'input-group mb-3'
                ],
            ])
            ->add('addLieu', SubmitType::class, [
                'validate' => false,
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ])
            ->add('creer', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }


    protected
    function addElements(FormInterface $form, $ville = null)
    {
        if ($ville) {
            $form->add('lieu', EntityType::class, array(
                'class' => Lieu::class,
                'choice_label' => 'nom',
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
                    'empty_value' => '-- Choose --',
                    'choices' => array())
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
        ]);
    }
}