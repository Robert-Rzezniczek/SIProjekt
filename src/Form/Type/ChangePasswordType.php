<?php

/**
 * Change password type.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChangePasswordType.
 */
class ChangePasswordType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options Form options
     *
     * @return void void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', PasswordType::class, [
                'label' => 'label.new_password',
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'message.password_not_blank',
                    ]),
                    new Assert\Length([
                        'min' => 6,
                        'minMessage' => 'message.password_too_short',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/',
                        'message' => 'message.password_must_contain_letter_and_number',
                    ]),
                ],
            ]);
    }
}
