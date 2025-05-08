<?php

namespace App\Form;

use App\Entity\ConversationMessage;
use App\Entity\ReclamationConversation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

/* class ConversationMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Tapez votre message...',
                    'class' => 'form-control',
                    'rows' => 2,
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConversationMessage::class,
        ]);
    }
} */

class ConversationMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Tapez votre message...',
                    'class' => 'form-control',
                    'rows' => 2,
                ]
            ])
            ->add('attachments', FileType::class, [
                'label' => false,
                'mapped' => false,
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*,application/pdf'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypesMessage' => 'Type de fichier non supporté.'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConversationMessage::class,
        ]);
    }
}
