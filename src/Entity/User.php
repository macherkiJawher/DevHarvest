<?php
// src/Entity/User.php
namespace App\Entity;

use App\Enum\RoleEnum;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    #[Assert\NotBlank(message: "Email is required.")]
    #[Assert\Email(message: "Please enter a valid email address.")]
    private string $email;

    #[ORM\Column(type: "string")]
    #[Assert\NotBlank(message: "Password is required.")]
    private string $password;

    #[ORM\Column(type: "string", enumType: RoleEnum::class)]
    private RoleEnum $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRoles(): array
    {
        return [$this->role->value];
    }
   

    public function getRole(): string
    {
        return $this->role->value ?? 'ROLE_USER'; // Assure qu'une valeur est toujours retournée
    }
    

    
    public function setRole(string $role): self
{
    $this->role = RoleEnum::from($role); // Convertit la chaîne en Enum
    return $this;
}



    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // Pas de données sensibles à effacer
    }
}
