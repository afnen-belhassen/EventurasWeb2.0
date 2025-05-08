<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Reclamation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
        ->add('subject', ChoiceType::class, [
            'label' => 'Sujet',
            'choices' => [
                'Évènement' => 'Evenement',
                'Organisateur' => 'Organizateur',
                'Problème Technique' => 'Probleme Technique',
            ],
            'placeholder' => 'Choisir un sujet',
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le sujet est requis.']),
            ],
        ])
        ->add('id_event', EntityType::class, [
            'class' => Event::class,
            'choice_label' => 'title', // or 'title', or whatever property identifies the event
            'placeholder' => 'Choisir un évènement',
            'label' => 'Évènement',
            'required' => true,
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
