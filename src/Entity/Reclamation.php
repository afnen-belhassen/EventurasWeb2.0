<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReclamationRepository;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
#[ORM\Table(name: 'reclamations')]
class Reclamation
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

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $id_user = null;

    public function getId_user(): ?int
    {
        return $this->id_user;
    }

    public function setId_user(int $id_user): self
    {
        $this->id_user = $id_user;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Event::class)]
    #[ORM\JoinColumn(name: "id_event", referencedColumnName: "id_event", nullable: true, onDelete: "SET NULL")]
    private ?Event $id_event = null;

    public function getIdEvent(): ?Event
    {
        return $this->id_event;
    }
    
    public function setIdEvent(?Event $id_event): self
    {
        $this->id_event = $id_event;
    
        return $this;
    }



    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $created_at = null;

    public function getCreated_at(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }


    

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $subject = null;

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $refuse_reason = null;

    public function getRefuse_reason(): ?string
    {
        return $this->refuse_reason;
    }

    public function setRefuse_reason(?string $refuse_reason): self
    {
        $this->refuse_reason = $refuse_reason;
        return $this;
    }

    #[ORM\OneToMany(mappedBy: 'reclamation', targetEntity: ReclamationAttachment::class, cascade: ['remove'], orphanRemoval: true)]
    private Collection $reclamationAttachments;

    /**
     * @return Collection<int, ReclamationAttachment>
     */
    public function getReclamationAttachments(): Collection
    {
        if (!$this->reclamationAttachments instanceof Collection) {
            $this->reclamationAttachments = new ArrayCollection();
        }
        return $this->reclamationAttachments;
    }

    public function addReclamationAttachment(ReclamationAttachment $reclamationAttachment): self
    {
        if (!$this->getReclamationAttachments()->contains($reclamationAttachment)) {
            $this->getReclamationAttachments()->add($reclamationAttachment);
        }
        return $this;
    }

    public function removeReclamationAttachment(ReclamationAttachment $reclamationAttachment): self
    {
        $this->getReclamationAttachments()->removeElement($reclamationAttachment);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ReclamationConversation::class, mappedBy: 'reclamation')]
    private Collection $reclamationConversations;

    public function __construct()
    {
         // ← set creation timestamp to “now”
        $this->created_at = new \DateTime();
        $this->reclamationAttachments = new ArrayCollection();
        $this->reclamationConversations = new ArrayCollection();
        
    }

    /**
     * @return Collection<int, ReclamationConversation>
     */
    public function getReclamationConversations(): Collection
    {
        if (!$this->reclamationConversations instanceof Collection) {
            $this->reclamationConversations = new ArrayCollection();
        }
        return $this->reclamationConversations;
    }

    public function addReclamationConversation(ReclamationConversation $reclamationConversation): self
    {
        if (!$this->getReclamationConversations()->contains($reclamationConversation)) {
            $this->getReclamationConversations()->add($reclamationConversation);
        }
        return $this;
    }

    public function removeReclamationConversation(ReclamationConversation $reclamationConversation): self
    {
        $this->getReclamationConversations()->removeElement($reclamationConversation);
        return $this;
    }

    public function getIdUser(): ?int
    {
        return $this->id_user;
    }

    public function setIdUser(int $id_user): static
    {
        $this->id_user = $id_user;

        return $this;
    }




    public function getRefuseReason(): ?string
    {
        return $this->refuse_reason;
    }

    public function setRefuseReason(?string $refuse_reason): static
    {
        $this->refuse_reason = $refuse_reason;

        return $this;
    }


    #[ORM\Column(type: 'boolean')]
private bool $isRated = false;

#[ORM\Column(type: 'smallint', nullable: true)]
private ?int $rating = null;

public function isRated(): bool
{
    return $this->isRated;
}

public function setIsRated(bool $isRated): self
{
    $this->isRated = $isRated;
    return $this;
}

public function getRating(): ?int
{
    return $this->rating;
}

public function setRating(?int $rating): self
{
    $this->rating = $rating;
    return $this;
}


#[ORM\Column(type: 'datetime', nullable: true)]
private ?\DateTimeInterface $closed_at = null;

public function getClosedAt(): ?\DateTimeInterface
{
    return $this->closed_at;
}

public function setClosedAt(?\DateTimeInterface $closed_at): self
{
    $this->closed_at = $closed_at;

    return $this;
}


}
