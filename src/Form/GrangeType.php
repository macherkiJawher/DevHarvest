<?php

namespace App\Form;

use App\Entity\Zone;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;




class GrangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('type_grange', ChoiceType::class, [
            'choices' => [
                'Vache' => 'vache',
                'Chameau' => 'chameau',
                'Poulet' => 'poulet',
                'Mouton' => 'mouton',
                'Chèvre' => 'chevre',
            ],
            'label' => 'Type de Grange',
            'attr' => ['class' => 'form-control border-success'],
            'placeholder' => 'Choisissez un type de grange'
        ])
            ->add('capacite', NumberType::class, [
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
            ->add('zone', EntityType::class, [
                'class' => Zone::class, 
                'choice_label' => 'nom_zone',  
                'label' => 'Zone',  
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
            'data_class' => \App\Entity\Grange::class, 
        ]);
    }
    
}