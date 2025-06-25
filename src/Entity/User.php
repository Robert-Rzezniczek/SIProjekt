<?php

/**
 * User entity.
 */

namespace App\Entity;

use App\Entity\Enum\UserRole;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class User.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'message.this_email_already_exists')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Email.
     */
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    /**
     * Roles.
     *
     * @var list<int, string>
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * Hashed password.
     */
    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    /**
     * Blocked.
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $blocked = null;

    /**
     * Nickname.
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $nickname = null;

    /**
     * Getter for id.
     *
     * @return int|null int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Getter for email.
     *
     * @return string|null string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Setter for email.
     *
     * @param string $email email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Getter for user identifier.
     *
     * @return string string
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Getter for roles.
     *
     * @return array|string[] array|string[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = UserRole::ROLE_USER->value;

        return array_unique($roles);
    }

    /**
     * Setter for roles.
     *
     * @param array $roles roles array
     *
     * @return void void
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * Getter for password.
     *
     * @return string|null string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Setter for password.
     *
     * @param string $password password string
     *
     * @return void void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Removes sensitive information from the token.
     *
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * Checker for if user is blocked.
     *
     * @return bool|null bool|null
     */
    public function isBlocked(): ?bool
    {
        return $this->blocked;
    }

    /**
     * Setter for blocked.
     *
     * @param bool $blocked blocked
     *
     * @return $this this
     */
    public function setBlocked(bool $blocked): static
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * Getter for nickname.
     *
     * @return string|null string|null
     */
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * Setter for nickname.
     *
     * @param string|null $nickname nickname
     *
     * @return $this this
     */
    public function setNickname(?string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }
}
