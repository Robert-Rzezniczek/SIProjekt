<?php

/**
 * \App\Entity\User service.
 */

namespace App\Service;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use App\Form\RegistrationForm;
use App\Form\Type\ChangePasswordType;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class UserService.
 */
class UserService implements UserServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param UserRepository              $userRepository UserRepository
     * @param PaginatorInterface          $paginator      PaginatorInterface
     * @param UserPasswordHasherInterface $passwordHasher UserPasswordHasherInterface
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly PaginatorInterface $paginator, private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedList(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->userRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['user.id', 'user.email', 'user.roles', 'user.blocked'],
                'defaultSortFieldName' => 'user.id',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void
    {
        $this->userRepository->save($user);
    }

    /**
     * Delete entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void
    {
        $this->userRepository->delete($user);
    }

    /**
     * Check if user has role.
     *
     * @param User            $user User entity
     * @param string|UserRole $role UserRole
     *
     * @return bool bool
     */
    public function hasRole(User $user, string|UserRole $role): bool
    {
        $roleValue = is_string($role) ? $role : $role->value;

        return in_array($roleValue, $user->getRoles());
    }

    /**
     * Update user role with validation.
     *
     * @param User $user        User to update
     * @param User $currentUser Current logged-in user
     * @param bool $isAdmin     New admin status
     *
     * @return bool True on success, false if operation is not allowed
     */
    public function updateUserRole(User $user, User $currentUser, bool $isAdmin): bool
    {
        if (!in_array(UserRole::ROLE_ADMIN->value, $currentUser->getRoles())) {
            return false;
        }
        if ($currentUser->getId() === $user->getId()) {
            return false;
        }
        $roles = $user->getRoles();
        if ($isAdmin) {
            if (!in_array(UserRole::ROLE_ADMIN->value, $roles)) {
                $roles[] = UserRole::ROLE_ADMIN->value;
            }
        } else {
            $roles = array_diff($roles, [UserRole::ROLE_ADMIN->value]);
        }
        $user->setRoles(array_values(array_unique($roles)));

        $this->save($user);

        return true;
    }

    /**
     * Toggle block/unblock for user.
     *
     * @param User $user        User entity
     * @param User $currentUser Current User
     *
     * @return bool bool
     */
    public function toggleBlock(User $user, User $currentUser): bool
    {
        if ($user === $currentUser) {
            return false;
        }

        $user->setBlocked(!$user->isBlocked());
        $this->save($user);

        return true;
    }

    /**
     * Change user password using ChangePasswordType form data.
     *
     * @param User                              $user User entity
     * @param FormInterface<ChangePasswordType> $form Form instance of ChangePasswordType
     */
    public function changePasswordFromForm(User $user, FormInterface $form): void
    {
        $plainPassword = $form->get('plainPassword')->getData();
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $this->save($user);
    }

    /**
     * Register a new user using RegistrationForm data.
     *
     * @param User                            $user User entity
     * @param FormInterface<RegistrationForm> $form Form instance of RegistrationForm
     */
    public function registerUser(User $user, FormInterface $form): void
    {
        if (null === $user->isBlocked()) {
            $user->setBlocked(false);
        }
        $nickname = $form->get('nickname')->getData();
        if (null !== $nickname && '' !== $nickname) {
            $user->setNickname($nickname);
        } else {
            $user->setNickname(null);
        }
        $user->setRoles([UserRole::ROLE_USER->value]);
        $plainPassword = $form->get('plainPassword')->getData();
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));
        $this->save($user);
    }
}
