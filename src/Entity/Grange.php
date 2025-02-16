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
    private ?string $type_grange = null;

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
}
