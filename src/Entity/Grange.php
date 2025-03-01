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
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/',)]
    private ?string $type_grange = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Type(type: 'numeric', message: 'Veuillez entrer un nombre valide.')]
    private ?float $capacite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $productivite = null; // Attribut pour la productivité


    #[ORM\ManyToOne(inversedBy: 'granges')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Zone $zone = null;

    // src/Entity/Grange.php
public function calculerProductivite(): string
{
    $productivite = 0;
    $unit = '';

    switch ($this->type_grange) {
        case 'poulet':
            $productivite = $this->capacite * 1;  // 1 œuf par poulet et par jour
            $unit = 'œufs par jour';
            break;
        case 'vache':
            $productivite = $this->capacite * 10; // 10 litres de lait par vache
            $unit = 'litres de lait par jour';
            break;
        case 'mouton':
            $productivite = $this->capacite * 5;  
            $unit = 'kilo de viande par mois';
            break;
        case 'chameau':
            $productivite = $this->capacite * 15; // 15 litres de lait par chameau
            $unit = 'litres de lait par jour';
            break;
        case 'chevre':
            $productivite = $this->capacite * 8;  // 8 litres de lait par chèvre
            $unit = 'litres de lait par jour';
            break;
        default:
            $productivite = 0;
            $unit = 'N/A';
            break;
    }

    return $productivite . ' ' . $unit;
}


   

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

    public function getProductivite(): ?float
    {
       
        return $this->productivite;
    }

    public function setProductivite(float $productivite): static
    {
        $this->productivite = $productivite;
        return $this;
    }

   
}