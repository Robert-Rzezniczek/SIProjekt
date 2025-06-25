<?php

/**
 * Reservation repository.
 */

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ReservationRepository.
 *
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('reservation')
            ->select(
                'partial reservation.{id, item, email, nickname, comment, status, createdAt, loanDate, expirationDate}',
                'partial item.{id, title}',
                'partial user.{id, email}',
            )
            ->leftJoin('reservation.item', 'item')
            ->leftJoin('reservation.user', 'user');
    }

    /**
     * Query all records by User.
     *
     * @param UserInterface $user User
     *
     * @return QueryBuilder Query builder
     */
    public function queryAllByUser(UserInterface $user): QueryBuilder
    {
        return $this->createQueryBuilder('reservation')
            ->select(
                'partial reservation.{id, email, nickname, comment, status, createdAt, loanDate, expirationDate}',
                'partial item.{id, title}',
                'partial user.{id, email}'
            )
            ->leftJoin('reservation.item', 'item')
            ->leftJoin('reservation.user', 'user')
            ->andWhere('reservation.user = :user')
            ->setParameter('user', $user);
    }

    /**
     * Save entity.
     *
     * @param Reservation $reservation Reservation entity
     */
    public function save(Reservation $reservation): void
    {
        $this->getEntityManager()->persist($reservation);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param Reservation $reservation Reservation entity
     */
    public function delete(Reservation $reservation): void
    {
        $this->getEntityManager()->remove($reservation);
        $this->getEntityManager()->flush();
    }

    /**
     * Count reservations by item.
     *
     * @param Item $item Item entity
     *
     * @return int Number of reservations for the item
     */
    public function countReservationsByItem(Item $item): int
    {
        $qb = $this->createQueryBuilder('reservation');

        return $qb->select($qb->expr()->countDistinct('reservation.id'))
            ->where('reservation.item = :item')
            ->setParameter(':item', $item)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Query overdue reservations (approved and not returned, or returned late).
     *
     * @return QueryBuilder Query builder
     */
    public function queryOverdueReservations(): QueryBuilder
    {
        return $this->createQueryBuilder('reservation')
            ->select(
                'partial reservation.{id, email, nickname, status, createdAt, loanDate, expirationDate, returnDate}',
                'partial item.{id, title}',
                'partial user.{id, email}'
            )
            ->leftJoin('reservation.item', 'item')
            ->leftJoin('reservation.user', 'user')
            ->where('reservation.expirationDate < :now')
            ->andWhere('(reservation.status = :approved AND reservation.returnDate IS NULL) 
                       OR (reservation.status = :returned AND reservation.returnDate > reservation.expirationDate)')
            ->setParameter('approved', 'approved')
            ->setParameter('returned', 'returned')
            ->setParameter('now', new \DateTimeImmutable());
    }
}
