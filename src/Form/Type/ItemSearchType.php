<?php

/**
 * Item search type.
 */

namespace App\Form\Type;

use App\Entity\Category;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ItemSearchType.
 */
class ItemSearchType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'label.search_by_title',
                'required' => false,
                'attr' => ['placeholder' => 'label.search_by_title'],
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'label.rating',
                'required' => false,
                'choices' => [
                    'label.5_stars' => 5,
                    'label.4_stars_and_above' => 4,
                    'label.3_stars_and_above' => 3,
                    'label.2_stars_and_above' => 2,
                    'label.1_star_and_above' => 1,
                ],
                'placeholder' => 'label.any_rating',
            ])
            ->add('categoryId', EntityType::class, [
                'label' => 'label.category',
                'required' => false,
                'class' => Category::class,
                'choice_label' => 'title',
                'placeholder' => 'label.any_category',
            ])
            ->add('tagId', EntityType::class, [
                'label' => 'label.tags',
                'required' => false,
                'class' => Tag::class,
                'choice_label' => 'title',
                'multiple' => false,
                'expanded' => false,
                'placeholder' => 'label.any_tag',
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     *
     * @return string The prefix of the template block name
     *
     * @psalm-return ''
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
