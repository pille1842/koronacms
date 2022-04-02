<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    attributes: ["security" => "is_granted('ROLE_USER')"],
    normalizationContext: ['groups' => ['user:output']],
    denormalizationContext: ['groups' => ['user:input']],
    collectionOperations: [
        "get",
        "post" => ["security" => "is_granted('ROLE_EDIT_USERS')"],
    ],
    itemOperations: [
        "get",
        "put" => ["security" => "is_granted('ROLE_EDIT_USERS') or object == user"],
        "patch" => ["security" => "is_granted('ROLE_EDIT_USERS') or object == user"],
    ],
)]
#[UniqueEntity('username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLES = [
        'ROLE_EDIT_USERS',
        'ROLE_USER',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["user:output"])]
    #[Assert\Positive()]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(["user:output", "user:input"])]
    #[Assert\NotBlank]
    private $username;

    #[ORM\Column(type: 'json')]
    #[Groups(["user:output", "admin:input"])]
    #[Assert\Type('array')]
    #[Assert\Choice(
        choices: self::ROLES,
        multiple: true,
    )]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[Groups(["user:input"])]
    #[Assert\NotCompromisedPassword()]
    private $plainPassword;

    #[ORM\Column(type: 'boolean')]
    #[Groups(["user:output", "admin:input"])]
    private $isEnabled = false;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(["user:output"])]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(["user:output"])]
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): self
    {
        $this->plainPassword = $password;

        // Temporarily change the password field so the PreUpdate listener gets called
        $this->password = null;

        return $this;
    }


    public function getIsEnabled(): ?bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
