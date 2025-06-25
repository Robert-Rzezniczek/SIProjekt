<?php

/**
 * \App\Entity\Reservation service interface.
 */

namespace App\Service;

use App\Entity\Item;
use App\Entity\Reservation;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Interface ReservationServiceInterface.
 */
interface ReservationServiceInterface
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
     * Get paginated list for User.
     *
     * @param int           $page Page number
     * @param UserInterface $user User
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListForUser(int $page, UserInterface $user): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Reservation $reservation Reservation entity
     */
    public function save(Reservation $reservation): void;

    /**
     * Delete entity.
     *
     * @param Reservation $reservation Reservation entity
     */
    public function delete(Reservation $reservation): void;

    /**
     * Get decision for reservation.
     *
     * @param Reservation $reservation Reservation entity
     * @param string      $decision    decision string
     *
     * @return bool bool
     */
    public function processReservationDecision(Reservation $reservation, string $decision): bool;

    /**
     * Create reservation request.
     *
     * @param Reservation $reservation Reservation entity
     * @param Item        $item        Item entity
     */
    public function createReservation(Reservation $reservation, Item $item): bool;

    /**
     * Initialize a new reservation with item and email.
     *
     * @param int           $itemId Item ID
     * @param UserInterface $user   Current logged-in user
     *
     * @return Reservation|null Initialized reservation or null if item not found
     */
    public function initializeReservation(int $itemId, UserInterface $user): ?Reservation;

    /**
     * Rent item for logged user.
     *
     * @param FormInterface $form   Form instance of RentType
     * @param int           $itemId Item ID
     * @param UserInterface $user   Current logged-in user
     *
     * @return bool True on success, false otherwise
     */
    public function rentItem(FormInterface $form, int $itemId, UserInterface $user): bool;

    /**
     * Initiate return request for a reservation.
     *
     * @param FormInterface $form          Form instance of ReturnType
     * @param int           $reservationId Reservation ID
     * @param UserInterface $user          Current logged-in user
     *
     * @return bool True on success, false otherwise
     */
    public function returnItem(FormInterface $form, int $reservationId, UserInterface $user): bool;

    /**
     * Process return decision for a reservation.
     *
     * @param Reservation $reservation Reservation entity
     * @param string      $decision    Decision (e.g., 'returned', 'rejected')
     */
    public function processReturnDecision(Reservation $reservation, string $decision): bool;

    /**
     * Get paginated list of overdue reservations.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedOverdueReservations(int $page): PaginationInterface;

    /**
     * Check if user has overdue reservations.
     *
     * @param UserInterface $user User entity
     *
     * @return bool True if user has overdue reservations
     */
    public function hasOverdueReservations(UserInterface $user): bool;
}
