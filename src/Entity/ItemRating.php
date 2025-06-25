<?php

/**
 * ItemRating entity.
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ItemRating.
 */
#[ORM\Entity]
#[ORM\Table(name: 'item_ratings')]
class ItemRating
{
    /**
     * Primary key.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Item.
     */
    #[ORM\ManyToOne(targetEntity: Item::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $item = null;

    /**
     * User.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * Rating value (e.g., 1-5).
     */
    #[ORM\Column(type: 'smallint')]
    private ?int $value = null;

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
    public function setItem(?Item $item): self
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
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Getter for value.
     *
     * @return int|null int|null
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * Setter for value.
     *
     * @param int $value value int
     *
     * @return $this this
     */
    public function setValue(int $value): self
    {
        if ($value < 1 || $value > 5) {
            throw new \InvalidArgumentException('message.rating.value.invalid');
        }
        $this->value = $value;

        return $this;
    }
}
