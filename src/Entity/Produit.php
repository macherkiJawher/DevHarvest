<?php

namespace App\Entity;

use App\Enum\CategorieProduit;
use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\User;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Le prix unitaire est obligatoire.")]
    #[Assert\Positive(message: "Le prix unitaire doit être un nombre positif.")]
    private ?float $prixUnitaire = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité en stock est obligatoire.")]
    #[Assert\PositiveOrZero(message: "La quantité en stock doit être un nombre positif ou égal à zéro.")]
    private ?int $quantiteStock = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Image(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/png", "image/webp"],
        mimeTypesMessage: "Veuillez télécharger une image valide (JPG, PNG, WEBP).",
        maxSizeMessage: "L'image ne doit pas dépasser 2 Mo."
    )]
    private ?string $image = null;

    #[ORM\Column(type: Types::STRING, enumType: CategorieProduit::class)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    private ?CategorieProduit $categorie = null;

    #[ORM\OneToMany(targetEntity: DetailCommande::class, mappedBy: 'produit', cascade: ['remove'])]
    private Collection $detailCommandes;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "agriculteur_id", referencedColumnName: "id", nullable: false)]
    private ?User $agriculteur = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAjout = null;
    
    public function __construct()
    {
        $this->dateAjout = new \DateTime(); 
        $this->detailCommandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $dateAjout): self
    {
        $this->dateAjout = $dateAjout;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPrixUnitaire(): ?float
    {
        return $this->prixUnitaire;
    }

    public function setPrixUnitaire(float $prixUnitaire): self
    {
        $this->prixUnitaire = $prixUnitaire;
        return $this;
    }

    public function getQuantiteStock(): ?int
    {
        return $this->quantiteStock;
    }

    public function setQuantiteStock(int $quantiteStock): self
    {
        $this->quantiteStock = $quantiteStock;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getCategorie(): ?CategorieProduit
    {
        return $this->categorie;
    }

    public function setCategorie(CategorieProduit|string $categorie): self
    {
        if (is_string($categorie) && CategorieProduit::tryFrom($categorie)) {
            $this->categorie = CategorieProduit::from($categorie);
        } elseif ($categorie instanceof CategorieProduit) {
            $this->categorie = $categorie;
        } else {
            throw new \InvalidArgumentException("La catégorie doit être une valeur valide de l'énumération CategorieProduit.");
        }
        return $this;
    }

    public function getAgriculteur(): ?User
    {
        return $this->agriculteur;
    }

    public function setAgriculteur(?User $agriculteur): self
    {
        $this->agriculteur = $agriculteur;
        return $this;
    }

    public function getDetailCommandes(): Collection
    {
        return $this->detailCommandes;
    }

    public function addDetailCommande(DetailCommande $detailCommande): self
    {
        if (!$this->detailCommandes->contains($detailCommande)) {
            $this->detailCommandes->add($detailCommande);
            $detailCommande->setProduit($this);
        }
        return $this;
    }

    public function removeDetailCommande(DetailCommande $detailCommande): self
    {
        if ($this->detailCommandes->removeElement($detailCommande)) {
            if ($detailCommande->getProduit() === $this) {
                $detailCommande->setProduit(null);
            }
        }
        return $this;
    }

    public function getImageUrl(): string
    {
        return '/uploads/produits/' . $this->image;
    }
}
