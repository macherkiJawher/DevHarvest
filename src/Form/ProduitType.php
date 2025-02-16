<?php

// src/Form/ProduitType.php
namespace App\Form;

use App\Entity\Produit;
use App\Enum\CategorieProduit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('prixunitaire', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prix ne peut pas être vide']),
                    new Positive(['message' => 'Le prix doit être un nombre positif'])
                ],
            ])
            ->add('quantitestock', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La quantité en stock ne peut pas être vide']),
                    new GreaterThan(['value' => 0, 'message' => 'La quantité en stock doit être supérieure à zéro'])
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image (JPG, PNG, max 2MB)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG ou PNG).'
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('categorie', ChoiceType::class, [
                'choices' => CategorieProduit::cases(), // Utilise directement l'énumération
                'choice_label' => fn (CategorieProduit $categorie) => $categorie->value, // Affiche les valeurs des énumérations
                'choice_value' => fn (?CategorieProduit $categorie) => $categorie?->value, // Convertit l'instance en string pour Symfony
                'expanded' => true, // Affichage en boutons radio
                'multiple' => false, // Une seule sélection possible
           
            
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
