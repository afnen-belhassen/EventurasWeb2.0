<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EventRepository;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'event')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_event = null;
    public function __construct()
    {
        $this->ratings = new ArrayCollection();
    }
    
    public function getId_event(): ?int
    {
        return $this->id_event;
    }

    public function setId_event(int $id_event): self
    {
        $this->id_event = $id_event;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'Oups ! Tu as oublié le titre. C’est la star de ton contenu !')]
    #[Assert\Length(min: 10, minMessage: 'Le titre doit contenir au moins 10 caractères.')]
    private ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\NotBlank(message: 'Dis-nous ce qui rend cet événement si spécial 😊')]
    #[Assert\Length(min:10,minMessage: 'La description doit contenir au moins 25 caractères')]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual("today", message: "On adore les événements… mais pas encore ceux du passé !")]
    #[Assert\NotBlank(message: 'Vous devez fournir une date😊')]
    
private ?\DateTimeInterface $date_event = null;

public function getDate_event(): ?\DateTimeInterface
{
    return $this->date_event;
}

public function setDate_event(?\DateTimeInterface $date_event): self
{
    $this->date_event = $date_event;
    return $this;
}


    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Ajoute un lieu pour qu’on puisse te rejoindre ')]
    private ?string $location = null;
    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[Assert\NotNull(message: ' C’est quel genre d’événement ? Aide-nous à le classer !')]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'category_id', nullable: false)]
    private ?Categorie $category = null;

    public function getCategory(): ?Categorie
    {
        return $this->category;
    }

    public function setCategory(?Categorie $category): self
    {
        $this->category = $category;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $creation_date = null;

    public function getCreation_date(): ?\DateTimeInterface
    {
        return $this->creation_date;
    }

    public function setCreation_date(\DateTimeInterface $creation_date): self
    {
        $this->creation_date = $creation_date;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $image = null;

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $activities = null;

    public function getActivities(): ?string
    {
        return $this->activities;
    }

    public function setActivities(?string $activities): self
    {
        $this->activities = $activities;
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

    #[ORM\Column(type: 'decimal', nullable: false)]
    #[Assert\NotNull(message: 'Vous devez fournir le prix')]
    #[Assert\GreaterThanOrEqual(value: 0,message: 'Oups ! Le prix doit être positif ')]
    private ?float $prix = null;

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotNull(message: 'Le nombre de places est requis.')]
    #[Assert\GreaterThanOrEqual(value: 10, message: 'Prévois un peu plus de monde ! Minimum 10 participants, c’est la règle ')]
    private ?int $nb_places = null;
  

    public function getNb_places(): ?int
    {
        return $this->nb_places;
    }

    public function setNb_places(int $nb_places): self
    {
        $this->nb_places = $nb_places;
        return $this;
    }

    public function getIdEvent(): ?int
    {
        return $this->id_event;
    }

    public function getDateEvent(): ?\DateTimeInterface
    {
        return $this->date_event;
    }

    public function setDateEvent(?\DateTimeInterface $date_event): static
    {
        $this->date_event = $date_event;

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creation_date;
    }

    public function setCreationDate(\DateTimeInterface $creation_date): static
    {
        $this->creation_date = $creation_date;

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

    public function getNbPlaces(): ?int
    {
        return $this->nb_places;
    }

    public function setNbPlaces(int $nb_places): static
    {
        $this->nb_places = $nb_places;

        return $this;
    }
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Assert\NotBlank(message: 'La date de fin de l\'événement est requise.')]
    #[Assert\GreaterThanOrEqual(propertyPath: "date_event", message: 'La date de fin de l\'événement doit être après la date de début.')]
    private ?\DateTimeInterface $date_fin_eve = null;

        public function getDateFinEve(): ?\DateTimeInterface
        {
            return $this->date_fin_eve;
        }

        public function setDateFinEve(?\DateTimeInterface $date_fin_eve): static
        {
            $this->date_fin_eve = $date_fin_eve;

            return $this;
        }
        #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'event')]
        private Collection $ratings;
        public function getRatings(): Collection
        {
        return $this->ratings;
        }
        public function getAverageRating(): float
        {
            $ratings = $this->ratings; // Assuming you have a relation with Rating
            if (count($ratings) === 0) {
                return 0;
            }

            $sum = 0;
            foreach ($ratings as $rating) {
                $sum += $rating->getValue();
            }

            return $sum / count($ratings); // Return the average rating
        }
        #[ORM\Column(type: 'float', nullable: false)]
        private ?float $latitude = null;
        #[ORM\Column(type: 'float', nullable: false)]
        private ?float $longitude = null;
        public function getLatitude(): ?int
        {
            return $this->latitude;
        }

        public function setLatitude(int $latitude): static
        {
            $this->latitude = $latitude;

            return $this;
        }
        public function getLongitude(): ?int
        {
            return $this->longitude;
        }

        public function setLongitude(int $longitude): static
        {
            $this->longitude = $longitude;

            return $this;
        }
}