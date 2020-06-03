<?php

namespace App\Repository;

use App\Entity\Object2category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Object2category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Object2category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Object2category[]    findAll()
 * @method Object2category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Object2categoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Object2category::class);
    }

    // /**
    //  * @return Object2category[] Returns an array of Object2category objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Object2category
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
