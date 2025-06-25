<?php

/**
 * Item repository.
 */

namespace App\Repository;

use App\Dto\ItemListFiltersDto;
use App\Entity\Category;
use App\Entity\Item;
use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ItemRepository.
 *
 * @extends ServiceEntityRepository<Item>
 */
class ItemRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    /**
     * Query all records.
     *
     * @param ItemListFiltersDto $filters Filters
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(ItemListFiltersDto $filters): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('item')
            ->select(
                'partial item.{id, createdAt, updatedAt, title, quantity, rating}',
                'partial category.{id, title}',
                'partial tags.{id, title}'
            )
            ->join('item.category', 'category')
            ->leftJoin('item.tags', 'tags')
            ->where('item.quantity > 0');

        return $this->applyFiltersToList($queryBuilder, $filters);
    }

    /**
     * Count items by category.
     *
     * @param Category $category Category
     *
     * @return int Number of items in category
     */
    public function countByCategory(Category $category): int
    {
        $qb = $this->createQueryBuilder('item');

        return $qb->select($qb->expr()->countDistinct('item.id'))
            ->where('item.category = :category')
            ->setParameter(':category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Save entity.
     *
     * @param Item $item Item entity
     */
    public function save(Item $item): void
    {
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param Item $item Item entity
     */
    public function delete(Item $item): void
    {
        $this->getEntityManager()->remove($item);
        $this->getEntityManager()->flush();
    }

    /**
     * Find items by search criteria.
     *
     * @param string|null $title Search title
     *
     * @return Item[] Returns an array of Item objects
     */
    public function findBySearchCriteria(?string $title = null): array
    {
        $qb = $this->createQueryBuilder('i');

        if ($title) {
            $qb->andWhere('i.title LIKE :title')
                ->setParameter('title', '%'.$title.'%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Query search results.
     *
     * @param string|null   $title    Search title
     * @param int|null      $rating   Minimum rating
     * @param Category|null $category Search category
     * @param Tag|null      $tag      Search Tag
     *
     * @return QueryBuilder Query builder
     */
    public function querySearchResults(?string $title = null, ?int $rating = null, ?Category $category = null, ?Tag $tag = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('item')
            ->select(
                'partial item.{id, createdAt, updatedAt, title, quantity, rating}',
                'partial category.{id, title}',
                'partial tags.{id, title}'
            )
            ->join('item.category', 'category')
            ->leftJoin('item.tags', 'tags');

        if ($title) {
            $qb->andWhere('item.title LIKE :title')
                ->setParameter('title', '%'.$title.'%');
        }

        if (null !== $rating) {
            $qb->andWhere('item.rating >= :rating')
                ->setParameter('rating', $rating);
        }

        if ($category) {
            $qb->andWhere('category = :category')
                ->setParameter('category', $category);
        }

        if ($tag) {
            $qb->andWhere('tags IN (:tag)')
                ->setParameter('tag', $tag);
        }

        return $qb;
    }

    /**
     * Apply filters to paginated list.
     *
     * @param QueryBuilder       $queryBuilder Query builder
     * @param ItemListFiltersDto $filters      Filters
     *
     * @return QueryBuilder Query builder
     */
    private function applyFiltersToList(QueryBuilder $queryBuilder, ItemListFiltersDto $filters): QueryBuilder
    {
        if ($filters->category instanceof Category) {
            $queryBuilder->andWhere('category = :category')
                ->setParameter('category', $filters->category);
        }

        if ($filters->tag instanceof Tag) {
            $queryBuilder->andWhere('tags IN (:tag)')
                ->setParameter('tag', $filters->tag);
        }

        return $queryBuilder;
    }
}
