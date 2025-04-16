<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Table(name: 'users')]
#[ORM\Index(columns: ['role_id'], name: 'fk_role_id')]
#[ORM\Entity(repositoryClass: 'App\Repository\UsersRepository')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'user_id', type: 'integer')]
    private ?int $userId = null;

    #[ORM\Column(name: 'user_username', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Username is required')]
    #[Assert\Length(
        min: 6,
        max: 50,
        minMessage: 'Username must be at least {{ limit }} characters long',
        maxMessage: 'Username cannot be longer than {{ limit }} characters'
    )]
    private string $userUsername;

    #[ORM\Column(name: 'user_email', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Email is required')]
    #[Assert\Email(
        message: 'The email "{{ value }}" is not a valid email address.'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
        message: 'The email format is invalid. Example: user@example.com'
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Email cannot be longer than {{ limit }} characters.'
    )]
    private string $userEmail;
    

    #[ORM\Column(name: 'user_password', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Password is required')]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: 'Password must be at least {{ limit }} characters long'
    )]
    private string $userPassword;

    #[ORM\Column(name: 'user_firstname', type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'First name is required')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'First name must be at least {{ limit }} character long',
        maxMessage: 'First name cannot be longer than {{ limit }} characters'
    )]
        private ?string $userFirstname = null;

    #[ORM\Column(name: 'user_lastname', type: 'string', length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Last name is required')]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: 'Last name must be at least {{ limit }} character long',
        maxMessage: 'Last name cannot be longer than {{ limit }} characters'
    )]
    
    private ?string $userLastname = null;

    #[ORM\Column(name: 'user_birthday', type: 'date', nullable: true)]
    #[Assert\NotBlank(message: 'Birthday is required')]
    #[Assert\Type('\DateTimeInterface', message: 'Birthday must be a valid date')]
    #[Assert\LessThan('-18 years', message: 'You must be at least 18 years old')]
    private ?\DateTimeInterface $userBirthday = null;

    #[ORM\Column(name: 'user_gender', type: 'string', length: 50, nullable: true)]
    #[Assert\NotBlank(message: 'Gender is required')]
    #[Assert\Choice(
        choices: ['male', 'female', 'other'],
        message: 'Choose a valid gender (male, female, or other)'
    )]
    private ?string $userGender = null;

    #[ORM\Column(name: 'user_picture', type: 'string', length: 255, nullable: true)]
    private ?string $userPicture = null;

    #[ORM\Column(name: 'user_phonenumber', type: 'string', length: 20, nullable: true)]
    #[Assert\NotBlank(message: 'Phone number is required')]
    #[Assert\Regex(
        pattern: '/^\+?[0-9]{8,8}$/',
        message: 'Phone number must be 8 digits and can start with +'
    )]
    private ?string $userPhonenumber = null;

    #[ORM\Column(name: 'user_level', type: 'integer', nullable: true, options: ['default' => 0])]
    #[Assert\NotBlank(message: 'Level is required')]
    #[Assert\Range(
        min: 0,
        max: 100,
        notInRangeMessage: 'Level must be between {{ min }} and {{ max }}'
    )]
    private int $userLevel = 0;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'role_id')]
    #[Assert\NotNull(message: 'Role is required')]
    private ?Role $role = null;
    #[ORM\Column(name: 'statut', type: 'integer')]
    private ?int $statut = null;
    
    public function __construct()
    {
        $this->statut = 1; // par défaut = 1
    }
    
    
public function getStatut(): int
{
    return $this->statut;
}

public function setStatut(int $Statut): self
{
    $this->statut = $Statut;
    return $this;
}
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getUserUsername(): ?string
    {
        return $this->userUsername;
    }

    public function setUserUsername(string $userUsername): static
    {
        $this->userUsername = $userUsername;
        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): static
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    public function getUserPassword(): ?string
    {
        return $this->userPassword;
    }

    public function setUserPassword(string $userPassword): static
    {
        $this->userPassword = $userPassword;
        return $this;
    }

    public function getUserFirstname(): ?string
    {
        return $this->userFirstname;
    }

    public function setUserFirstname(?string $userFirstname): static
    {
        $this->userFirstname = $userFirstname;
        return $this;
    }

    public function getUserLastname(): ?string
    {
        return $this->userLastname;
    }

    public function setUserLastname(?string $userLastname): static
    {
        $this->userLastname = $userLastname;
        return $this;
    }

    public function getUserBirthday(): ?\DateTimeInterface
    {
        return $this->userBirthday;
    }

    public function setUserBirthday(?\DateTimeInterface $userBirthday): static
    {
        $this->userBirthday = $userBirthday;
        return $this;
    }

    public function getUserGender(): ?string
    {
        return $this->userGender;
    }

    public function setUserGender(?string $userGender): static
    {
        $this->userGender = $userGender;
        return $this;
    }

    public function getUserPicture(): ?string
    {
        return $this->userPicture;
    }

    public function setUserPicture(?string $userPicture): static
    {
        $this->userPicture = $userPicture;
        return $this;
    }

    public function getUserPhonenumber(): ?string
    {
        return $this->userPhonenumber;
    }

    public function setUserPhonenumber(?string $userPhonenumber): static
    {
        $this->userPhonenumber = $userPhonenumber;
        return $this;
    }

    public function getUserLevel(): ?int
    {
        return $this->userLevel;
    }

    public function setUserLevel(?int $userLevel): static
    {
        $this->userLevel = $userLevel;
        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getRoles(): array
    {
        // Returns the roles dynamically based on the associated Role entity.
        return [ strtoupper($this->role->getRoleName())];
    }

    public function getPassword(): string
    {
        return $this->userPassword;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUserIdentifier(): string
    {
        return $this->userEmail;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function eraseCredentials(): void
    {
        // Pas de données sensibles pour le moment
    }
}
