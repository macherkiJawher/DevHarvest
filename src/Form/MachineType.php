<?php

namespace App\Form;

use App\Entity\Machine;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType; // Ajout pour le champ fichier
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_machine', TextType::class)
            ->add('type', TextType::class)
            ->add('etat', TextType::class)
            ->add('date_dernier_entretien', DateType::class)
            ->add('prix_location_jour', IntegerType::class)
            ->add('marque', TextType::class)
            ->add('imageFile', FileType::class, [
                'label' => 'Image (JPEG, PNG)',
                'mapped' => false, // Non lié directement à l'entité
                'required' => false, // Facultatif
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Machine::class,
        ]);
    }
}