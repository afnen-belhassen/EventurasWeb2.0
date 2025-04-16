<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'role')]
#[ORM\Entity(repositoryClass: 'App\Repository\RoleRepository')]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'role_id', type: 'integer')]
    private ?int $roleId = null;

    #[ORM\Column(name: 'role_name', type: 'string', length: 50, nullable: true)]
    #[Assert\NotBlank(message: 'Role name is required')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Role name must be at least {{ limit }} characters long',
        maxMessage: 'Role name cannot be longer than {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z_]+$/',
        message: 'Role name can only contain letters and underscores'
    )]
    private ?string $roleName = null;

    public function getRoleId(): ?int
    {
        return $this->roleId;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function setRoleName(?string $roleName): static
    {
        $this->roleName = $roleName;
        return $this;
    }

    public function __toString(): string
    {
        return $this->roleName ?? 'Unknown Role';
    }
}