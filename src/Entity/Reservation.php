<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReservationRepository;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservation')]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $Id = null;

    public function getId(): ?int
    {
        return $this->Id;
    }

    public function setId(int $Id): self
    {
        $this->Id = $Id;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $event_Id = null;

    public function getEvent_Id(): ?int
    {
        return $this->event_Id;
    }

    public function setEvent_Id(int $event_Id): self
    {
        $this->event_Id = $event_Id;
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $status = null;

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ticket_id = null;

    public function getTicket_id(): ?int
    {
        return $this->ticket_id;
    }

    public function setTicket_id(?int $ticket_id): self
    {
        $this->ticket_id = $ticket_id;
        return $this;
    }

    public function getEventId(): ?int
    {
        return $this->event_Id;
    }

    public function setEventId(int $event_Id): static
    {
        $this->event_Id = $event_Id;

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

    public function getTicketId(): ?int
    {
        return $this->ticket_id;
    }

    public function setTicketId(?int $ticket_id): static
    {
        $this->ticket_id = $ticket_id;

        return $this;
    }

}
