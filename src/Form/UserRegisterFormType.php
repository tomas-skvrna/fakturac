<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\PasswordEquality;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserRegisterFormType extends AbstractType
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
                'password',
                PasswordType::class,
                [
                    'label' => 'Heslo',
                    'required' => true,
                    'label_attr' => [
                        'class' => 'sr-only',
                    ],
                    'attr' => [
                        'placeholder' => 'Heslo',
                        'class' => 'form-control mb-3 rounded-pill',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Prosím vyplňte heslo'
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Heslo musí mít alespoň 6 znaků!'
                        ])
                    ],
                ]
            )
            ->add(
                'password_retype',
                PasswordType::class,
                [
                    'label' => 'Heslo znovu',
                    'required' => true,
                    'mapped' => false,
                    'label_attr' => [
                        'class' => 'sr-only',
                    ],
                    'attr' => [
                        'placeholder' => 'Heslo znovu',
                        'class' => 'form-control mb-3 rounded-pill',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Prosím vyplňte znovu heslo'
                        ]),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Heslo musí mít alespoň 6 znaků!'
                        ]),
                        new PasswordEquality()
                    ],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'Registrovat se',
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
