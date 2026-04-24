<?php

namespace App\Form;

use App\Entity\Plants;
use App\Entity\PropagationActions;
use App\Enum\PropagationMethod;
use App\Enum\Status;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PropagationActionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('method', null, [
                'class' => PropagationMethod::class,
                'label' => 'Methode',
                'choice_label' => 'value',
                'required' => true,
            ])
            ->add('planned_date', null, [
                'widget' => 'single_text',
                'label' => 'geplant am',
                'required' => true,
            ])
            ->add('status', null,[
                'class' => Status::class,
                'label' => 'Status',
                'choice_label' => 'value',
                'required' => true,
            ])
            ->add('notes', null, [
                'label' => 'Notizen'
            ])
            ->add('plant', EntityType::class, [
                'class' => Plants::class,
                'choice_label' => 'Name',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('p')
                        ->andWhere('p.user = :user')
                        ->orderBy('p.name', 'ASC')
                        ->setParameter('user', $options['user']);
                },
                'label' => 'Pflanze'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PropagationActions::class,
            'user' => null
        ]);
    }
}
