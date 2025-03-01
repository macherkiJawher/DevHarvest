<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $quantite_stock = null;

    #[ORM\Column(length: 255)]
    private ?string $type_stock = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantiteStock(): ?float
    {
        return $this->quantite_stock;
    }

    public function setQuantiteStock(float $quantite_stock): static
    {
        $this->quantite_stock = $quantite_stock;

        return $this;
    }

    public function getTypeStock(): ?string
    {
        return $this->type_stock;
    }

    public function setTypeStock(string $type_stock): static
    {
        $this->type_stock = $type_stock;

        return $this;
    }
}
