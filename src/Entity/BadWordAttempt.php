<?php

namespace App\Entity;

use App\Repository\BadWordAttemptRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BadWordAttemptRepository::class)]
class BadWordAttempt
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?BadWord $badWord = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $attemptedAt = null;

    #[ORM\Column]
    private ?int $user_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBadWord(): ?BadWord
    {
        return $this->badWord;
    }

    public function setBadWord(?BadWord $badWord): static
    {
        $this->badWord = $badWord;

        return $this;
    }

    public function getAttemptedAt(): ?\DateTimeImmutable
    {
        return $this->attemptedAt;
    }

    public function setAttemptedAt(\DateTimeImmutable $attemptedAt): static
    {
        $this->attemptedAt = $attemptedAt;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }
}
