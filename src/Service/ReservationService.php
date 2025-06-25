<?php

/**
 * Reservation service.
 */

namespace App\Service;

use App\Entity\Item;
use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\ReservationRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ReservationService.
 */
class ReservationService implements ReservationServiceInterface
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
    private const LOAN_PERIOD_DAYS = 7;

    /**
     * Constructor.
     *
     * @param ReservationRepository $reservationRepository ReservationRepository
     * @param ItemRepository        $itemRepository        ItemRepository
     * @param PaginatorInterface    $paginator             PaginatorInterface
     * @param ItemRatingService     $itemRatingService     ItemRatingService
     * @param ItemServiceInterface  $itemService           ItemService
     */
    public function __construct(private readonly ReservationRepository $reservationRepository, private readonly ItemRepository $itemRepository, private readonly PaginatorInterface $paginator, private readonly ItemRatingService $itemRatingService, private readonly ItemServiceInterface $itemService)
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
            $this->reservationRepository->queryAll(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['reservation.id', 'reservation.createdAt', 'reservation.loanDate', 'reservation.status', 'reservation.email', 'reservation.firstName', 'reservation.expirationDate', 'item.title', 'reservation.nickname'],
                'defaultSortFieldName' => 'reservation.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Get paginated list for User.
     *
     * @param int           $page Page number
     * @param UserInterface $user User
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedListForUser(int $page, UserInterface $user): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->reservationRepository->queryAllByUser($user),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['reservation.createdAt', 'reservation.status'],
                'defaultSortFieldName' => 'reservation.createdAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save entity.
     *
     * @param Reservation $reservation Reservation entity
     */
    public function save(Reservation $reservation): void
    {
        $this->reservationRepository->save($reservation);
    }

    /**
     * Delete entity.
     *
     * @param Reservation $reservation Reservation entity
     */
    public function delete(Reservation $reservation): void
    {
        $this->reservationRepository->delete($reservation);
    }

    /**
     * Get decision for reservation.
     *
     * @param Reservation $reservation Reservation entity
     * @param string      $decision    decision string
     *
     * @return bool bool
     */
    public function processReservationDecision(Reservation $reservation, string $decision): bool
    {
        if (!in_array($decision, ['approved', 'rejected'])) {
            return false;
        }
        if ('pending' !== $reservation->getStatus()) {
            return false;
        }

        $reservation->setStatus($decision);
        if ('approved' === $decision) {
            $reservation->setLoanDate(new \DateTimeImmutable());
            $reservation->setExpirationDate((new \DateTimeImmutable())->modify('+'.self::LOAN_PERIOD_DAYS.' days'));
            $item = $reservation->getItem();
            if ($item && $item->getQuantity() > 0) {
                $item->setQuantity($item->getQuantity() - 1);
            } else {
                return false;
            }
        }
        $this->reservationRepository->save($reservation);

        return true;
    }

    /**
     * Create reservation request.
     *
     * @param Reservation $reservation Reservation entity
     * @param Item        $item        Item entity
     *
     * @return bool bool
     */
    public function createReservation(Reservation $reservation, Item $item): bool
    {
        if ($item->getQuantity() <= 0) {
            return false;
        }

        $reservation->setItem($item);
        $reservation->setStatus('pending');

        $this->reservationRepository->save($reservation);

        return true;
    }

    /**
     * Initialize a new reservation with item and email.
     *
     * @param int           $itemId Item ID
     * @param UserInterface $user   Current logged-in user
     *
     * @return Reservation|null Initialized reservation or null if item not found
     */
    public function initializeReservation(int $itemId, UserInterface $user): ?Reservation
    {
        $item = $this->itemRepository->find($itemId);
        if (!$item) {
            return null;
        }

        $reservation = new Reservation();
        $reservation->setItem($item);
        if ($user instanceof User) {
            $reservation->setEmail($user->getEmail() ?? '');
        } else {
            $reservation->setEmail('');
        }

        return $reservation;
    }

    /**
     * Rent item for logged user.
     *
     * @param FormInterface $form   Form instance of RentType
     * @param int           $itemId Item ID
     * @param UserInterface $user   Current logged-in user
     *
     * @return bool True on success, false otherwise
     */
    public function rentItem(FormInterface $form, int $itemId, UserInterface $user): bool
    {
        $item = $this->itemRepository->find($itemId);
        if (!$item || $item->getQuantity() <= 0) {
            return false;
        }
        $reservation = $form->getData();
        $reservation->setUser($user);
        if ($user instanceof User) {
            $reservation->setNickname($user->getNickname() ?? '');
        } else {
            $reservation->setNickname('');
        }
        $reservation->setComment($form->get('comment')->getData() ?? '');

        $reservation->setStatus('approved');
        $reservation->setLoanDate(new \DateTimeImmutable());
        $reservation->setExpirationDate((new \DateTimeImmutable())->modify('+'.self::LOAN_PERIOD_DAYS.' days'));

        $item->setQuantity($item->getQuantity() - 1);
        $this->itemRepository->save($item);
        $this->reservationRepository->save($reservation);

        return true;
    }

    /**
     * Initialize a reservation for return with validation.
     *
     * @param int           $reservationId Reservation ID
     * @param UserInterface $user          Current logged-in user
     *
     * @return Reservation|null Initialized reservation or null if not valid
     */
    public function initializeReturnReservation(int $reservationId, UserInterface $user): ?Reservation
    {
        $reservation = $this->reservationRepository->find($reservationId);
        if (!$reservation || $reservation->getUser() !== $user || 'approved' !== $reservation->getStatus()) {
            return null;
        }

        return $reservation;
    }

    /**
     * Initiate return request for a reservation.
     *
     * @param FormInterface $form          Form instance of ReturnType
     * @param int           $reservationId Reservation ID
     * @param UserInterface $user          Current logged-in user
     *
     * @return bool True on success, false otherwise
     */
    public function returnItem(FormInterface $form, int $reservationId, UserInterface $user): bool
    {
        $ratingData = $form->get('tempRating')->getData();
        $reservation = $form->getData();
        $reservation->setStatus('return_pending');
        $reservation->setTempRating($ratingData);
        $this->reservationRepository->save($reservation);

        return true;
    }

    /**
     * Process return decision for a reservation.
     *
     * @param Reservation $reservation Reservation entity
     * @param string      $decision    decision
     *
     * @return bool bool
     */
    public function processReturnDecision(Reservation $reservation, string $decision): bool
    {
        if ('returned' !== $decision) {
            return false;
        }
        if ('return_pending' !== $reservation->getStatus()) {
            return false;
        }

        $reservation->setStatus($decision);
        $item = $reservation->getItem();
        if ($item) {
            $item->setQuantity($item->getQuantity() + 1);
            $this->itemRepository->save($item);
        }
        $reservation->setReturnDate(new \DateTimeImmutable());
        $tempRating = $reservation->getTempRating();
        if (null !== $tempRating && $tempRating >= 1 && $tempRating <= 5) {
            $user = $reservation->getUser();
            $this->itemRatingService->addRating($item, $user, $tempRating);
            $this->itemService->updateRatingAverage($item);
            $reservation->setTempRating(null);
        }

        $this->reservationRepository->save($reservation);

        return true;
    }

    /**
     * Get paginated list of overdue reservations.
     *
     * @param int $page Page number
     *
     * @return PaginationInterface Paginated list
     */
    public function getPaginatedOverdueReservations(int $page): PaginationInterface
    {
        return $this->paginator->paginate(
            $this->reservationRepository->queryOverdueReservations(),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['reservation.expirationDate', 'reservation.createdAt', 'item.title', 'reservation.returnDate', 'reservation.email'],
                'defaultSortFieldName' => 'reservation.expirationDate',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Check if user has overdue reservations.
     *
     * @param UserInterface $user User entity
     *
     * @return bool True if user has overdue reservations
     */
    public function hasOverdueReservations(UserInterface $user): bool
    {
        $overdueReservations = $this->reservationRepository->queryOverdueReservations()
            ->andWhere('reservation.user = :user')
            ->andWhere('reservation.status = :approved')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return !empty($overdueReservations);
    }
}
