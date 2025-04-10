<?php

namespace App\Form;

use App\Entity\Zone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


class ZoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('superficie_zone', NumberType::class, [
            'label' => 'Superficie de la Zone',
            'scale' => 2, 
            'html5' => true, 
            'constraints' => [
                new NotBlank([
                    'message' => 'La superficie est obligatoire.',
                ]),
                new Positive([
                    'message' => 'La superficie doit être un nombre positif.',
                ]),
            ],
        ])
        ->add('nom_zone', TextType::class, [
            'label' => 'Nom de la Zone',
            'constraints' => [
                new NotBlank([
                    'message' => 'Le nom de la zone ne peut pas être vide.',
                ]),
                new Regex([
                    'pattern' => '/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/',
                    'message' => 'Le nom de la zone ne doit contenir que des lettres et des espaces.',
                ]),
            ],
        ])
        ->add('localisation_zone', TextType::class, [
            'attr' => ['class' => 'form-control', 'readonly' => true], // Empêche la saisie manuelle
            'required' => true
        ])
            ->add('image', FileType::class, [
                'label' => 'Zone Image (JPG/PNG)',
                'mapped' => false, 
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPG or PNG)',
                    ])
                ],
            ])
        
        
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Zone::class,
        ]);
    }
}