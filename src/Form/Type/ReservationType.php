<?php

/**
 * Reservation type.
 */

namespace App\Form\Type;

use App\Entity\Item;
use App\Entity\Reservation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReservationType.
 */
class ReservationType extends AbstractType
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
            ->add('item', EntityType::class, [
                'class' => Item::class,
                'choice_label' => 'title',
                'label' => 'label.item',
                'disabled' => true,
                'data' => $options['data']?->getItem(),
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'label.email',
            ])
            ->add('nickname', TextType::class, [
                'required' => true,
                'label' => 'label.nickname',
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'label.comment',
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
            'data_class' => Reservation::class,
        ]);
    }
}
