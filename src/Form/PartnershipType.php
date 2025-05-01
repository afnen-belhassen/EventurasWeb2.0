<?php

namespace App\Form;

use App\Entity\Partnership;
use App\Entity\Partner;
use App\Enum\ContractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartnershipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('partnerId', EntityType::class, [
                'class' => Partner::class,
                'choice_label' => 'name',
                'label' => 'Partenaire',
                'required' => true,
                'attr' => ['class' => 'form-control bg-dark text-white border-secondary']
            ])
            ->add('contracttype', ChoiceType::class, [
                'label' => 'Type de Contrat',
                'required' => true,
                'choices' => ContractType::getChoices(),
                'attr' => ['class' => 'form-control bg-dark text-white border-secondary']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['class' => 'form-control bg-dark text-white border-secondary', 'rows' => 5]
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'pending',
                    'Actif' => 'active',
                    'Inactif' => 'inactive'
                ],
                'label' => 'Statut',
                'required' => true,
                'attr' => ['class' => 'form-control bg-dark text-white border-secondary']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Partnership::class,
        ]);
    }
} 