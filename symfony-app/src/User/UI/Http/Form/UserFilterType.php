<?php

declare(strict_types=1);

namespace App\User\UI\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('first_name', TextType::class, ['required' => false])
            ->add('last_name', TextType::class, ['required' => false])
            ->add('gender', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'male' => 'male',
                    'female' => 'female',
                ],
            ])
            ->add('birthdate_from', DateType::class, ['required' => false, 'widget' => 'single_text'])
            ->add('birthdate_to', DateType::class, ['required' => false, 'widget' => 'single_text'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
