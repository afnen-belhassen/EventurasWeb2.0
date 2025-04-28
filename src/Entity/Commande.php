<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Produit;
use APP\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;


#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le produit est requis.")]
    private ?Produit $produit = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom du client est obligatoire")]
    private ?string $nom_client = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    private ?string $adresse = null;


    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le téléphone est obligatoire")]
    private ?string $telephone = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité est obligatoire")]
    #[Assert\PositiveOrZero(message: "La quantité doit être positive ou nulle")]
    private ?int $quantite = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $total = null;

    #[ORM\Column(type: "datetime")]
    #[Assert\NotBlank(message: "La date de commande est obligatoire")]
    private ?\DateTimeInterface $date_commande = null;



   

    public function __construct()
    {
        $this->date_commande = new \DateTimeImmutable();
       
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        return $this;
    }

    public function getNomClient(): ?string
    {
        return $this->nom_client;
    }

    public function setNomClient(?string $nom_client): self
    {
        $this->nom_client = $nom_client;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->date_commande;
    }

    public function setDateCommande(\DateTimeInterface $date): self
    {
        $this->date_commande = $date;
        return $this;
    }

    public function getTotal(): ?float { return $this->total; }
    public function setTotal(?float $t): self { // Allow null temporarily if needed, though your logic sets a float
        $this->total = $t;
        return $this;
    }

    public function getMontantTotal(): float
    {
        if ($this->produit === null || $this->quantite === null) {
            return 0.0; 
        }
        return $this->quantite * $this->produit->getPrix();
    }

    public function getCustomerEmail(Security $security): ?string
    {
        $user = $security->getUser();
    
        if ($user instanceof User) {
            return $user->getEmail();
        }
    
        return null;
    }
    

    


}
