<?php

namespace App\Entity;

use App\Repository\GrangeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GrangeRepository::class)]
class Grange
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type_grange = null;

    #[ORM\Column]
    private ?float $capacite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'granges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Zone $zone = null;

 

   

   

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeGrange(): ?string
    {
        return $this->type_grange;
    }

    public function setTypeGrange(string $type_grange): static
    {
        $this->type_grange = $type_grange;

        return $this;
    }

    public function getCapacite(): ?float
    {
        return $this->capacite;
    }

    public function setCapacite(float $capacite): static
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getZone(): ?Zone
    {
        return $this->zone;
    }

    public function setZone(?Zone $zone): static
    {
        $this->zone = $zone;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }
    
    public function setImage(?string $image): static
    {
        $this->image = $image;
    
        return $this;
    }


   
}
