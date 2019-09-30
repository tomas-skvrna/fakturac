<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserForgottenPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                TextType::class,
                [
                    'label' => 'E-mail',
                    'required' => true,
                    'label_attr' => [
                        'class' => 'sr-only',
                    ],
                    'attr' => [
                        'placeholder' => 'E-mail',
                        'class' => 'form-control mb-3 rounded-pill',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Prosím vyplňte e-mailovou adresu.',
                        ]),
                        new Email([
                            'message' => 'Tato e-mailová adresa není platná!'
                        ]),
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'Odeslat nové heslo',
                    'attr' => [
                        'class' => 'btn btn-lg btn-primary mb-3 rounded-pill',
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}