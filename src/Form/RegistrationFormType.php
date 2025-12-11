<?php

namespace App\Form;

use App\Entity\People;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'ФИО',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите ваше имя',
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Имя должно содержать минимум {{ limit }} символа',
                        'max' => 255,
                    ]),
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Номер телефона',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите номер телефона',
                    ]),
                    new Regex([
                        'pattern' => '/^(\+7|8)[0-9]{10}$/',
                        'message' => 'Введите корректный номер телефона (+7XXXXXXXXXX или 8XXXXXXXXXX)',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Пароли должны совпадать.',
                'first_options'  => [
                    'label' => 'Пароль',
                    'attr' => ['placeholder' => 'Минимум 6 символов'],
                ],
                'second_options' => [
                    'label' => 'Повторите пароль',
                    'attr' => ['placeholder' => 'Повторите пароль'],
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, введите пароль',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Пароль должен содержать минимум {{ limit }} символов',
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => People::class,
        ]);
    }
}