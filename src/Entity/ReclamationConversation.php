<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReclamationConversationRepository;

#[ORM\Entity(repositoryClass: ReclamationConversationRepository::class)]
#[ORM\Table(name: 'reclamation_conversations')]
class ReclamationConversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Reclamation::class, inversedBy: 'reclamationConversations')]
    #[ORM\JoinColumn(name: 'reclamation_id', referencedColumnName: 'id')]
    private ?Reclamation $reclamation = null;

    public function getReclamation(): ?Reclamation
    {
        return $this->reclamation;
    }

    public function setReclamation(?Reclamation $reclamation): self
    {
        $this->reclamation = $reclamation;
        return $this;
    }

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $created_at = null;

    public function getCreated_at(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'active'])]
    private string $status = 'active';

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->created_at = $createdAt;
        return $this;
    }
    
    #[ORM\OneToMany(
        mappedBy: 'conversation',
        targetEntity: ConversationMessage::class,
        cascade: ['persist','remove']
    )]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(ConversationMessage $msg): static
    {
        if (!$this->messages->contains($msg)) {
            $this->messages->add($msg);
            $msg->setConversation($this);
        }
        return $this;
    }

    public function removeMessage(ConversationMessage $msg): static
    {
        if ($this->messages->removeElement($msg) && $msg->getConversation() === $this) {
            $msg->setConversation(null);
        }
        return $this;
    }

}
