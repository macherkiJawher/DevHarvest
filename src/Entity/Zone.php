<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Entity(repositoryClass: ZoneRepository::class)]
class Zone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Assert\Type(type: 'numeric', message: 'Veuillez entrer un nombre valide.')]
    private ?float $superficie_zone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Regex(
        pattern: '/^[A-Za-zÀ-ÖØ-öø-ÿ\s]+$/',)]
    private ?string $nom_zone = null;

    #[ORM\Column(length: 255)]
    private ?string $localisation_zone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $longitude = null;


    /**
     * @var Collection<int, Grange>
     */
    #[ORM\OneToMany(targetEntity: Grange::class, mappedBy: 'zone')]
    private Collection $granges;

    public function __construct()
    {
        $this->granges = new ArrayCollection();
    }

   
   

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

    /**
     * @return Collection<int, Grange>
     */
    public function getGranges(): Collection
    {
        return $this->granges;
    }

    public function addGrange(Grange $grange): static
    {
        if (!$this->granges->contains($grange)) {
            $this->granges->add($grange);
            $grange->setZone($this);
        }

        return $this;
    }

    public function removeGrange(Grange $grange): static
    {
        if ($this->granges->removeElement($grange)) {
            
            if ($grange->getZone() === $this) {
                $grange->setZone(null);
            }
        }

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

    public function getLatitude(): ?float
{
    return $this->latitude;
}

public function setLatitude(?float $latitude): self
{
    $this->latitude = $latitude;
    return $this;
}

public function getLongitude(): ?float
{
    return $this->longitude;
}

public function setLongitude(?float $longitude): self
{
    $this->longitude = $longitude;
    return $this;
}



    
    
}