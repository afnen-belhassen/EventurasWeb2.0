<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: RatingRepository::class)]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    #[ORM\ManyToOne(targetEntity: Event::class, inversedBy: 'ratings')]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id_event')]
    private ?Event $event = null;
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;
        return $this;
    }

    
    #[ORM\Column(type: 'integer')]
    private ?int $user_id = null;

    #[ORM\Column(type: 'integer')]
    private ?int $value = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
    public function getUser_id(): ?int
    {
        return $this->user_id;
    }
    public function setUser_id(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }
    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }
    public function getCreation_date(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreation_date(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }   
}
