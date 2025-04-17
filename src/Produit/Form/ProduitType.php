<?php

namespace App\Produit\Form;

use App\Produit\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du produit est obligatoire']),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du produit'
                ],
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La description est obligatoire'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description du produit',
                    'rows' => 4
                ],
                'required' => true
            ])
            ->add('prix', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prix est obligatoire']),
                    new Positive(['message' => 'Le prix doit être positif'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Prix',
                    'step' => '0.01',
                    'min' => '0'
                ],
                'required' => true
            ])
            ->add('quantite', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La quantité est obligatoire']),
                    new PositiveOrZero(['message' => 'La quantité doit être positive ou nulle'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Quantité',
                    'min' => '0'
                ],
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
} 