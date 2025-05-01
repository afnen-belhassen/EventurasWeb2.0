<?php
namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use App\Entity\Categorie; // Import the Categorie entity
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('date_event', null, [
                'widget' => 'single_text',
            ])
            ->add('date_finEve')
            ->add('location')
            ->add('category', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'name', // Display the name of the category
            ])
            ->add('image', FileType::class, [
                'label' => 'Event Image (JPEG, PNG)',
                'mapped' => false, // This field is not mapped to the entity directly
                'required' => false, // Make it optional
            ])
            ->add('activities')
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'required' => true,
            ])
            ->add('nb_places')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
