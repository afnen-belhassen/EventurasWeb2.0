<?php

namespace App\Entity;

use App\Repository\PartnershipRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartnershipRepository::class)]
#[ORM\Table(name: 'partnership')]
class Partnership
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $organizerid = null;

    #[ORM\ManyToOne(inversedBy: 'partnerships')]
    #[ORM\JoinColumn(name: 'partner_id', referencedColumnName: 'id')]
    private ?Partner $partnerId = null;

    #[ORM\Column(length: 255)]
    private ?string $contracttype = null;

    #[ORM\Column(type: 'text')]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isSigned = false;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $status = 'pending';

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $signedContractFile = null;
    
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $signedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganizerid(): ?int
    {
        return $this->organizerid;
    }

    public function setOrganizerid(?int $organizerid): static
    {
        $this->organizerid = $organizerid;
        return $this;
    }

    public function getPartnerId(): ?Partner
    {
        return $this->partnerId;
    }

    public function setPartnerId(?Partner $partnerId): static
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    public function getContracttype(): ?string
    {
        return $this->contracttype;
    }

    public function setContracttype(string $contracttype): static
    {
        $this->contracttype = $contracttype;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function isSigned(): ?bool
    {
        return $this->isSigned;
    }

    public function setIsSigned(bool $isSigned): static
    {
        $this->isSigned = $isSigned;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    
    public function getSignedContractFile(): ?string
    {
        return $this->signedContractFile;
    }
    
    public function setSignedContractFile(?string $signedContractFile): static
    {
        $this->signedContractFile = $signedContractFile;
        return $this;
    }
    
    public function getSignedAt(): ?\DateTimeInterface
    {
        return $this->signedAt;
    }
    
    public function setSignedAt(?\DateTimeInterface $signedAt): static
    {
        $this->signedAt = $signedAt;
        return $this;
    }
    
    /**
     * Get the name of the partner associated with this partnership
     */
    public function getPartnerName(): ?string
    {
        return $this->partnerId ? $this->partnerId->getName() : null;
    }
    
    /**
     * Get the type of the partnership
     */
    public function getType(): ?string
    {
        return $this->contracttype;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;
        return $this;
    }
} 