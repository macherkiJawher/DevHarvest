<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type_animal = null;

    #[ORM\Column]
    private ?int $age_animal = null;

    #[ORM\Column(length: 255)]
    private ?string $produit_fournie = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeAnimal(): ?string
    {
        return $this->type_animal;
    }

    public function setTypeAnimal(string $type_animal): static
    {
        $this->type_animal = $type_animal;

        return $this;
    }

    public function getAgeAnimal(): ?int
    {
        return $this->age_animal;
    }

    public function setAgeAnimal(int $age_animal): static
    {
        $this->age_animal = $age_animal;

        return $this;
    }

    public function getProduitFournie(): ?string
    {
        return $this->produit_fournie;
    }

    public function setProduitFournie(string $produit_fournie): static
    {
        $this->produit_fournie = $produit_fournie;

        return $this;
    }
}
