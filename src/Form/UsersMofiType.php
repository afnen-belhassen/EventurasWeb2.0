<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UsersMofiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userUsername')
            ->add('userEmail')
            ->add('userPassword')
            ->add('userFirstname')
            ->add('userLastname')
            ->add('userBirthday', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date of Birth',
                'required' => false,
                'html5' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Select your birthdate',
                ],
            ])   
            ->add('userGender', ChoiceType::class, [
                'label' => 'Gender',
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female',
                    'Other' => 'other'
                ],
                'required' => false,
                'placeholder' => 'Choose an option'
            ])
            ->add('userPicture', FileType::class, [
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
            ])
            ->add('userPhonenumber')
            ->add('userLevel');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
