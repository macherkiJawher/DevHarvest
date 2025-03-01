<?php
// src/Entity/Culture.php

namespace App\Entity;

use App\Repository\CultureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CultureRepository::class)]
class Culture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: "text")]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_plantation = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_recolte = null;

    #[ORM\Column(type: "string")]
    private ?string $saison = null; // Ajout de l'attribut saison

    // Relation OneToMany vers Parcelle
    #[ORM\OneToMany(mappedBy: 'cultureActuelle', targetEntity: Parcelle::class)]
    private Collection $parcelles;

    public function __construct()
    {
        $this->parcelles = new ArrayCollection();
    }

    public function getParcelles(): Collection
    {
        return $this->parcelles;
    }

    public function addParcelle(Parcelle $parcelle): self
    {
        if (!$this->parcelles->contains($parcelle)) {
            $this->parcelles[] = $parcelle;
            $parcelle->setCultureActuelle($this);
        }

        return $this;
    }

    public function removeParcelle(Parcelle $parcelle): self
    {
        if ($this->parcelles->removeElement($parcelle)) {
            // Définit la culture actuelle de la parcelle à null
            if ($parcelle->getCultureActuelle() === $this) {
                $parcelle->setCultureActuelle(null);
            }
        }

        return $this;
    }
    #[ORM\Column(type: "float")]
    private ?float $quantite = 0;


    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    // Getters et setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getDatePlantation(): ?\DateTimeInterface
    {
        return $this->date_plantation;
    }

    public function setDatePlantation(\DateTimeInterface $date_plantation): static
    {
        $this->date_plantation = $date_plantation;
        return $this;
    }

    public function getDateRecolte(): ?\DateTimeInterface
    {
        return $this->date_recolte;
    }

    public function setDateRecolte(\DateTimeInterface $date_recolte): static
    {
        $this->date_recolte = $date_recolte;
        return $this;
    }

    public function getSaison(): ?string
    {
        return $this->saison; // Retourne la saison
    }

    public function setSaison(string $saison): static
    {
        $this->saison = $saison; // Définit la saison
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}
