<?php

namespace App\Entity;

use App\Repository\PasswordResetTokenRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Ulid;

#[ORM\Entity(repositoryClass: PasswordResetTokenRepository::class)]
class PasswordResetToken
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $token = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $expirationDate = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;

    public function __construct(UserInterface $user)
    {
        $this->User = $user;
        $this->token = Uuid::uuid4()->toString();
        $this->expirationDate = new \DateTime('+1 hour'); // Le jeton expirera après 1 heure
    }

    public function isExpired(): bool
    {
        return $this->expirationDate < new \DateTime();
    }




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(\DateTimeInterface $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(User $User): static
    {
        $this->User = $User;

        return $this;
    }
}
