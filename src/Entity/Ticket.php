<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\TicketRepository;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: 'ticket')]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $ticket_id = null;

    public function getTicket_id(): ?int
    {
        return $this->ticket_id;
    }

    public function setTicket_id(int $ticket_id): self
    {
        $this->ticket_id = $ticket_id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $ticket_code = null;

    public function getTicket_code(): ?string
    {
        return $this->ticket_code;
    }

    public function setTicket_code(string $ticket_code): self
    {
        $this->ticket_code = $ticket_code;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $seat_number = null;

    public function getSeat_number(): ?int
    {
        return $this->seat_number;
    }

    public function setSeat_number(int $seat_number): self
    {
        $this->seat_number = $seat_number;
        return $this;
    }

    public function getTicketId(): ?int
    {
        return $this->ticket_id;
    }

    public function getTicketCode(): ?string
    {
        return $this->ticket_code;
    }

    public function setTicketCode(string $ticket_code): static
    {
        $this->ticket_code = $ticket_code;

        return $this;
    }

    public function getSeatNumber(): ?string
    {
        return $this->seat_number;
    }

    public function setSeatNumber(string $seat_number): static
    {
        $this->seat_number = $seat_number;

        return $this;
    }

}
