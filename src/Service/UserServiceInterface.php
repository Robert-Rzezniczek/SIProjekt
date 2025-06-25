<?php

/**
 * \App\Entity\User service interface.
 */

namespace App\Service;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface UserServiceInterface.
 */
interface UserServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface;

    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void;

    /**
     * Delete entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void;

    /**
     * Check if user has role.
     *
     * @param User            $user User entity
     * @param string|UserRole $role UserRole
     *
     * @return bool bool
     */
    public function hasRole(User $user, string|UserRole $role): bool;

    /**
     * Update user role with validation.
     *
     * @param User $user        User to update
     * @param User $currentUser Current logged-in user
     * @param bool $isAdmin     New admin status
     *
     * @return bool True on success, false if operation is not allowed
     */
    public function updateUserRole(User $user, User $currentUser, bool $isAdmin): bool;

    /**
     * Toggle block/unblock for user.
     *
     * @param User $user        User entity
     * @param User $currentUser Current User
     *
     * @return bool bool
     */
    public function toggleBlock(User $user, User $currentUser): bool;
}
