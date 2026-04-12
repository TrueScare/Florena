<?php

namespace App\Form;

use App\Entity\Locations;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class LocationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'constraints' => [
                    new Length(min: 2, max: 50),
                    new Regex("/[\-\_A-z ]+/")
                ]
            ])
            ->add('description', TextType::class, [
                'label' => 'Beschreibung',
                'constraints' => [
                    new Length(max: 255)
                ]
            ])
            ->add('light_condition', EnumType::class, [
                'class' => LightRequirement::class,
                'choice_label' => 'value',
                'label' => 'Lichtbedingungen',
                'help' => "Keller = schattig, Balkon = sonnig"
            ])
            ->add('temperature_level', EnumType::class, [
                'class' => TemperatureRequirement::class,
                'choice_label' => 'value',
                'label' => 'Temperaturbedingungen',
                'help' => "durchschnittliche Temperatur im Raum"
            ])
            ->add('humidity_level', EnumType::class, [
                'class' => HumidityRequirement::class,
                'choice_label' => 'value',
                'label' => 'Feuchtigkeit',
                'help' => "Badezimmer = feucht, Wohnzimmer = mittel"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Locations::class,
        ]);
    }
}
