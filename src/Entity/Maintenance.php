<?php

namespace App\Entity;

use App\Repository\MaintenanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MaintenanceRepository::class)]
class Maintenance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_de_maintenance = null;

    #[ORM\Column(length: 255)]
    private ?string $description_probleme = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_de_resolution = null;

    #[ORM\Column]
    private ?int $cout_reparation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDeMaintenance(): ?\DateTimeInterface
    {
        return $this->date_de_maintenance;
    }

    public function setDateDeMaintenance(\DateTimeInterface $date_de_maintenance): static
    {
        $this->date_de_maintenance = $date_de_maintenance;

        return $this;
    }

    public function getDescriptionProbleme(): ?string
    {
        return $this->description_probleme;
    }

    public function setDescriptionProbleme(string $description_probleme): static
    {
        $this->description_probleme = $description_probleme;

        return $this;
    }

    public function getDateDeResolution(): ?\DateTimeInterface
    {
        return $this->date_de_resolution;
    }

    public function setDateDeResolution(\DateTimeInterface $date_de_resolution): static
    {
        $this->date_de_resolution = $date_de_resolution;

        return $this;
    }

    public function getCoutReparation(): ?int
    {
        return $this->cout_reparation;
    }

    public function setCoutReparation(int $cout_reparation): static
    {
        $this->cout_reparation = $cout_reparation;

        return $this;
    }
}