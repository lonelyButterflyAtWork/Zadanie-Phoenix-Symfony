<?php

declare(strict_types=1);

namespace App\Form\Phoenix;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Choice;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Regex(
                        pattern: '/^[\p{L}]+$/u',
                        message: 'First name must contain only letters.',
                    ),
                ],
            ])
            ->add('last_name', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Regex(
                        pattern: '/^[\p{L}]+$/u',
                        message: 'Last name must contain only letters.',
                    ),
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female',
                ],
                'constraints' => [
                    new NotBlank(),
                    new Choice([
                        'choices' => ['male', 'female'],
                        'message' => 'Gender must be Male or Female.',
                    ]),
                ],
            ])
            ->add('birthdate', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }
}
