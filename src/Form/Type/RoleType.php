<?php

/**
 * Role type.
 */

namespace App\Form\Type;

use App\Entity\User;
use App\Entity\Enum\UserRole;
use App\Service\UserServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RoleType.
 */
class RoleType extends AbstractType
{
    /**
     * Construct.
     *
     * @param UserServiceInterface $userService UserServiceInterface
     */
    public function __construct(private readonly UserServiceInterface $userService)
    {
    }

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
        $user = $options['data'];
        $builder
            ->add('isAdmin', CheckboxType::class, [
                'label' => 'label.is_admin',
                'required' => false,
                'data' => $this->userService->hasRole($user, UserRole::ROLE_ADMIN->value),
                'mapped' => false,
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
