<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
class Zone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $superficie_zone = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_zone = null;

    #[ORM\Column(length: 255)]
    private ?string $localisation_zone = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSuperficieZone(): ?float
    {
        return $this->superficie_zone;
    }

    public function setSuperficieZone(float $superficie_zone): static
    {
        $this->superficie_zone = $superficie_zone;

        return $this;
    }

    public function getNomZone(): ?string
    {
        return $this->nom_zone;
    }

    public function setNomZone(string $nom_zone): static
    {
        $this->nom_zone = $nom_zone;

        return $this;
    }

    public function getLocalisationZone(): ?string
    {
        return $this->localisation_zone;
    }

    public function setLocalisationZone(string $localisation_zone): static
    {
        $this->localisation_zone = $localisation_zone;

        return $this;
    }
}
