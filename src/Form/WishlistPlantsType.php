<?php

namespace App\Form;

use App\Entity\Locations;
use App\Entity\User;
use App\Entity\WishlistPlants;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class WishlistPlantsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'constraints' => [
                    new Length(min: 2, max: 50),
                    new Regex("/[\-\_A-z ]+/"),
                    new NotBlank(),
                ]
            ])
            ->add('description', null, [
                'label' => 'Beschreibung',
                'constraints' => [
                    new Length(max: 255),
                ]
            ])
            ->add('botanical_name')
            ->add('location', EntityType::class, [
                'class' => Locations::class,
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('l')
                        ->orderBy('l.name', 'ASC')
                        ->andWhere('l.user = :user')
                        ->setParameter('user', $options['user']);
                },
                'choice_label' => 'Name ',
                'label' => 'Standort',
                'required' => false,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'attr' => ['hidden' => true],
                'data' => $options['user'],
                'choices' => [$options['user']],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WishlistPlants::class,
            'user' => null,
        ]);
    }
}
