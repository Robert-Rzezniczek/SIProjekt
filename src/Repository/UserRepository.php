<?php

/**
 * \App\Entity\User repository.
 */

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class UserRepository.
 *
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * Constructor.
     *
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Query all records.
     *
     * @return QueryBuilder Query builder
     */
    public function queryAll(): QueryBuilder
    {
        return $this->createQueryBuilder('user')
            ->select(
                'partial user.{id, email, roles, password, blocked}',
            );
    }

    /**
     * Save entity.
     *
     * @param User $user User entity
     */
    public function save(User $user): void
    {
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Delete entity.
     *
     * @param User $user User entity
     */
    public function delete(User $user): void
    {
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * Count current admins.
    //     * @return int
    //     */
    //    public function countAdmins(): int
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->select('COUNT(u.id)')
    //            ->where('JSON_CONTAINS(u.roles, :role)')
    //            ->setParameter('role', '"ROLE_ADMIN"')
    //            ->getQuery()
    //            ->getSingleScalarResult();
    //    }
}
