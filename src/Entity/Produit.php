<?php

namespace App\Entity;

use App\Enum\CategorieProduit;
use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
    private ?float $prixunitaire = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité en stock est obligatoire.")]
    #[Assert\PositiveOrZero(message: "La quantité en stock doit être un nombre positif ou égal à zéro.")]
    private ?int $quantitestock = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Image(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/png", "image/webp"],
        mimeTypesMessage: "Veuillez télécharger une image valide (JPG, PNG, WEBP).",
        maxSizeMessage: "L'image ne doit pas dépasser 2 Mo."
    )]
    private ?string $image = null;

    #[ORM\Column(type: 'string', enumType: CategorieProduit::class)]
    #[Assert\NotBlank(message: "La catégorie est obligatoire.")]
    private ?CategorieProduit $categorie = null;

    /**
     * @var Collection<int, DetailCommande>
     */
    #[ORM\OneToMany(targetEntity: DetailCommande::class, mappedBy: 'produit')]
    private Collection $detailCommandes;

    public function __construct()
    {
        $this->detailCommandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getPrixunitaire(): ?float
    {
        return $this->prixunitaire;
    }

    public function setPrixunitaire(float $prixunitaire): static
    {
        $this->prixunitaire = $prixunitaire;

        return $this;
    }

    public function getQuantitestock(): ?int
    {
        return $this->quantitestock;
    }

    public function setQuantitestock(int $quantitestock): static
    {
        $this->quantitestock = $quantitestock;

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

    // Setter for categorie
    public function setCategorie($categorie): static
    {
        if (is_string($categorie) && CategorieProduit::tryFrom($categorie)) {
            $this->categorie = CategorieProduit::from($categorie); // Convert directly to Enum
        } elseif ($categorie instanceof CategorieProduit) {
            $this->categorie = $categorie; // If it's already an instance of CategorieProduit, just assign it
        } else {
            throw new \InvalidArgumentException("La catégorie doit être une valeur valide de l'énumération CategorieProduit.");
        }

        return $this;
    }

    /**
     * @return Collection<int, DetailCommande>
     */
    public function getDetailCommandes(): Collection
    {
        return $this->detailCommandes;
    }

    public function addDetailCommande(DetailCommande $detailCommande): static
    {
        if (!$this->detailCommandes->contains($detailCommande)) {
            $this->detailCommandes->add($detailCommande);
            $detailCommande->setProduit($this);
        }

        return $this;
    }

    public function removeDetailCommande(DetailCommande $detailCommande): static
    {
        if ($this->detailCommandes->removeElement($detailCommande)) {
            // set the owning side to null (unless already changed)
            if ($detailCommande->getProduit() === $this) {
                $detailCommande->setProduit(null);
            }
        }

        return $this;
    }

    // Method to get image URL
    public function getImageUrl(): string
    {
        return '/uploads/produits/' . $this->image;
    }
}
