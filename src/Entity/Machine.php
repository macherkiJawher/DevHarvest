<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: 'App\Repository\MachineRepository')]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $nom_machine;

    #[ORM\Column(type: 'string', length: 255)]
    private $type;

    #[ORM\Column(type: 'string', length: 255)]
    private $etat;

    #[ORM\Column(type: 'datetime')]
    private $date_dernier_entretien;

    #[ORM\Column(type: 'integer')]
    private $prix_location_jour;

    #[ORM\Column(type: 'string', length: 255)]
    private $marque;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $image;

 

    #[ORM\OneToMany(mappedBy: 'machine', targetEntity: Reservation::class, orphanRemoval: true)]
    private $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomMachine(): ?string
    {
        return $this->nom_machine;
    }

    public function setNomMachine(string $nom_machine): self
    {
        $this->nom_machine = $nom_machine;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDateDernierEntretien(): ?\DateTimeInterface
    {
        return $this->date_dernier_entretien;
    }

    public function setDateDernierEntretien(\DateTimeInterface $date_dernier_entretien): self
    {
        $this->date_dernier_entretien = $date_dernier_entretien;

        return $this;
    }

    public function getPrixLocationJour(): ?int
    {
        return $this->prix_location_jour;
    }

    public function setPrixLocationJour(int $prix_location_jour): self
    {
        $this->prix_location_jour = $prix_location_jour;

        return $this;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(string $marque): self
    {
        $this->marque = $marque;

        return $this;
    }

       // Getter et Setter pour l'attribut image
       public function getImage(): ?string
       {
           return $this->image;
       }
   
       public function setImage(?string $image): self
       {
           $this->image = $image;
   
           return $this;
       }
   

    /**
     * @return Collection|Reservation[]
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setMachine($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getMachine() === $this) {
                $reservation->setMachine(null);
            }
        }

        return $this;
    }
} 