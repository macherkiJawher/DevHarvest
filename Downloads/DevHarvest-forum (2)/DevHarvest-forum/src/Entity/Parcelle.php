<?php
// src/Entity/Parcelle.php

namespace App\Entity;

use App\Repository\ParcelleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\TypeSol;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParcelleRepository::class)]
class Parcelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $zone = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $superficie = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $prix_de_location = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_de_location = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date_de_fin_location = null;

    #[ORM\Column(length: 255)]
    private ?string $etat = null;

    #[ORM\Column(type: 'string', enumType: TypeSol::class)]
    private ?TypeSol $typeSol = null;

    #[ORM\ManyToOne(targetEntity: Culture::class, inversedBy: 'parcelles')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Culture $cultureActuelle = null;

    #[ORM\ManyToMany(targetEntity: Culture::class)]
    private Collection $historiqueCultures;

    // Gestion de l'image
    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Image(
        mimeTypes: ['image/jpeg', 'image/png', 'image/gif'],
        maxSize: '5M',
        mimeTypesMessage: 'Veuillez télécharger une image valide (jpeg, png, gif).',
        maxSizeMessage: 'L\'image ne doit pas dépasser 5 Mo.'
    )]
    private ?string $image = null;

    public function __construct()
    {
        $this->historiqueCultures = new ArrayCollection();
    }

    // Getters et Setters...
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(string $zone): static
    {
        $this->zone = $zone;
        return $this;
    }

    public function getSuperficie(): ?float
    {
        return $this->superficie;
    }

    public function setSuperficie(float $superficie): static
    {
        $this->superficie = $superficie;
        return $this;
    }

    public function getPrixDeLocation(): ?float
    {
        return $this->prix_de_location;
    }

    public function setPrixDeLocation(float $prix_de_location): static
    {
        $this->prix_de_location = $prix_de_location;
        return $this;
    }

    public function getDateDeLocation(): ?\DateTimeInterface
    {
        return $this->date_de_location;
    }

    public function setDateDeLocation(\DateTimeInterface $date_de_location): static
    {
        $this->date_de_location = $date_de_location;
        return $this;
    }

    public function getDateDeFinLocation(): ?\DateTimeInterface
    {
        return $this->date_de_fin_location;
    }

    public function setDateDeFinLocation(\DateTimeInterface $date_de_fin_location): static
    {
        $this->date_de_fin_location = $date_de_fin_location;
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

    public function getTypeSol(): ?TypeSol
    {
        return $this->typeSol;
    }

    public function setTypeSol(TypeSol $typeSol): static
    {
        $this->typeSol = $typeSol;
        return $this;
    }

    // Nouvelle méthode pour obtenir la valeur sous forme de chaîne
    public function getTypeSolAsString(): string
    {
        return $this->typeSol->value;  // Accède à la valeur de l'énumération
    }

    public function getCultureActuelle(): ?Culture
    {
        return $this->cultureActuelle;
    }

    public function setCultureActuelle(?Culture $cultureActuelle): static
    {
        $this->cultureActuelle = $cultureActuelle;
        return $this;
    }

    public function getHistoriqueCultures(): Collection
    {
        return $this->historiqueCultures;
    }

    public function addHistoriqueCulture(Culture $culture): static
    {
        if (!$this->historiqueCultures->contains($culture)) {
            $this->historiqueCultures->add($culture);
        }
        return $this;
    }

    public function removeHistoriqueCulture(Culture $culture): static
    {
        $this->historiqueCultures->removeElement($culture);
        return $this;
    }

    // Gestion de l'image
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
