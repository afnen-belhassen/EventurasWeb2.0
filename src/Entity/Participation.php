<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ParticipationRepository;

#[ORM\Entity(repositoryClass: ParticipationRepository::class)]
#[ORM\Table(name: 'participation')]
class Participation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_part = null;

    public function getId_part(): ?int
    {
        return $this->id_part;
    }

    public function setId_part(int $id_part): self
    {
        $this->id_part = $id_part;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $event_id = null;

    public function getEvent_id(): ?int
    {
        return $this->event_id;
    }

    public function setEvent_id(int $event_id): self
    {
        $this->event_id = $event_id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $status = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $activities = null;

    public function getActivities(): ?string
    {
        return $this->activities;
    }

    public function setActivities(string $activities): self
    {
        $this->activities = $activities;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $part_date = null;

    public function getPart_date(): ?\DateTimeInterface
    {
        return $this->part_date;
    }

    public function setPart_date(\DateTimeInterface $part_date): self
    {
        $this->part_date = $part_date;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $user_id = null;

    public function getUser_id(): ?int
    {
        return $this->user_id;
    }

    public function setUser_id(int $user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getIdPart(): ?int
    {
        return $this->id_part;
    }

    public function getEventId(): ?int
    {
        return $this->event_id;
    }

    public function setEventId(int $event_id): static
    {
        $this->event_id = $event_id;

        return $this;
    }

    public function getPartDate(): ?\DateTimeInterface
    {
        return $this->part_date;
    }

    public function setPartDate(\DateTimeInterface $part_date): static
    {
        $this->part_date = $part_date;

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
