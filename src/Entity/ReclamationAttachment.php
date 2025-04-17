<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ReclamationAttachmentRepository;

#[ORM\Entity(repositoryClass: ReclamationAttachmentRepository::class)]
#[ORM\Table(name: 'reclamation_attachments')]
class ReclamationAttachment
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

    #[ORM\ManyToOne(targetEntity: Reclamation::class, inversedBy: 'reclamationAttachments')]
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

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $file_path = null;

    public function getFile_path(): ?string
    {
        return $this->file_path;
    }

    public function setFile_path(string $file_path): self
    {
        $this->file_path = $file_path;
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->file_path;
    }

    public function setFilePath(string $file_path): static
    {
        $this->file_path = $file_path;

        return $this;
    }

}
