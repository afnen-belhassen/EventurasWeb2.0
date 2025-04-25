<?php

namespace App\Form;

use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Role;
use Doctrine\ORM\EntityRepository;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userUsername', TextType::class, [
                'label' => 'Username',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('userEmail', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('userPassword', PasswordType::class, [
                'label' => 'Password',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('userFirstname', TextType::class, [
                'label' => 'First Name',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('userLastname', TextType::class, [
                'label' => 'Last Name',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
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
                    'Other' => 'other',
                ],
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
->add('userPicture', FileType::class, [
    'label' => 'Profile Picture',
    'required' => false,
    'mapped' => false, // Important car l'entité attend un string (nom de fichier)
    'constraints' => [
        new \Symfony\Component\Validator\Constraints\File([
            'maxSize' => '2M',
            'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
            'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG or GIF)',
        ])
    ],
    'attr' => ['class' => 'form-control'],
])
            ->add('userPhonenumber', TextType::class, [
                'label' => 'Phone Number',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('userLevel', null, [
                'label' => 'Level',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
           # ->add(child: 'role')
           ->add('role', EntityType::class, [
            'class' => Role::class,
            'choice_label' => 'roleName',  // <--- correct property
            'label' => 'Role',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('r')
                          ->where('r.roleName != :admin')
                          ->setParameter('admin', 'ROLE_ADMIN');
            },
            'attr' => ['class' => 'form-control'],
        ])

            
            ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}