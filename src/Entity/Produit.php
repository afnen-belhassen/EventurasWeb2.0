<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\Table(name: "produits")]


class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire.")]
    #[Assert\Length(min: 2, max: 100, minMessage: "Nom trop court.", maxMessage: "Nom trop long.")]
    private ?string $nom = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    private ?string $description = null;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    #[Assert\Positive(message: "Le prix doit être positif.")]
    private ?float $prix = null;

    #[ORM\Column(type: "integer")]
    #[Assert\NotNull(message: "La quantité est obligatoire.")]
    #[Assert\PositiveOrZero(message: "La quantité doit être positive ou nulle.")]
    private ?int $quantite = null;

    #[ORM\Column(name: 'image', type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }



}
