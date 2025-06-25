<?php

/**
 * User checker.
 */

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * UserChecker class.
 */
class UserChecker implements UserCheckerInterface
{
    /**
     * Checker pre Authorization (checks if user is blocked).
     *
     * @param UserInterface $user UserInterface
     *
     * @return void void
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isBlocked()) {
            throw new CustomUserMessageAccountStatusException('message.blocked');
        }
    }

    /**
     * Checker post Authorization.
     *
     * @param UserInterface $user UserInterface
     *
     * @return void void
     */
    public function checkPostAuth(UserInterface $user): void
    {
    }
}
