<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom_machine = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_dernier_entretien = null;

    #[ORM\Column]
    private ?int $prix_location_jour = null;

    #[ORM\Column(length: 255)]
    private ?string $marque = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomMachine(): ?string
    {
        return $this->nom_machine;
    }

    public function setNomMachine(string $nom_machine): static
    {
        $this->nom_machine = $nom_machine;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDateDernierEntretien(): ?\DateTimeInterface
    {
        return $this->date_dernier_entretien;
    }

    public function setDateDernierEntretien(\DateTimeInterface $date_dernier_entretien): static
    {
        $this->date_dernier_entretien = $date_dernier_entretien;

        return $this;
    }

    public function getPrixLocationJour(): ?int
    {
        return $this->prix_location_jour;
    }

    public function setPrixLocationJour(int $prix_location_jour): static
    {
        $this->prix_location_jour = $prix_location_jour;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): static
    {
        $this->marque = $marque;

        return $this;
    }
}
