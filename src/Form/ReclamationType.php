<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_user', IntegerType::class, [
                'label' => 'User ID',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le champ utilisateur est requis.']),
                ],
            ])
            ->add('id_event', IntegerType::class, [
                'label' => 'Event ID',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le champ événement est requis.']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description est requise.']),
                    new Assert\Length([
                        'min' => 20,
                        'max' => 300,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('subject', TextType::class, [
                'label' => 'Sujet',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le sujet est requis.']),
                    new Assert\Length([
                        'min' => 10,
                        'max' => 30,
                        'minMessage' => 'Le sujet doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le sujet ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('attachments', FileType::class, [
                'label'    => 'Pièces jointes',
                'mapped'   => false,
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'id'    => 'attachmentsInput',
                    'style' => 'display:none',
                ],
                'label_attr' => [
                    'style' => 'display:none',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
