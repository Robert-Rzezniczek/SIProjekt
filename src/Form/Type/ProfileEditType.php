<?php

/**
 * Profile edit type.
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ProfileEditType.
 */
class ProfileEditType extends AbstractType
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
            ->add('email', EmailType::class, [
                'label' => 'label.email',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'message.email_not_blank',
                    ]),
                    new Assert\Email([
                        'message' => 'message.invalid_email_format',
                        'mode' => 'strict',
                    ]),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 180,
                        'minMessage' => 'message.email_too_short',
                        'maxMessage' => 'message.email_too_long',
                    ]),
                ],
            ])
            ->add('nickname', TextType::class, [
                'label' => 'label.nickname',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 50,
                        'maxMessage' => 'message.nickname_too_long',
                    ]),
                ],
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     *
     * @return void void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
