<?php

namespace App\Form;

use App\Entity\Culture;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class CultureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control']
            ])
            ->add('quantite', NumberType::class, [
                'label' => 'Quantité disponible',
                'required' => true,
                'attr' => [
                    'min' => 0,  // Valeur minimale de quantité
                    'step' => 0.1,  // Pas de la quantité
                ],
            ])
            ->add('datePlantation', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de plantation',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateRecolte', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de récolte',
                'attr' => ['class' => 'form-control']
            ])
            ->add('saison', ChoiceType::class, [
                'choices' => [
                    'Printemps' => 'printemps',
                    'Été' => 'été',
                    'Automne' => 'automne',
                    'Hiver' => 'hiver',
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image (JPG, PNG)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG ou PNG)',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Culture::class,
        ]);
    }
}

