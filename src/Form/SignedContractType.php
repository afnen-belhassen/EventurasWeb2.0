<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class SignedContractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('signedContract', FileType::class, [
                'label' => 'Téléverser le contrat signé (PDF)',
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un fichier PDF à téléverser',
                    ]),
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez téléverser un fichier PDF valide',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale autorisée est de {{ limit }} {{ suffix }}.',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'application/pdf',
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'top',
                    'title' => 'Sélectionnez un fichier PDF contenant le contrat signé (max 10MB)'
                ],
                'help' => 'Téléversez un fichier PDF contenant le contrat signé. Notre système vérifiera automatiquement la signature. Taille maximale : 10MB.'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // No data class needed as we're just handling file uploads
        ]);
    }
} 