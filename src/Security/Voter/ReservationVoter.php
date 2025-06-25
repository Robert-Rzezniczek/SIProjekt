<?php

/**
 * Reservation Voter.
 */

namespace App\Security\Voter;

use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * ReservationVoter class.
 */
final class ReservationVoter extends Voter
{
    /**
     * Reservation approve permission.
     *
     * @const string
     */
    public const APPROVE = 'RESERVATION_APPROVE';

    /**
     * Reservation return permission.
     *
     * @const string
     */
    public const RETURN = 'RESERVATION_RETURN';

    /**
     * Reservation view permission.
     *
     * @const string
     */
    public const VIEW = 'RESERVATION_VIEW';

    /**
     * Reservation edit permission.
     *
     * @const string
     */
    public const EDIT = 'RESERVATION_EDIT';

    /**
     * Reservation manage permission.
     *
     * @const string
     */
    public const MANAGE = 'RESERVATION_MANAGE';

    /**
     * Reservation manage return permission.
     *
     * @const string
     */
    public const MANAGE_RETURN = 'RESERVATION_MANAGE_RETURN';

    /**
     * Reservation reject permission.
     *
     * @const string
     */
    public const REJECT = 'RESERVATION_REJECT';

    /**
     * Reservation rent permission.
     *
     * @const string
     */
    public const RENT = 'RESERVATION_RENT';

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
        return in_array($attribute, [self::APPROVE, self::RETURN, self::VIEW, self::EDIT, self::MANAGE, self::MANAGE_RETURN, self::REJECT, self::RENT])
            && $subject instanceof Reservation;
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
        if (!$user instanceof UserInterface) {
            return false;
        }
        if (!$subject instanceof Reservation) {
            return false;
        }

        return match ($attribute) {
            self::APPROVE => $this->canApprove($subject, $user),
            self::RETURN => $this->canReturn($subject, $user),
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::MANAGE => $this->canManage($subject, $user),
            self::MANAGE_RETURN => $this->canManageReturn($subject, $user),
            self::REJECT => $this->canReject($subject, $user),
            self::RENT => $this->canRent($subject, $user),
            default => false,
        };
    }

    /**
     * Checks if user can approve reservation.
     *
     * @param Reservation   $reservation Reservation entity
     * @param UserInterface $user        user
     *
     * @return bool result
     */
    private function canApprove(Reservation $reservation, UserInterface $user): bool
    {
        return $user instanceof User && in_array('ROLE_ADMIN', $user->getRoles())
            && 'pending' === $reservation->getStatus();
    }

    /**
     * Checks if user can return reservation.
     *
     * @param Reservation   $reservation Reservation entity
     * @param UserInterface $user        user
     *
     * @return bool result
     */
    private function canReturn(Reservation $reservation, UserInterface $user): bool
    {
        return $reservation->getUser() === $user
            && 'approved' === $reservation->getStatus()
            && null === $reservation->getReturnDate();
    }

    /**
     * Checks if user can view reservation.
     *
     * @param Reservation   $reservation Reservation entity
     * @param UserInterface $user        user
     *
     * @return bool result
     */
    private function canView(Reservation $reservation, UserInterface $user): bool
    {
        return $reservation->getUser() === $user
            || in_array('ROLE_ADMIN', $user->getRoles());
    }

    /**
     * Checks if user can edit reservation.
     *
     * @param Reservation   $reservation Reservation entity
     * @param UserInterface $user        user
     *
     * @return bool result
     */
    private function canEdit(Reservation $reservation, UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles())
            && 'returned' !== $reservation->getStatus();
    }

    /**
     * Checks if user can manage reservation.
     *
     * @param Reservation   $reservation Reservation entity
     * @param UserInterface $user        user
     *
     * @return bool result
     */
    private function canManage(Reservation $reservation, UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles())
            && 'pending' === $reservation->getStatus();
    }

    /**
     * Checks if user can manage return of a reservation.
     *
     * @param Reservation   $reservation Reservation entity
     * @param UserInterface $user        user
     *
     * @return bool result
     */
    private function canManageReturn(Reservation $reservation, UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles())
            && 'return_pending' === $reservation->getStatus();
    }

    /**
     * Checks if user can reject a reservation.
     *
     * @param Reservation   $reservation Reservation entity
     * @param UserInterface $user        user
     *
     * @return bool result
     */
    private function canReject(Reservation $reservation, UserInterface $user): bool
    {
        return $user instanceof User && in_array('ROLE_ADMIN', $user->getRoles())
            && 'pending' === $reservation->getStatus();
    }

    /**
     * Checks if user can rent.
     *
     * @param Reservation   $reservation Reservation entity
     * @param UserInterface $user        user
     *
     * @return bool result
     */
    private function canRent(Reservation $reservation, UserInterface $user): bool
    {
        return $user instanceof User && in_array('ROLE_USER', $user->getRoles())
            && $reservation->getItem()->getQuantity() > 0
            && 'pending' === $reservation->getStatus();
    }
}
