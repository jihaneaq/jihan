<?php

namespace App\Entity;

use App\Repository\ScoreRepository; // Assure-toi que l'import est là
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScoreRepository::class)]
class Score
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $score;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)] // Utilisation des constantes Types
    private \DateTimeInterface $createdAt;

    // ATTENTION : "User" doit prendre une majuscule pour correspondre à la classe
    #[ORM\ManyToOne(inversedBy: 'scores')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null; 

    public function getId(): ?int { return $this->id; }

    public function getScore(): int { return $this->score; }
    public function setScore(int $score): self {
        $this->score = $score;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }
}