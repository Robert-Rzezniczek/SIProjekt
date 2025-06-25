<?php

/**
 * ItemRating repository.
 */

namespace App\Repository;

use App\Entity\Item;
use App\Entity\ItemRating;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ItemRatingRepository.
 *
 * @extends ServiceEntityRepository<ItemRating>
 */
class ItemRatingRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemRating::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('rating')
            ->select(
                'partial rating.{id, value}',
                'partial item.{id, title}',
                'partial user.{id, email}'
            )
            ->join('rating.item', 'item')
            ->join('rating.user', 'user');

        return $queryBuilder;
    }

    /**
     * Find a rating by item and user.
     *
     * @param Item $item The item to filter by
     * @param User $user The user to filter by
     *
     * @return ItemRating|null The rating entity or null if not found
     */
    public function findOneByItemAndUser(Item $item, User $user): ?ItemRating
    {
        return $this->createQueryBuilder('rating')
            ->where('rating.item = :item')
            ->andWhere('rating.user = :user')
            ->setParameter('item', $item)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all ratings for an item.
     *
     * @param Item $item The item to filter by
     *
     * @return array List of ratings
     */
    public function findByItem(Item $item): array
    {
        return $this->createQueryBuilder('rating')
            ->where('rating.item = :item')
            ->setParameter('item', $item)
            ->getQuery()
            ->getResult();
    }

    /**
     * Save entity.
     *
     * @param ItemRating $itemRating ItemRating entity
     */
    public function save(ItemRating $itemRating): void
    {
        $this->getEntityManager()->persist($itemRating);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param ItemRating $itemRating ItemRating entity
     */
    public function delete(ItemRating $itemRating): void
    {
        $this->getEntityManager()->remove($itemRating);
        $this->getEntityManager()->flush();
    }
}
