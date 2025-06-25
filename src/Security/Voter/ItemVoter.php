<?php

/**
 * Item Voter.
 */

namespace App\Security\Voter;

use App\Entity\Item;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * ItemVoter class.
 */
final class ItemVoter extends Voter
{
    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'ITEM_VIEW';

    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'ITEM_EDIT';

    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'ITEM_DELETE';

    /**
     * Rent permission.
     *
     * @const string
     */
    public const RENT = 'ITEM_RENT';

    /**
     * Reserve permission.
     *
     * @const string
     */
    public const RESERVE = 'ITEM_RESERVE';

    /**
     * Create permission.
     *
     * @const string
     */
    public const CREATE = 'ITEM_CREATE';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::RENT, self::RESERVE, self::CREATE])
            && ($subject instanceof Item || self::CREATE === $attribute);
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (self::CREATE === $attribute && null === $subject) {
            return $this->canCreate($user);
        }
        if (!$subject instanceof Item) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::RENT => $this->canRent($subject, $user),
            self::RESERVE => $this->canReserve($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can view item.
     *
     * @param Item  $item Item entity
     * @param mixed $user user
     *
     * @return bool result
     */
    private function canView(Item $item, mixed $user): bool
    {
        return true;
    }

    /**
     * Checks if user can edit item.
     *
     * @param Item  $item Item entity
     * @param mixed $user user
     *
     * @return bool result
     */
    private function canEdit(Item $item, mixed $user): bool
    {
        return $user instanceof User && in_array('ROLE_ADMIN', $user->getRoles());
    }

    /**
     * Checks if user can delete item.
     *
     * @param Item  $item Item entity
     * @param mixed $user user
     *
     * @return bool result
     */
    private function canDelete(Item $item, mixed $user): bool
    {
        return $user instanceof User && in_array('ROLE_ADMIN', $user->getRoles());
    }

    /**
     * Checks if user can rent item.
     *
     * @param Item  $item Item entity
     * @param mixed $user user
     *
     * @return bool result
     */
    private function canRent(Item $item, mixed $user): bool
    {
        return $user instanceof User && in_array('ROLE_USER', $user->getRoles()) && $item->getQuantity() > 0;
    }

    /**
     * Checks if user can reserve item.
     *
     * @param Item  $item Item entity
     * @param mixed $user user
     *
     * @return bool result
     */
    private function canReserve(Item $item, mixed $user): bool
    {
        return !$user instanceof User && $item->getQuantity() > 0;
    }

    /**
     * Checks if user can create item.
     *
     * @param mixed $user user
     *
     * @return bool result
     */
    private function canCreate(mixed $user): bool
    {
        return $user instanceof User && in_array('ROLE_ADMIN', $user->getRoles());
    }
}
