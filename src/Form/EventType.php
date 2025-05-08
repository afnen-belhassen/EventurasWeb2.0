<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Titre'
            ])
            ->add('date_event', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control datetimepicker'],
                'label' => 'Date début'
            ])
            ->add('date_finEve', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control datetimepicker'],
                'label' => 'Date fin'
            ])
            ->add('prix', NumberType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Prix'
            ])
            ->add('location', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Lieu'
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'required' => false,
                'label' => 'Description'
            ])
            ->add('category', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'name',
                'attr' => ['class' => 'form-control'],
                'label' => 'Catégorie'
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage' => 'Veuillez téléverser une image valide (JPEG, PNG, GIF)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ]
            ])
            ->add('activities', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'rows' => 4],
                'required' => false,
                'label' => 'Activités prévues'
            ])
            ->add('nb_places', NumberType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Nombre de participants'
            ])
            ->add('latitude', HiddenType::class, [
                'mapped' => true,
                'required' => false,
            ])
            ->add('longitude', HiddenType::class, [
                'mapped' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
