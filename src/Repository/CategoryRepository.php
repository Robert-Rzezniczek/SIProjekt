<?php

/**
 * Category repository.
 */

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class CategoryRepository.
 *
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('category')
            ->select(
                'partial category.{id, createdAt, updatedAt, title}',
            );
    }

    /**
     * Query items by category.
     *
     * @param Category $category Category entity
     *
     * @return QueryBuilder Query builder
     */
    public function findByCategory(Category $category): QueryBuilder
    {
        return $this->createQueryBuilder('item')
            ->select('partial item.{id, createdAt, updatedAt, title, description, quantity}')
            ->where('item.category = :category')
            ->setParameter('category', $category);
    }

    /**
     * Save entity.
     *
     * @param Category $category Category entity
     */
    public function save(Category $category): void
    {
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param Category $category Category entity
     */
    public function delete(Category $category): void
    {
        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();
    }
}
