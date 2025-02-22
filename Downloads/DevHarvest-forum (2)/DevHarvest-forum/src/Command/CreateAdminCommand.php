<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\RoleEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin')]
class CreateAdminCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
{
    $user = new User();
    $user->setEmail('admin@devharvest.com');
    $user->setUsername('admin'); // Définir un nom d'utilisateur
    $user->setPassword($this->passwordHasher->hashPassword($user, 'admin123'));
    $user->setRole(RoleEnum::ADMIN->value);

    $this->entityManager->persist($user);
    $this->entityManager->flush();

    $output->writeln('Admin créé avec succès !');
    return Command::SUCCESS;
}

}
