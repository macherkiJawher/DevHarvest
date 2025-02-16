<?php
// src/Form/UserType.php
namespace App\Form;

use App\Entity\User;
use App\Enum\RoleEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email',
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                // Pour l'édition, vous pouvez rendre ce champ optionnel
                'required' => false,
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => array_combine(
                    array_map(fn (RoleEnum $role) => $role->name, array_filter(RoleEnum::cases(), fn ($role) => $role !== RoleEnum::ADMIN)),
                    array_map(fn (RoleEnum $role) => $role->value, array_filter(RoleEnum::cases(), fn ($role) => $role !== RoleEnum::ADMIN))
                ),
                'expanded' => false,
                'multiple' => false,
            ]);
            
            
            
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
