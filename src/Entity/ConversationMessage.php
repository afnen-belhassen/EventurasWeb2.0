<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ConversationMessageRepository;

#[ORM\Entity(repositoryClass: ConversationMessageRepository::class)]
#[ORM\Table(name: 'conversation_messages')]
class ConversationMessage
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
    private ?int $conversation_id = null;

    public function getConversation_id(): ?int
    {
        return $this->conversation_id;
    }

    public function setConversation_id(int $conversation_id): self
    {
        $this->conversation_id = $conversation_id;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $sender_id = null;

    public function getSender_id(): ?int
    {
        return $this->sender_id;
    }

    public function setSender_id(int $sender_id): self
    {
        $this->sender_id = $sender_id;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $message = null;

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
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

    #[ORM\OneToMany(targetEntity: MessageAttachment::class, mappedBy: 'conversationMessage')]
    private Collection $messageAttachments;

    public function __construct()
    {
        $this->messageAttachments = new ArrayCollection();
    }

    /**
     * @return Collection<int, MessageAttachment>
     */
    public function getMessageAttachments(): Collection
    {
        if (!$this->messageAttachments instanceof Collection) {
            $this->messageAttachments = new ArrayCollection();
        }
        return $this->messageAttachments;
    }

    public function addMessageAttachment(MessageAttachment $messageAttachment): self
    {
        if (!$this->getMessageAttachments()->contains($messageAttachment)) {
            $this->getMessageAttachments()->add($messageAttachment);
        }
        return $this;
    }

    public function removeMessageAttachment(MessageAttachment $messageAttachment): self
    {
        $this->getMessageAttachments()->removeElement($messageAttachment);
        return $this;
    }

    public function getConversationId(): ?int
    {
        return $this->conversation_id;
    }

    public function setConversationId(int $conversation_id): static
    {
        $this->conversation_id = $conversation_id;

        return $this;
    }

    public function getSenderId(): ?int
    {
        return $this->sender_id;
    }

    public function setSenderId(int $sender_id): static
    {
        $this->sender_id = $sender_id;

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

    #[ORM\ManyToOne(
        targetEntity: ReclamationConversation::class,
        inversedBy: 'messages'
    )]
    #[ORM\JoinColumn(nullable: false)]
    private ?ReclamationConversation $conversation = null;

    public function getConversation(): ?ReclamationConversation
    {
        return $this->conversation;
    }

    public function setConversation(?ReclamationConversation $conv): static
    {
        $this->conversation = $conv;
        return $this;
    }
    

}
