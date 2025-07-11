<?php

namespace App\Form;

use App\Entity\Track;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrackType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('title', TextType::class, [
        'constraints' => [
          new Assert\NotBlank([
            'message' => 'Please enter a value for the title field',
          ])
        ]
      ])
      ->add('artist', TextType::class, [
        'constraints' => [new Assert\NotBlank([
          'message' => 'Please enter a value for the artist field',
        ])]
      ])
      ->add('duration', IntegerType::class, [
        'constraints' => [
          new Assert\NotBlank([
            'message' => 'Please specify the duration in seconds.',
          ]),
          new Assert\GreaterThan([
            'value' => 0,
            'message' => 'Duration must be greater than 0.',
          ]),
        ]
      ])
      ->add('isrc', TextType::class, [
        'required' => false,
        'constraints' => [
          new Assert\Regex([
            'pattern' => '/^[A-Z]{2}-[A-Z0-9]{3}-\d{2}-\d{5}$/',
            'message' => 'ISRC must match the format: XX-XXX-00-00000'
          ])
        ]
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Track::class,
    ]);
  }
}
