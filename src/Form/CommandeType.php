<?php

namespace App\Form;

use App\Entity\Commande;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomClient', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom du client est obligatoire']),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du client'
                ],
                'required' => true
            ])
            ->add('adresse', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'L\'adresse est obligatoire']),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'L\'adresse doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'L\'adresse ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Adresse'
                ],
                'required' => true
            ])
            ->add('telephone', TelType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le numéro de téléphone est obligatoire']),
                    new Regex([
                        'pattern' => '/^[2459][0-9]{7}$/',
                        'message' => 'Le numéro de téléphone doit être un numéro tunisien valide (8 chiffres commençant par 2, 4, 5 ou 9)'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Numéro de téléphone',
                    'pattern' => '[2459][0-9]{7}'
                ],
                'required' => true
            ])
            ->add('produit', EntityType::class, [
                'class' => Produit::class,
                'choice_label' => 'nom',
                'constraints' => [
                    new NotBlank(['message' => 'Le produit est obligatoire'])
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => true
            ])
            ->add('quantite', IntegerType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La quantité est obligatoire']),
                    new Positive(['message' => 'La quantité doit être positive'])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Quantité',
                    'min' => 1
                ],
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}
