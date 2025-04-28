<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
                'label' => 'Nom du produit',
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 100]),
                ],
                'attr' => ['placeholder' => 'Ex : T-Shirt']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [new NotBlank()],
                'attr' => ['rows' => 4]
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'constraints' => [new NotBlank(), new Positive()],
                'attr' => ['step' => '0.01']
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'constraints' => [new NotBlank(), new PositiveOrZero()],
                'attr' => ['min' => 0]
            ])
            ->add('image', FileType::class, [
                'label' => 'Profile Picture',
                'required' => false,
                'mapped' => false, 
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG or GIF)',
                    ])
                ],
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
