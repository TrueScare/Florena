<?php

namespace App\Form;

use App\Entity\Locations;
use App\Entity\Plants;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class PlantsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Name',
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
            ->add('botanical_name', null, [
                'label' => 'Botanischer Name',
            ])
            ->add('image', FileType::class, [
                'label' => 'Bild',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File(maxSize: '20M', extensions: ["png", "jpg", "jpeg"]),
                ]
            ])
            ->add('light_requirement', EnumType::class, [
                'class' => LightRequirement::class,
                'choice_label' => 'value',
            ])
            ->add('temperature_requirement', EnumType::class, [
                'class' => TemperatureRequirement::class,
                'choice_label' => 'value',
            ])
            ->add('humidity_requirement', EnumType::class, [
                'class' => HumidityRequirement::class,
                'choice_label' => 'value',
            ])
            ->add('soil_type', null, [
                'label' => 'Bodenart',
            ])
            ->add('pot_size', null, [
                'label' => 'Topfgröße',
            ])
            ->add('watering_interval_days', null, [
                'label' => 'Gießintervall in Tagen',
                'attr' => ['min' => 1],
            ])
            ->add('fertilizing_interval_days', null, [
                'label' => 'Düngintervall in Tagen',
            ])
            ->add('repotting_interval_days', null, [
                'label' => 'Umtopfintervall in Tagen',
            ])
            ->add('last_watered_at', null, [
                'widget' => 'single_text',
                'label' => 'Zuletzt gegossen am',
            ])
            ->add('last_fertilized_at', null, [
                'widget' => 'single_text',
                'label' => 'Zuletzt gedüngt am',
            ])
            ->add('last_repotted_at', null, [
                'widget' => 'single_text',
                'label' => 'Zuletzt umgetopft am',
            ])
            ->add('toxic_for_humans', null, [
                'label' => 'Giftig für Menschen'
            ])
            ->add('toxic_for_animals', null, [
                'label' => 'Giftig für Tiere'
            ])
            ->add('purchase_date', null, [
                'widget' => 'single_text',
                'label' => 'Gekauft am',
            ])
            ->add('stress_score', null, [
                'label' => 'Zufriedenheitsscore',
                'disabled' => true,
            ])
            ->add('died_at', null, [
                'widget' => 'single_text',
                'label' => 'Gestorben am',
            ])
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plants::class,
            'user' => null,
        ]);
    }
}
