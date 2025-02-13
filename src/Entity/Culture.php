<?php

namespace App\Entity;

use App\Repository\CultureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CultureRepository::class)]
class Culture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type_culture = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_semis = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_recolte_prevue = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeCulture(): ?string
    {
        return $this->type_culture;
    }

    public function setTypeCulture(string $type_culture): static
    {
        $this->type_culture = $type_culture;

        return $this;
    }

    public function getDateSemis(): ?\DateTimeInterface
    {
        return $this->date_semis;
    }

    public function setDateSemis(\DateTimeInterface $date_semis): static
    {
        $this->date_semis = $date_semis;

        return $this;
    }

    public function getDateRecoltePrevue(): ?\DateTimeInterface
    {
        return $this->date_recolte_prevue;
    }

    public function setDateRecoltePrevue(\DateTimeInterface $date_recolte_prevue): static
    {
        $this->date_recolte_prevue = $date_recolte_prevue;

        return $this;
    }
}
