<?php

namespace App\Form;

use App\Entity\CareTask;
use App\Entity\TaskAssignments;
use App\Entity\User;
use App\Repository\CareTaskRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskAssignmentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start_date', null, [
                'widget' => 'single_text',
                'label' => 'Von',
            ])
            ->add('end_date', null, [
                'widget' => 'single_text',
                'label' => 'Bis',
            ])
            ->add('to_user', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $er) use ($options) {
                    // prevent the user from selecting themselves
                    return $er->createQueryBuilder('u')
                        ->where('u.id <> :userid')
                        ->setParameter(':userid', $options['user']->getId());
                },
                'choice_label' => 'username',
                'label' => 'Übertragen an'
            ]);

        if ($options['data']->getId() === null) {
            $builder->add('care_tasks', EntityType::class, [
                'class' => CareTask::class,
                'choice_label' => function (CareTask $task) {
                    return sprintf('%s %s', $task->getPlant()->getName(), $task->getTaskType()->value);
                },
                'query_builder' => function (CareTaskRepository $er) use ($options) {
                    return $er->createQueryBuilder('c')
                        ->addSelect('p', 'u')
                        ->leftJoin('c.plant', 'p')
                        ->leftJoin('p.user', 'u')
                        ->andWhere('u = :user')
                        ->setParameter('user', $options['user']);
                },
                'multiple' => true,
                'label' => 'Aufgabe(n)',
                'mapped' => false,
                'required' => true
            ]);
        } else {
            $builder->add('care_task', EntityType::class, [
                'class' => CareTask::class,
                'choice_label' => function (CareTask $task) {
                    return sprintf('%s %s', $task->getPlant()->getName(), $task->getTaskType()->value);
                },
                'query_builder' => function (CareTaskRepository $er) use ($options) {
                    return $er->createQueryBuilder('c')
                        ->addSelect('p', 'u')
                        ->leftJoin('c.plant', 'p')
                        ->leftJoin('p.user', 'u')
                        ->andWhere('u = :user')
                        ->setParameter('user', $options['user']);
                },
                'label' => 'Aufgabe(n)',
                'required' => true
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskAssignments::class,
            'user' => null
        ]);
    }
}
