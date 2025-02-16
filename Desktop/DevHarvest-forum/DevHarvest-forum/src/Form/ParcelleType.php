<?php
// src/Form/ParcelleType.php

namespace App\Form;

use App\Entity\Parcelle;
use App\Enum\TypeSol;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParcelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Décrivez la parcelle'],
                'constraints' => [new NotBlank(['message' => 'La description est obligatoire.'])],
            ])
            ->add('zone', TextType::class, [
                'label' => 'Zone',
                'required' => true,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Entrez la zone de la parcelle'],
                'constraints' => [new NotBlank(['message' => 'La zone est obligatoire.'])],
            ])
            ->add('superficie', NumberType::class, [
                'label' => 'Superficie (en m²)',
                'required' => true,
                'scale' => 2,
                'attr' => ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'Superficie en m²'],
                'constraints' => [new NotBlank(['message' => 'La superficie est obligatoire.'])],
            ])
            ->add('prix_de_location', NumberType::class, [
                'label' => 'Prix de location (en €)',
                'required' => true,
                'scale' => 2,
                'attr' => ['class' => 'form-control', 'step' => '0.01', 'placeholder' => 'Prix de location en €'],
                'constraints' => [new NotBlank(['message' => 'Le prix de location est obligatoire.'])],
            ])
            
            ->add('date_de_location', DateType::class, [
                'label' => 'Date de location',
                'required' => true,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'La date de location est obligatoire.'])],
            ])
            ->add('date_de_fin_location', DateType::class, [
                'label' => 'Date de fin de location',
                'required' => true,
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'La date de fin de location est obligatoire.'])],
            ])
            ->add('etat', ChoiceType::class, [
                'label' => 'État de la parcelle',
                'required' => true,
                'choices' => [
                    'Bon' => 'Bon',
                    'Moyenne' => 'Moyenne',
                    'Mauvais' => 'Mauvais',
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'L\'état de la parcelle est obligatoire.'])],
            ])
            ->add('type_sol', ChoiceType::class, [
                'label' => 'Type de sol',
                'required' => true,
                'choices' => [
                    'Argileux' => TypeSol::ARGILEUX,
                    'Sableux' => TypeSol::SABLEUX,
                    'Calcaire' => TypeSol::CALCAIRE,
                    'Loameux' => TypeSol::LOAMEUX,
                    'Autre' => TypeSol::AUTRE,
                ],
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(['message' => 'Le type de sol est obligatoire.'])],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de la parcelle (jpeg, png, gif)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control-file'],
                'constraints' => [
                    new Image([
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'maxSize' => '5M',
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou GIF).',
                        'maxSizeMessage' => 'L\'image ne doit pas dépasser 5 Mo.'
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcelle::class,
        ]);
    }
}
