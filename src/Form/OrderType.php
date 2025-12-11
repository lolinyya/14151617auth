<?php

namespace App\Form;

use App\Entity\OrderEntity;
use App\Entity\People;
use App\Entity\Dish;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bundle\SecurityBundle\Security;

class OrderType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Сначала добавляем все поля, кроме people
        $builder
            ->add('dishes', EntityType::class, [
                'class' => Dish::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Блюда'
            ])
            ->add('documents', CollectionType::class, [
                'entry_type' => \App\Form\OrderDocumentType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'required' => false,
                'label' => 'Документы'
            ]);

        // Поле people добавляем только если пользователь не авторизован
        if (!$this->security->isGranted('ROLE_USER')) {
            $builder->add('people', EntityType::class, [
                'class' => People::class,
                'choice_label' => 'name',
                'placeholder' => 'Выберите клиента',
                'required' => true,
                'label' => 'Клиент'
            ]);
        }
        // Если пользователь авторизован, поле people не показываем
        // Значение установим в контроллере
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderEntity::class,
        ]);
    }
}