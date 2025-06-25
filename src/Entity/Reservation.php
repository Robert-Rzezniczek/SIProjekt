<?php

/**
 * Reservation entity.
 */

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Reservation.
 */
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Item.
     */
    #[ORM\ManyToOne(targetEntity: Item::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Assert\Type(Item::class)]
    private ?Item $item = null;

    /**
     * User.
     */
    #[ORM\ManyToOne(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    #[Assert\Type(User::class)]
    private ?User $user = null;

    /**
     * Email.
     */
    #[ORM\Column(length: 150)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 150)]
    private ?string $email = null;

    /**
     * Nickname.
     */
    #[ORM\Column(length: 50, nullable:true)]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $nickname = null;

    /**
     * Comment.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $comment = null;

    /**
     * Status.
     */
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['pending', 'approved', 'rejected', 'return_pending', 'returned'])]
    private ?string $status = 'pending';

    /**
     * Created at.
     */
    #[ORM\Column]
    #[Assert\Type(\DateTimeImmutable::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Expiration date.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $expirationDate = null;

    /**
     * Loan date.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $loanDate = null;

    /**
     * Rating for a rented item.
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $tempRating = null;

    /**
     * Return date.
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Assert\Type(\DateTimeImmutable::class)]
    private ?\DateTimeImmutable $returnDate = null;

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
     * Getter for item.
     *
     * @return Item|null Item|null
     */
    public function getItem(): ?Item
    {
        return $this->item;
    }

    /**
     * Setter for item.
     *
     * @param Item|null $item Item|null
     *
     * @return $this this
     */
    public function setItem(?Item $item): static
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Getter for user.
     *
     * @return User|null User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Setter for user.
     *
     * @param User|null $user User|null
     *
     * @return $this this
     */
    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
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
     * @param string $email email string
     *
     * @return $this this
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

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
     * @param string $nickname nickname string
     *
     * @return $this this
     */
    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    /**
     * Getter for comment.
     *
     * @return string|null string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Setter for comment.
     *
     * @param string|null $comment commen string|null
     *
     * @return $this this
     */
    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Getter for status.
     *
     * @return string|null string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Setter for status.
     *
     * @param string $status status string
     *
     * @return $this this
     */
    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Getter for created at.
     *
     * @return \DateTimeImmutable|null DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Setter for created at.
     *
     * @param \DateTimeImmutable $createdAt created at
     *
     * @return $this this
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Getter for expiration date.
     *
     * @return \DateTimeImmutable|null DateTimeImmutable|null
     */
    public function getExpirationDate(): ?\DateTimeImmutable
    {
        return $this->expirationDate;
    }

    /**
     * Setter for expiration date.
     *
     * @param \DateTimeImmutable|null $expirationDate expiration date
     *
     * @return $this this
     */
    public function setExpirationDate(?\DateTimeImmutable $expirationDate): static
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Getter for loan date.
     *
     * @return \DateTimeImmutable|null DateTimeImmutable|null
     */
    public function getLoanDate(): ?\DateTimeImmutable
    {
        return $this->loanDate;
    }

    /**
     * Setter for loan date.
     *
     * @param \DateTimeImmutable|null $loanDate loan date
     *
     * @return $this this
     */
    public function setLoanDate(?\DateTimeImmutable $loanDate): static
    {
        $this->loanDate = $loanDate;

        return $this;
    }

    /**
     * Getter for temporary rating.
     *
     * @return int|null int|null
     */
    public function getTempRating(): ?int
    {
        return $this->tempRating;
    }

    /**
     * Getter for temporary rating.
     *
     * @param int|null $tempRating temporary rating
     *
     * @return $this this
     */
    public function setTempRating(?int $tempRating): static
    {
        $this->tempRating = $tempRating;

        return $this;
    }

    /**
     * Getter for return date.
     *
     * @return \DateTimeImmutable|null DateTimeImmutable|null
     */
    public function getReturnDate(): ?\DateTimeImmutable
    {
        return $this->returnDate;
    }

    /**
     * Setter for return date.
     *
     * @param \DateTimeImmutable|null $returnDate return date
     *
     * @return $this this
     */
    public function setReturnDate(?\DateTimeImmutable $returnDate): static
    {
        $this->returnDate = $returnDate;

        return $this;
    }
}
