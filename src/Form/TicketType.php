<?php

namespace App\Form;
use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('ticket_code', TextType::class, [
            'label' => 'Ticket Code',
            'attr' => [
                'readonly' => true,
                'class' => 'form-control-plaintext'
            ]
        ])
        ->add('seat_number', TextType::class, [
            'label' => 'Seat Number',
            'attr' => [
                'readonly' => true,
                'class' => 'form-control-plaintext'
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
